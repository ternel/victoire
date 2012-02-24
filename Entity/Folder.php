<?php

namespace  Kunstmaan\MediaBundle\Entity;

use Doctrine\ORM\EntityManager;
use Kunstmaan\AdminBundle\Modules\Slugifier;
use Kunstmaan\MediaBundle\Helper\FolderStrategy;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * Class that defines a folder from the MediaBundle in the database
 *
 * @author Kristof Van Cauwenbergh
 *
 * @ORM\Entity(repositoryClass="Kunstmaan\MediaBundle\Repository\FolderRepository")
 * @ORM\Table(name="media_folder")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({ "folder"="Folder", "imagegallery" = "ImageGallery", "filegallery" = "FileGallery", "slidegallery" = "SlideGallery" , "videogallery" = "VideoGallery"})
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 */
class Folder{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
   /**
    * @Gedmo\Translatable
    * @ORM\Column(type="string")
    */
    protected $name;
    
    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    protected $locale;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $slug;

    /**
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="children", fetch="EAGER")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Folder", mappedBy="parent", fetch="EAGER")
     * @ORM\OrderBy({"sequencenumber" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="Media", mappedBy="gallery")
     */
    protected $files;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;
    
    /**
     * @ORM\Column(type="boolean")
     */
	protected $candelete;
	
	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $rel;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $sequencenumber;
    
	protected $em;
	
    public function __construct(EntityManager $em)
    {
    	$this->em = $em;
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
        $this->setCanDelete(true);
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function setName($name){
        $this->name = $name;
        $this->setSlug($this->name);
    }

    public function getName(){
        return $this->name;
    }
    
    public function setTranslatableLocale($locale)
    {
    	$this->locale = $locale;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
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
    
    public function setCanDelete($bool){
    	$this->candelete = $bool;
    }
    
    public function canDelete(){
    	return $this->candelete;
    }
    
    public function setRel($rel){
    	$this->rel = $rel;
    }
    
    public function getRel(){
    	return $this->rel;
    }
 
    /**
      * Set created
      *
      * @param datetime $created
      */
     public function setCreated($created)
     {
         $this->created = $created;
     }

     /**
      * Get created
      *
      * @return datetime
      */
     public function getCreated()
     {
         return $this->created;
     }

     /**
      * Set updated
      *
      * @param datetime $updated
      */
     public function setUpdated($updated)
     {
         $this->updated = $updated;
     }

     /**
      * Get updated
      *
      * @return datetime
      */
     public function getUpdated()
     {
         return $this->updated;
     }

    /**
     * Set parent
     *
     * @param Kunstmaan\MediaBundle\Entity\Folder $parent
     */
    public function setParent(Folder $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Kunstmaan\MediaBundle\Entity\Folder 
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function getParents()
    {
        $parent = $this->getParent();
        $parents=array();
        while($parent!=null){
            $parents[] = $parent;
            $parent = $parent->getParent();
        }
        return array_reverse($parents);
    }

    /**
     * Add children
     *
     * @param Kunstmaan\MediaBundle\Entity\Gallery $children
     */
    public function addGallery(Folder $children)
    {
        $this->children[] = $children;
    }

     /**
      * Add children
      *
      * @param \Kunstmaan\MediaBundle\Entity\ImageGallery $children
      */
     public function addChild(Folder $child){
         $this->children[] = $child;
         $child->setParent($this);
     }


     public function getChildren(){
         return $this->children;
     }
     
     public function getNextSequence(){
     	$children = $this->getChildren();
     	$count = 0;
     	foreach($children as $child){
     		$count++;
     	}
     	return $count + 1;
     }

     public function setChildren($children){
         $this->children = $children;
     }
     
     public function getSequencenumber(){
     	return $this->sequencenumber;
     }
     
     public function setSequencenumber($sequencenumber){
     	$this->sequencenumber = $sequencenumber;
     }

     public function disableChildrenLazyLoading()
     {
         if (is_object($this->children)) {
             $this->children->setInitialized(true);
         }
     }

    /**
     * Add files
     *
     * @param Kunstmaan\MediaBundle\Entity\Media $files
     */
    public function addMedia(Media $files)
    {
        $this->files[] = $files;
    }

    /**
     * Get files
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getFiles()
    {
        return $this->files;
    }
    
    /**
     * Get images
     *
     * @return Doctrine\Common\Collections\images
     */
    public function getImages()
    {
    	$all = $this->files;
    	$images = array();
    	foreach($all as $file){
    		if($file instanceof Image){
    			$images[] = $file;
    		}
    	}
    	return $images;
    }
    
    public function getFilesOnly()
    {
    	$all = $this->files;
    	$files = array();
    	foreach($all as $file){
    		if($file instanceof File){
    			$files[] = $file;
    		}
    	}
    	return $files;
    }
    
    public function getSlidesOnly()
    {
    	$all = $this->files;
    	$files = array();
    	foreach($all as $file){
    		if($file instanceof Slide){
    			$files[] = $file;
    		}
    	}
    	return $files;
    }
    
    public function getVideosOnly()
    {
    	$all = $this->files;
    	$files = array();
    	foreach($all as $file){
    		if($file instanceof Video){
    			$files[] = $file;
    		}
    	}
    	return $files;
    }
    
    public function hasActive($id){
    	$bool = false;
    	foreach($this->getChildren() as $child){
    		$bool = $child->hasActive($id);
    		if($bool == true) return true;
    		if($child->getId()==$id) return true;
    	}
    	return false;
    }
    
    public function getStrategy(){
    	return new FolderStrategy();
    }
    
    public function getFormType($gallery = null)
    {
    	return new \Kunstmaan\MediaBundle\Form\FolderType($this->getStrategy()->getGalleryClassName(), $gallery);
    }
    
    public function getType()
    {
    	return $this->getStrategy()->getType();
    }

    /**
     * @return string
     */
    public function __toString()
    {
    	return $this->getName();
    }
    
    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setSlug(Slugifier::slugify($this->getName()));
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->setUpdated(new \DateTime());
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemove()
    {

    }
    
    /**
     * @ORM\PrePersist
     */
    public function preInsert(){
    	if(!$this->sequencenumber){
	    	$parent = $this->getParent();
	    	if($parent){
	    		$count = $parent->getChildren()->count();
	    		$this->sequencenumber = $count+1;
	    	}else $this->sequencenumber = 1;
    	}
    }
}