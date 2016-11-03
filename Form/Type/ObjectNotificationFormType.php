<?php

namespace Carbon\ApiBundle\Form\Type;

use Carbon\ApiBundle\Form\DataTransformer\CryoblockOTOTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectNotificationFormType extends CryoblockAbstractType
{
    private $class;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objectType', 'text')
            ->add('onCreateGroup', 'entity', array(
                'class' => 'AppBundle\\Entity\\Group',
                'property' => 'on_create_group_id',
                'multiple' => false
            ))
            ->add('onUpdateGroup', 'entity', array(
                'class' => 'AppBundle\\Entity\\Group',
                'property' => 'on_update_group_id',
                'multiple' => false
            ))
            ->add('onDeleteGroup', 'entity', array(
                'class' => 'AppBundle\\Entity\\Group',
                'property' => 'on_delete_group_id',
                'multiple' => false
            ))
        ;

        $builder->get('onCreateGroup')
            ->addViewTransformer(new CryoblockOTOTransformer(
                $this->em, 'AppBundle\\Entity\\Group'
            ))
        ;

        $builder->get('onUpdateGroup')
            ->addViewTransformer(new CryoblockOTOTransformer(
                $this->em, 'AppBundle\\Entity\\Group'
            ))
        ;

        $builder->get('onDeleteGroup')
            ->addViewTransformer(new CryoblockOTOTransformer(
                $this->em, 'AppBundle\\Entity\\Group'
            ))
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function getName()
    {
        return 'object_notification';
    }
}
