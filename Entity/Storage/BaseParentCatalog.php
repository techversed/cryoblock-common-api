<?php

namespace Carbon\ApiBundle\Entity\Storage;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
class BaseParentCatalog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="parent_catalog_id", type="integer")
     * @JMS\Groups({"default"})
     */
    protected $parentCatalogId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Catalog")
     * @ORM\JoinColumn(name="parent_catalog_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $parentCatalog;

    /**
     * @var integer
     *
     * @ORM\Column(name="child_catalog_id", type="integer")
     * @JMS\Groups({"default"})
     */
    protected $childCatalogId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Catalog")
     * @ORM\JoinColumn(name="child_catalog_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $childCatalog;


    /**
     * Gets the value of parentCatalogId.
     *
     * @return integer
     */
    public function getParentCatalogId()
    {
        return $this->parentCatalogId;
    }

    /**
     * Sets the value of parentCatalogId.
     *
     * @param integer $parentCatalogId the parent catalog id
     *
     * @return self
     */
    public function setParentCatalogId($parentCatalogId)
    {
        $this->parentCatalogId = $parentCatalogId;

        return $this;
    }

    /**
     * Gets the value of parentCatalog.
     *
     * @return mixed
     */
    public function getParentCatalog()
    {
        return $this->parentCatalog;
    }

    /**
     * Sets the value of parentCatalog.
     *
     * @param mixed $parentCatalog the parent catalog
     *
     * @return self
     */
    public function setParentCatalog($parentCatalog)
    {
        $this->parentCatalog = $parentCatalog;

        return $this;
    }

    /**
     * Gets the value of childCatalogId.
     *
     * @return integer
     */
    public function getChildCatalogId()
    {
        return $this->childCatalogId;
    }

    /**
     * Sets the value of childCatalogId.
     *
     * @param integer $childCatalogId the child catalog id
     *
     * @return self
     */
    public function setChildCatalogId($childCatalogId)
    {
        $this->childCatalogId = $childCatalogId;

        return $this;
    }

    /**
     * Gets the value of childCatalog.
     *
     * @return mixed
     */
    public function getChildCatalog()
    {
        return $this->childCatalog;
    }

    /**
     * Sets the value of childCatalog.
     *
     * @param mixed $childCatalog the child catalog
     *
     * @return self
     */
    public function setChildCatalog($childCatalog)
    {
        $this->childCatalog = $childCatalog;

        return $this;
    }
}
