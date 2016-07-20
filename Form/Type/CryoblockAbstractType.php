<?php

namespace Carbon\ApiBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

abstract class CryoblockAbstractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array(
            $this, 'onPreSubmit'
        ));
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (is_array($data)) {
            foreach ($data as $name => $value) {
                if (!$form->has($name)) {
                    // ignore extra fields
                    $form->add($name, 'hidden', array('mapped' => false));
                }
            }
        }
    }
}
