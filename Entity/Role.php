<?php

namespace Carbon\ApiBundle\Entity;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="role")
 */
class Role implements RoleInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Groups({"default"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="role", unique=true)
     * @Carbon\Searchable(name="role")
     * @JMS\Groups({"default"})
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

    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getStringLabel()
    {
        return $this->id . ': ' . $this->getRole();
    }
}
