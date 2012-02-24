<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kristof
 * Date: 15/11/11
 * Time: 22:23
 * To change this template use File | Settings | File Templates.
 */

namespace Kunstmaan\MediaBundle\Helper\MediaList;

use Kunstmaan\AdminListBundle\AdminList\AbstractAdminListConfigurator;
use Kunstmaan\AdminListBundle\AdminList\AdminListFilter;
use Kunstmaan\AdminListBundle\AdminList\FilterDefinitions\StringFilterType;
use Kunstmaan\AdminListBundle\AdminList\FilterDefinitions\BooleanFilterType;

class FileListConfigurator extends AbstractAdminListConfigurator{

    public function buildFilters(AdminListFilter $builder){
        $builder->add('name', new StringFilterType("name"), "form.name");
        $builder->add('contentType', new StringFilterType("contentType"), "form.type");
    }

    public function buildFields()
    {
    	$this->addField("name", "form.name", true);
    	$this->addField("contentType", "form.type", true);
    	$this->addField("createdAt", "form.createdat", true);
    	$this->addField("updatedAt", "form.updatedat", true); 
    }

	public function canAdd() {
        return true;
    }

    public function getAddUrlFor($params=array()) {
    	return array('file' => array('path' => 'KunstmaanMediaBundle_folder_filecreate', 'params' => array( 'gallery_id' => $params['gallery_id'])));
    }

    public function canEdit() {
    	return true;
    }
    
    public function getEditUrlFor($item) {
    	return array('path' => 'KunstmaanMediaBundle_media_show', 'params' => array( 'media_id' => $item->getId()));
    }

    public function canDelete($item) {
        return true;
    }

    public function getRepositoryName(){
        return 'KunstmaanMediaBundle:File';
    }

    function adaptQueryBuilder($querybuilder, $params=array()){
        parent::adaptQueryBuilder($querybuilder, $params);
        $querybuilder->andwhere($querybuilder->expr()->eq("b.gallery", $params['gallery']));
    }
}