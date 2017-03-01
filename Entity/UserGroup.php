<?php

namespace Carbon\ApiBundle\Entity;

use Carbon\ApiBundle\Annotation AS Carbon;
use Carbon\ApiBundle\Entity\Group;
use Carbon\ApiBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User Group
 *
 * @ORM\Entity()
 * @ORM\Table(name="cryoblock.user_group", schema="cryoblock")
 */
class UserGroup
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @JMS\Groups({"default"})
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     * @JMS\Groups({"default"})
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\User", inversedBy="userGroups")
     * @ORM\JoinColumn(name="user_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    private $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="group_id", type="integer")
     * @JMS\Groups({"default"})
     */
    private $groupId;

    /**
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\Group", inversedBy="groupUsers")
     * @ORM\JoinColumn(name="group_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    private $group;

    public function setGroup(Group $group)
    {
        $this->group = $group;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
