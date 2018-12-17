<?php

namespace Carbon\ApiBundle\Entity\Help;

use Carbon\ApiBundle\Annotation AS Carbon;
use Carbon\ApiBundle\Entity\BaseCryoblockEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
class BaseHelp extends BaseCryoblockEntity
{
    /**
     * @Gedmo\TreeLeft
     * @JMS\Groups({"default"})
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeRight
     * @JMS\Groups({"default"})
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @ORM\Column(name="path", type="string", length=3000, nullable=true)
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="path")
     */
    protected $path;

    /**
     * @ORM\Column(name="id_path", type="string", length=3000, nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $idPath;

    /**
     * @Gedmo\TreePathSource
     * @ORM\Column(name="title", type="string", length=64)
     * @JMS\Groups({"default"})
     * @Assert\NotBlank()
     * @Carbon\Searchable(name="title")
     */
    protected $title;

    /**
     * @ORM\Column(name="description", type="string", length=300)
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="description")
     */
    protected $description;

    /**
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $parentId;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Help", inversedBy="children")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @JMS\Groups({"parent"})
     */
    protected $parent;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="level", type="integer", nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $level;

    /**
     * @ORM\OneToMany(targetEntity="Help", mappedBy="parent")
     * @JMS\Groups({"children"})
     * @JMS\MaxDepth(2)
     */
    protected $children;

    /**
     * @ORM\Column(name="is_public_edit", type="boolean", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $isPublicEdit = false;

    /**
     * @ORM\Column(name="is_public_view", type="boolean", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $isPublicView = true;

    /**
     * Gets the value of lft.
     *
     * @return mixed
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Sets the value of lft.
     *
     * @param mixed $lft the lft
     *
     * @return self
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Gets the value of rgt.
     *
     * @return mixed
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Sets the value of rgt.
     *
     * @param mixed $rgt the rgt
     *
     * @return self
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Gets the value of path.
     *
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the value of path.
     *
     * @param mixed $path the path
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Gets the value of idPath.
     *
     * @return mixed
     */
    public function getIdPath()
    {
        return $this->idPath;
    }

    /**
     * Sets the value of idPath.
     *
     * @param mixed $idPath the id path
     *
     * @return self
     */
    public function setIdPath($idPath)
    {
        $this->idPath = $idPath;

        return $this;
    }

    /**
     * Gets the value of title.
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the value of title.
     *
     * @param mixed $title the title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets the value of description.
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the value of description.
     *
     * @param mixed $description the description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the value of parentId.
     *
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Sets the value of parentId.
     *
     * @param mixed $parentId the parent id
     *
     * @return self
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Gets the }).
     *
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the }).
     *
     * @param mixed $parent the parent
     *
     * @return self
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Gets the value of level.
     *
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Sets the value of level.
     *
     * @param mixed $level the level
     *
     * @return self
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Gets the value of children.
     *
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Sets the value of children.
     *
     * @param mixed $children the children
     *
     * @return self
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Check if the division has children
     *
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return ($this->getRgt() - $this->getLft()) > 1;
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getStringLabel()
    {
        return $this->getPath();
    }
}
