<?php

namespace Carbon\ApiBundle\Form\Type;

use AppBundle\Entity\Storage\Catalog;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class SampleCatalogFormType extends AbstractType
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

        $parentObject = $options['parent_object'];
        $parentClass = get_class($parentObject);

        $preSubmit = function (FormEvent $event) use ($em, $parentObject) {

            $catalogData = $event->getData();

            if (is_array($catalogData)) {
                $catalog = $em->getRepository('AppBundle\Entity\Storage\Catalog')->find($catalogData['id']);
                $parentObject->setCatalog($catalog);
                return;
            } else {
                $catalog = new Catalog();
                $catalog->setName($catalogData);
                $catalog->setStatus('Available');
                $em->persist($catalog);
                $em->flush();
                $parentObject->setCatalogId($catalog->getId());
                return;
            }
            // var_dump($map);
            // die;

            // $removingIds = isset($map['removing']) ? $map['removing'] : array();
            // $addingIds = isset($map['adding']) ? $map['adding'] : array();
            // $parentId = $parentObject->getId();

            // if (empty($removingIds) && empty($addingIds)) {
            //     return;
            // }

            // $repo = $em->getRepository($targetEntity);

            // foreach ($removingIds as $removingId) {

            //     $linkerObject = $repo->findOneBy(array(
            //         $parentJoinColumn => $parentId,
            //         $childJoinColumn => $removingId
            //     ));

            //     if ($linkerObject) {

            //         $em->remove($linkerObject);

            //     }

            // }

            // foreach ($addingIds as $addingId) {

            //     $alreadyExists = $em->getRepository($targetEntity)->findOneBy(array(
            //         $childJoinColumn => $addingId,
            //         $parentJoinColumn => $parentId
            //     ));

            //     if ($alreadyExists) {
            //         continue;
            //     }

            //     $objRepo = $em->getRepository($childClass);

            //     $childObj = $objRepo->find($addingId);

            //     if (!$childObj) {
            //         continue;
            //     }

            //     $newLinkerObject = new $targetEntity();

            //     $propertyAccessor->setValue($newLinkerObject, str_replace('Id', '', $childJoinColumn), $childObj);
            //     $propertyAccessor->setValue($newLinkerObject, str_replace('Id', '', $parentJoinColumn), $parentObject);

            //     $em->persist($newLinkerObject);

            // }

        };

        $builder->addEventListener(FormEvents::PRE_SUBMIT, $preSubmit);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));

        $resolver->setRequired(array(
            'parent_object',
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function getName()
    {
        return 'cryoblock_sample_catalog';
    }
}
