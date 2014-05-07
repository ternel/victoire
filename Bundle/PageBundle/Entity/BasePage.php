<?php
namespace Victoire\Bundle\PageBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Victoire\Bundle\CoreBundle\Annotations as VIC;
use Victoire\Bundle\CoreBundle\Entity\Route;
use Victoire\Bundle\PageBundle\Entity\Template;
use Victoire\Bundle\CoreBundle\Entity\Widget;
use Victoire\Bundle\SeoBundle\Entity\PageSeo;

/**
 * Page
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Table("cms_page")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\PageBundle\Repository\BasePageRepository")
 * @UniqueEntity("url")
 */
abstract class BasePage
{
    use \Gedmo\Timestampable\Traits\TimestampableEntity;

    const STATUS_DRAFT = "draft";
    const STATUS_PUBLISHED = "published";
    const STATUS_UNPUBLISHED = "unpublished";
    const STATUS_SCHEDULED = "scheduled";

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\SeoBundle\Entity\PageSeo", inversedBy="page", cascade={"persist"})
     * @ORM\JoinColumn(name="seo_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $seo;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="bodyId", type="string", length=255, nullable=true)
     */
    protected $bodyId;

    /**
     * @var string
     *
     * @ORM\Column(name="bodyClass", type="string", length=255, nullable=true)
     */
    protected $bodyClass;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"title"}, separator="-", updatable=false)
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\CoreBundle\Entity\Route", mappedBy="page", cascade={"persist"})
     */
    protected $routes;

    /**
     * @var string
     * This property is computed by the method PageSubscriber::buildUrl
     *
     * @ORM\Column(name="url", type="string", unique=true)
     */
    protected $url;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="layout", type="string", length=255)
     */
    protected $layout;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\CoreBundle\Entity\Widget", mappedBy="page")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $widgets;


    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\PageBundle\Entity\Template", inversedBy="pages")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id", onDelete="CASCADE")
     *
     */
    protected $template;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="BasePage", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="BasePage", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="Victoire\Bundle\SeoBundle\Entity\PageSeo", mappedBy="redirectTo")
     */
    protected $referer;

    /**
     * @var string
     *
     * @ORM\Column(name="homepage", type="boolean", nullable=false)
     */
    protected $homepage;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    protected $position = 0;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected $root;

    /**
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    protected $status = self::STATUS_PUBLISHED;

    /**
    * @var datetime $publishedAt
    *
    * @ORM\Column(name="publishedAt", type="datetime")
    * @VIC\BusinessProperty("date")
    */
    protected $publishedAt;

    /**
    * This relation it dynamicly added by PageSubscriber
    */
    protected $author;

    /**
     * @var string
     *
     * @ORM\Column(name="undeletable", type="boolean")
     */
    protected $undeletable = false;

    /**
     * @ORM\Column(name="widgetMap", type="array")
     */
    protected $widgetMap;

    /**
     * to string
     *
     * @return string
     **/
    public function __toString()
    {
        return $this->title;
    }

    /**
     * contruct
     **/
    public function __construct()
    {
        $this->widgets = new ArrayCollection();
        $this->homepage = false;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->publishedAt = new \DateTime();
        $this->widgetMap = array();
    }

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
     * Set id
     * @param id $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set seo
     *
     * @param PageSeo $seo
     * @return Page
     */
    public function setSeo(PageSeo $seo)
    {
        $seo->setPage($this);
        $this->seo = $seo;

        return $this;
    }

    /**
     * Get seo
     *
     * @return PageSeo
     */
    public function getSeo()
    {
        return $this->seo;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Page
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Page
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set layout
     *
     * @param string $layout
     * @return Page
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Get layout
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set template
     *
     * @param Page $template
     * @return Page
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }


    /**
     * Set widgets
     *
     * @param string $widgets
     * @return Page
     */
    public function setWidgets($widgets)
    {
        $this->widgets = $widgets;
        foreach ($widgets as $widget) {
            $widget->setPage($this);
        }

        return $this;
    }

    /**
     * Get widgets
     *
     * @return string
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * Get widgets
     *
     * @param string $slot
     * @return string
     */
    public function getWidgetsForSlot($slot)
    {
        $widgets = array();
        foreach ($this->getWidgets() as $widget) {
            if ($widget->getSlot() === $slot) {
                $widgets[] = $widget;
            }
        }

        return $widgets;
    }


    /**
     * Add widget
     *
     * @param Widget $widget
     */
    public function addWidget(Widget $widget)
    {
            $this->widgets[] = $widget;
    }
    /**
     * has widget
     *
     * @param Widget $widget
     * @return bool
     */
    public function hasWidget(Widget $widget)
    {
        return $this->widgets->contains($widget);
    }

    /**
     * Set parent
     *
     * @param parent $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return parent
     */
    public function getParent()
    {
        return $this->parent;
    }
    /**
     * Set children
     *
     * @param string $children
     * @return Page
     */
    public function setChildren($children)
    {
        $this->children = $children;
        foreach ($children as $child) {
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * Get children
     *
     * @return string
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add child
     *
     * @param child $child
     */
    public function addChild(Page $child)
    {
            $this->children[] = $child;
    }

    /**
     * Get referer
     *
     * @return string
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * Set homepage
     *
     * @param homepage $homepage
     * @return Page
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;

        return $this;
    }

    /**
     * Set homepage
     *
     * @return bool is homepage
     */
    public function isHomepage()
    {
        return $this->homepage;
    }

    /**
     * Is this page published
     *
     * @return bool is published ?
     */
    public function isPublished()
    {
        if (
            $this->getStatus() == self::STATUS_PUBLISHED ||
            $this->getStatus() == self::STATUS_SCHEDULED && $this->getPublishedAt() < new \DateTime()
            ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get homepage
     *
     * @return homepage
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * Set undeletable
     *
     * @return bool is undeletable
     */
    public function isUndeletable()
    {
        return $this->undeletable;
    }

    /**
     * Get undeletable
     *
     * @return undeletable
     */
    public function setUndeletable($undeletable)
    {
        $this->undeletable = $undeletable;

        return $this;
    }

    /**
     * Set position
     *
     * @param position $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get discriminator type
     *
     * @return integer
     */
    public function getType()
    {
        $class = get_called_class();

        return $class::TYPE;
    }

    /**
     * Set routes
     *
     * @param routes $routes
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }
    /**
     * Add route
     *
     * @param route $route
     */
    public function addRoute($route)
    {
        $this->routes[] = $route;
    }

    /**
     * Get routes
     *
     * @return routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }
    /**
     * Set url
     *
     * @param url $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set status
     *
     * @param status $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set publishedAt
     *
     * @param publishedAt $publishedAt
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * Get publishedAt
     *
     * @return publishedAt
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set author
     *
     * @param string $author
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Set widgetMap
     *
     * @param widgetMap $widgetMap
     */
    public function setWidgetMap($widgetMap)
    {
        $this->widgetMap = $widgetMap;
    }

    /**
     * Get widgetMap
     *
     * @return widgetMap
     */
    public function getWidgetMap()
    {
        return $this->widgetMap;
    }

    /**
     * Get widgetMap
     * @param string $slot
     *
     * @return widgetMap
     */
    public function getWidgetMapForSlot($slot = null)
    {
        if (empty($this->widgetMap[$slot])) {
            return null;
        }

        return $this->widgetMap[$slot];
    }

    /**
     * Get bodyId
     *
     * @return string
     */
    public function getBodyId()
    {
        return $this->bodyId;
    }

    /**
     * Set bodyId
     *
     * @param string $bodyId
     * @return $this
     */
    public function setBodyId($bodyId)
    {
        $this->bodyId = $bodyId;
        return $this;
    }

    /**
     * Get bodyClass
     *
     * @return string
     */
    public function getBodyClass()
    {
        return $this->bodyClass;
    }

    /**
     * Set bodyClass
     *
     * @param string $bodyClass
     * @return $this
     */
    public function setBodyClass($bodyClass)
    {
        $this->bodyClass = $bodyClass;
        return $this;
    }

}