<?php

namespace Carbon\ApiBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class StringToObjectValidator extends ConstraintValidator
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        $results = $this->em->getRepository($constraint->entity)->findBy(array(
            $constraint->property => $value
        ));

        $resultCount = count($results);

        if ($resultCount > 1) {
            $this->context->buildViolation($constraint->propertyNotUniqueMessage)
                ->setParameter('%objectName%', $constraint->objectName)
                ->setParameter('%property%', $constraint->property)
                ->setParameter('%string%', $value)
                ->addViolation()
            ;
        }

        if ($resultCount === 0) {
            $this->context->buildViolation($constraint->objectNotFoundMessage)
                ->setParameter('%objectName%', $constraint->objectName)
                ->setParameter('%property%', $constraint->property)
                ->setParameter('%string%', $value)
                ->addViolation()
            ;
        }
    }

    public function getRequiredOptions()
    {
        return array('entity', 'property');
    }
}
