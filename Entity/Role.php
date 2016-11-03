<?php

namespace Carbon\ApiBundle\Entity;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * @ORM\MappedSuperclass
 */
class Role implements RoleInterface
{
    /**
     * @ORM\Column(type="string", name="role", unique=true)
     * @Groups({"default"})
     */
    protected $role;

    /**
     * Populate the role field
     *
     * @param string $role ROLE_FOO etc
     */
    public function __construct($role)
    {
        $this->role = $role;
    }

    public function __toString()
    {
        return $this->role;
    }

    /**
     * Return the role field.
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }
}
