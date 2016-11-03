<?php

namespace Carbon\ApiBundle\Form\Type;

use AppBundle\Form\DataTransformer\SampleTypeTransformer;
use Carbon\ApiBundle\Form\Type\CryoblockAbstractType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GroupFormType extends CryoblockAbstractType
{
    private $class;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('roles', 'cryoblock_mtm', array(
                'parent_object' => $builder->getForm()->getData(),
                'accessor' => 'groupRoles',
                'child_accessor' => 'role'
            ))
            ->add('users', 'cryoblock_mtm', array(
                'parent_object' => $builder->getForm()->getData(),
                'accessor' => 'groupUsers',
                'child_accessor' => 'user'
            ))
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Carbon\ApiBundle\Entity\Group',
            'csrf_protection' => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function getName()
    {
        return 'group';
    }
}
