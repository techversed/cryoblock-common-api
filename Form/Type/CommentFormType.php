<?php

namespace Carbon\ApiBundle\Form\Type;

use Carbon\ApiBundle\Form\DataTransformer\CryoblockOTOTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentFormType extends CryoblockAbstractType
{
    private $class;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', 'text')
            ->add('objectType', 'text')
            ->add('objectId', 'integer')
            ->add('parent', 'entity', array(
                'class' => 'Carbon\\ApiBundle\\Entity\\Comment',
                'property' => 'parent_id',
                'multiple' => false
            ))
        ;

        $builder->get('parent')
            ->addViewTransformer(new CryoblockOTOTransformer(
                $this->em, 'Carbon\\ApiBundle\\Entity\\Comment'
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
        return 'comment';
    }
}
