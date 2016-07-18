<?php

namespace Carbon\ApiBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class CryoblockMTMFormType extends AbstractType
{
    private $class;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $em = $this->em;

        $camelCaseConverter = new CamelCaseToSnakeCaseNameConverter();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $builder
            ->add('parentId', 'hidden', array('mapped' => false))
            ->add('removing', 'hidden', array('mapped' => false))
            ->add('adding', 'hidden', array('mapped' => false))
        ;

        $accessor = $options['accessor'];
        $childAccessor = $options['child_accessor'];
        $parentObject = $options['parent_object'];
        $parentClass = get_class($parentObject);

        $parentMappings = $this->em->getMetadataFactory()->getMetadataFor($parentClass);
        $parentAssociationMappings = $parentMappings->getAssociationMapping($accessor);

        $targetEntity = $parentAssociationMappings['targetEntity'];
        $mappedBy = $parentAssociationMappings['mappedBy'];

        $targetMappings = $this->em->getMetadataFactory()->getMetadataFor($targetEntity);
        $targetAssociationMappings = $targetMappings->getAssociationMapping($mappedBy);

        $parentJoinColumn = $camelCaseConverter->denormalize($targetAssociationMappings['joinColumns'][0]['name']);

        $childMappings = $targetMappings->getAssociationMapping($childAccessor);
        $childJoinColumn = $camelCaseConverter->denormalize($childMappings['joinColumns'][0]['name']);
        $childClass = $childMappings['targetEntity'];

        $preSubmit = function (FormEvent $event) use ($em, $parentObject, $childClass, $targetEntity, $parentJoinColumn, $childJoinColumn, $accessor, $propertyAccessor) {

            $map = $event->getData();

            $removingIds = isset($map['removing']) ? $map['removing'] : array();
            $addingIds = isset($map['adding']) ? $map['adding'] : array();
            $parentId = $parentObject->getId();

            if (empty($removingIds) && empty($addingIds)) {
                return;
            }

            $repo = $em->getRepository($targetEntity);

            foreach ($removingIds as $removingId) {

                $linkerObject = $repo->findOneBy(array(
                    $parentJoinColumn => $parentId,
                    $childJoinColumn => $removingId
                ));

                if ($linkerObject) {

                    $em->remove($linkerObject);

                }

            }

            foreach ($addingIds as $addingId) {

                $newLinkerObject = new $targetEntity();

                $objRepo = $em->getRepository($childClass);

                $childObj = $objRepo->find($addingId);

                $propertyAccessor->setValue($newLinkerObject, str_replace('Id', '', $childJoinColumn), $childObj);
                $propertyAccessor->setValue($newLinkerObject, str_replace('Id', '', $parentJoinColumn), $parentObject);

                $em->persist($newLinkerObject);

            }

        };

        $builder->addEventListener(FormEvents::PRE_SUBMIT, $preSubmit);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));

        $resolver->setRequired(array(
            'parent_object', 'accessor', 'child_accessor',
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function getName()
    {
        return 'cryoblock_mtm';
    }
}
