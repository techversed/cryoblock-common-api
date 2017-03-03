<?php

namespace Carbon\ApiBundle\Form\Type;

use Carbon\ApiBundle\Form\Type\CryoblockAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserFormType extends CryoblockAbstractType
{
    private $class;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', 'text', array('label' => 'form.firstName', 'translation_domain' => 'FOSUserBundle'))
            ->add('lastName', 'text', array('label' => 'form.lastName', 'translation_domain' => 'FOSUserBundle'))
            ->add('email', 'email', array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('username', null, array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
            ->add('plainPassword', 'password')
            ->add('enabled', 'checkbox')
            ->add('groups', 'cryoblock_mtm', array(
                'parent_object' => $builder->getForm()->getData(),
                'accessor' => 'userGroups',
                'child_accessor' => 'group'
            ))
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Carbon\ApiBundle\Entity\User',
            'intention'  => 'registration',
            'csrf_protection' => false,
        ));
    }

    // BC for SF < 2.7
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function getName()
    {
        return 'user';
    }
}
