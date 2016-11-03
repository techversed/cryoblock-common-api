<?php

namespace Carbon\ApiBundle\Entity;

use Carbon\ApiBundle\Entity\Group;
use Carbon\ApiBundle\Entity\Role;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Group Role
 *
 * @ORM\Entity()
 * @ORM\Table(name="group_role")
 */
class GroupRole
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Groups({"default"})
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="group_id", type="integer")
     * @JMS\Groups({"default"})
     */
    private $groupId;

    /**
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\Group")
     * @ORM\JoinColumn(name="group_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    private $group;

    /**
     * @var integer
     *
     * @ORM\Column(name="role_id", type="integer")
     * @JMS\Groups({"default"})
     */
    private $roleId;

    /**
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\Role")
     * @ORM\JoinColumn(name="role_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    private $role;

    public function setGroup(Group $group)
    {
        $this->group = $group;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }
}
