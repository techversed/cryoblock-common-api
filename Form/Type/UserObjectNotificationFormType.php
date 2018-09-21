<?php

namespace Carbon\ApiBundle\Form\Type;

use Carbon\ApiBundle\Form\DataTransformer\CryoblockOTOTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserObjectNotificationFormType extends CryoblockAbstractType
{
    private $class;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entityId', 'integer')
            ->add('entityDetail', 'entity', array(
                'class' => 'Carbon\\ApiBundle\\Entity\\EntityDetail',
                'property' => 'entity_detail_id',
                'multiple' => false
            ))
            ->add('user', 'entity', array(
                'class' => 'Carbon\\ApiBundle\\Entity\\User',
                'property' => 'user_id',
                'multiple' => false
            ))
            ->add('onCreate', 'checkbox')
            ->add('onUpdate', 'checkbox')
            ->add('onDelete', 'checkbox')
        ;

        $builder->get('user')
            ->addViewTransformer(new CryoblockOTOTransformer(
                $this->em, 'CarbonApiBundle:User'
            ))
        ;

        $builder->get('entityDetail')
            ->addViewTransformer(new CryoblockOTOTransformer(
                $this->em, 'CarbonApiBundle:EntityDetail'
            ))
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Carbon\ApiBundle\Entity\UserObjectNotification',
            'csrf_protection' => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function getName()
    {
        return 'user_object_notification';
    }
}
