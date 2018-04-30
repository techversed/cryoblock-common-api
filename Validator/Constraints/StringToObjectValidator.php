<?php

namespace Carbon\ApiBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
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

        if ($constraint->regex && !preg_match($constraint->regex, $value)) {
            return;
        }

        if (strpos($value, ',')) {
            $value = explode(',', $value);
        }

        $results = $this->em->getRepository($constraint->entity)->findBy(array(
            $constraint->property => $value
        ));

        $resultCount = count($results);

        if (!is_array($value) && $resultCount > 1) {
            $this->context->buildViolation($constraint->propertyNotUniqueMessage)
                ->setParameter('%objectName%', $constraint->objectName)
                ->setParameter('%property%', $constraint->property)
                ->setParameter('%string%', $value)
                ->addViolation()
            ;
        }

        if (is_array($value) && $resultCount != count($value)) {

            $propertyAccessor = PropertyAccess::createPropertyAccessor();

            $foundValues = array();
            foreach ($results as $result) {
                $foundValues[] = $propertyAccessor->getValue($result, $constraint->property);
            }

            $notFoundValues = array_diff($value, $foundValues);

            $this->context->buildViolation('No %objectName%(s) were found with id(s) %string%')
                ->setParameter('%objectName%', $constraint->objectName)
                ->setParameter('%property%', $constraint->property)
                ->setParameter('%string%', implode(',', $notFoundValues))
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
