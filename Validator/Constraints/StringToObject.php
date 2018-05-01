<?php

namespace Carbon\ApiBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class StringToObject extends Constraint
{
    public $objectNotFoundMessage = 'No %objectName% was found with %property% "%string%".';

    public $propertyNotUniqueMessage = 'Multiple %objectName% objects were found with %property% "%string%". To convert from string to object, the %property% property must be unique.';

    public $objectName;

    public $entity;

    public $property;

    public $regex;

    public function __construct($options)
    {
        $this->entity = $options['entity'];
        $this->property = $options['property'];
        $this->objectName = $options['objectName'];
        $this->regex = array_key_exists('regex', $options) ? $options['regex'] : null;
    }

    public function validatedBy()
    {
        return 'string_to_object_validator';
    }

}
