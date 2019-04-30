<?php

namespace Carbon\ApiBundle\Entity\Storage;

use JMS\Serializer\Annotation AS JMS;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\MappedSuperclass */
class BaseWorkingSet
{
    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"default"})
     */
    protected $createdAt;

    /**
     * Created by id
     * @ORM\Column(name="created_by_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $createdById;

    /**
     * @var User $createdBy
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $createdBy;


    /**
     * Created by id
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $userId;


    /**
     * @var User $createdBy
     *
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $user;


    /**
     * @var Sample $sample
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Sample")
     * @ORM\JoinColumn(name="sample_id", referencedColumnName="id")
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     */
    protected $sample;
                                                                        //Carbon\Searchable(name="catalog", join=true, searchProp="name", joinProp="catalogId", subAlias="ct")
    /**
     * Sample id
     * @ORM\Column(name="sample_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $sampleId;

    /**
     * Gets the value of createdAt.
     *
     * @return \DateTime $updated
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the value of createdAt.
     *
     * @param \DateTime $updated $createdAt the created at
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Gets the Created by id.
     *
     * @return mixed
     */
    public function getCreatedById()
    {
        return $this->createdById;
    }

    /**
     * Sets the Created by id.
     *
     * @param mixed $createdById the created by id
     *
     * @return self
     */
    public function setCreatedById($createdById)
    {
        $this->createdById = $createdById;

        return $this;
    }

    /**
     * Gets the value of createdBy.
     *
     * @return User $createdBy
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Sets the value of createdBy.
     *
     * @param User $createdBy $createdBy the created by
     *
     * @return self
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Gets the Created by id.
     *
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Sets the Created by id.
     *
     * @param mixed $userId the user id
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Gets the value of user.
     *
     * @return User $createdBy
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the value of user.
     *
     * @param User $createdBy $user the user
     *
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the value of sample.
     *
     * @return Sample $sample
     */
    public function getSample()
    {
        return $this->sample;
    }

    /**
     * Sets the value of sample.
     *
     * @param Sample $sample $sample the sample
     *
     * @return self
     */
    public function setSample($sample)
    {
        $this->sample = $sample;

        return $this;
    }

    /**
     * Gets the Sample id.
     *
     * @return mixed
     */
    public function getSampleId()
    {
        return $this->sampleId;
    }

    /**
     * Sets the Sample id.
     *
     * @param mixed $sampleId the sample id
     *
     * @return self
     */
    public function setSampleId($sampleId)
    {
        $this->sampleId = $sampleId;

        return $this;
    }
}
