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
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     */
    protected $content;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="html_content", type="text", nullable=true)
     */
    protected $htmlContent;

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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Help\HelpEditor", mappedBy="help", cascade={"remove"})
     * @JMS\Groups({"children", "editors"})
     */
    public $helpEditors;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Help\HelpViewer", mappedBy="help", cascade={"remove"})
     * @JMS\Groups({"children", "viewers"})
     */
    public $helpViewers;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Help\HelpGroupViewer", mappedBy="help", cascade={"remove"})
     * @JMS\Groups({"children", "groupViewers"})
     */
    protected $helpGroupViewers;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Help\HelpGroupEditor", mappedBy="help", cascade={"remove"})
     * @JMS\Groups({"children", "groupEditors"})
     */
    protected $helpGroupEditors;

    /**
     * @ORM\Column(name="is_public_edit", type="boolean", nullable=false)
     * @JMS\Groups({"default"})
     */
    public $isPublicEdit = false;

    /**
     * @ORM\Column(name="is_public_view", type="boolean", nullable=false)
     * @JMS\Groups({"default"})
     */
    public $isPublicView = true;

    public $viewers;

    public $editors;

    protected $entityDetailId = -1;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param integer $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Check if the help has children
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

    /**
     * Gets the value of helpEditors.
     *
     * @return mixed
     */
    public function getHelpEditors()
    {
        return $this->helpEditors;
    }

    /**
     * Sets the value of helpEditors.
     *
     * @param mixed $helpEditors the help editors
     *
     * @return self
     */
    public function setHelpEditors($helpEditors)
    {
        $this->helpEditors = $helpEditors;

        return $this;
    }

    /**
     * Gets the value of helpViewers.
     *
     * @return mixed
     */
    public function getHelpViewers()
    {
        return $this->helpViewers;
    }

    /**
     * Sets the value of helpViewers.
     *
     * @param mixed $helpViewers the help viewers
     *
     * @return self
     */
    public function setHelpViewers($helpViewers)
    {
        $this->helpViewers = $helpViewers;

        return $this;
    }

    /**
     * Gets the value of isPublicEdit.
     *
     * @return mixed
     */
    public function getIsPublicEdit()
    {
        return $this->isPublicEdit;
    }

    /**
     * Sets the value of isPublicEdit.
     *
     * @param mixed $isPublicEdit the is public edit
     *
     * @return self
     */
    public function setIsPublicEdit($isPublicEdit)
    {
        $this->isPublicEdit = $isPublicEdit;

        return $this;
    }

    /**
     * Gets the value of isPublicView.
     *
     * @return mixed
     */
    public function getIsPublicView()
    {
        return $this->isPublicView;
    }

    /**
     * Sets the value of isPublicView.
     *
     * @param mixed $isPublicView the is public view
     *
     * @return self
     */
    public function setIsPublicView($isPublicView)
    {
        $this->isPublicView = $isPublicView;

        return $this;
    }

    /**
     * Gets the value of viewers.
     *
     * @return mixed
     */
    public function getViewers()
    {
        return $this->viewers;
    }

    /**
     * Sets the value of viewers.
     *
     * @param mixed $viewers the viewers
     *
     * @return self
     */
    public function setViewers($viewers)
    {
        $this->viewers = $viewers;

        return $this;
    }

    /**
     * Gets the value of editors.
     *
     * @return mixed
     */
    public function getEditors()
    {
        return $this->editors;
    }

    /**
     * Sets the value of editors.
     *
     * @param mixed $editors the editors
     *
     * @return self
     */
    public function setEditors($editors)
    {
        $this->editors = $editors;

        return $this;
    }

    /**
     * Gets the value of helpGroupViewers.
     *
     * @return mixed
     */
    public function getHelpGroupViewers()
    {
        return $this->helpGroupViewers;
    }

    /**
     * Sets the value of helpGroupViewers.
     *
     * @param mixed $helpGroupViewers the help group viewers
     *
     * @return self
     */
    public function setHelpGroupViewers($helpGroupViewers)
    {
        $this->helpGroupViewers = $helpGroupViewers;

        return $this;
    }

    /**
     * Gets the value of helpGroupEditors.
     *
     * @return mixed
     */
    public function getHelpGroupEditors()
    {
        return $this->helpGroupEditors;
    }

    /**
     * Sets the value of helpGroupEditors.
     *
     * @param mixed $helpGroupEditors the help group editors
     *
     * @return self
     */
    public function setHelpGroupEditors($helpGroupEditors)
    {
        $this->helpGroupEditors = $helpGroupEditors;

        return $this;
    }

    /**
     * Gets the value of entityDetailId.
     *
     * @return mixed
     */
    public function getEntityDetailId()
    {
        return $this->entityDetailId;
    }

    /**
     * Sets the value of entityDetailId.
     *
     * @param mixed $entityDetailId the entity detail id
     *
     * @return self
     */
    public function setEntityDetailId($entityDetailId)
    {
        $this->entityDetailId = $entityDetailId;

        return $this;
    }

    /**
     * Gets the value of content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the value of content.
     *
     * @param string $content the content
     *
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Gets the value of htmlContent.
     *
     * @return string
     */
    public function getHtmlContent()
    {
        return $this->htmlContent;
    }

    /**
     * Sets the value of htmlContent.
     *
     * @param string $htmlContent the html content
     *
     * @return self
     */
    public function setHtmlContent($htmlContent)
    {
        $this->htmlContent = $htmlContent;

        return $this;
    }
}
