<?php

namespace Victoire\Bundle\QueryBundle\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage;
use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;

/**
 * The QueryHelper helps to build query in Victoire's components
 * ref: victoire_query.query_helper
 */
class QueryHelper
{
    protected $entityManager = null;
    protected $businessEntityHelper = null;
    protected $currentView;

    /**
     * Constructor
     *
     * @param EntityManager        $entityManager
     * @param BusinessEntityHelper $businessEntityHelper
     * @param CurrentViewHelper    $currentView
     */
    public function __construct(EntityManager $entityManager, BusinessEntityHelper $businessEntityHelper, CurrentViewHelper $currentView)
    {
        $this->entityManager = $entityManager;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->currentView = $currentView;
    }

    /**
     * Get the query builder base. This makes a "select  from item XXX"
     * use the item for doing the left join or where dql
     *
     * @param \Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern $containerEntity
     *
     * @return QueryBuilder
     *
     * @throws Exception
     */
    public function getQueryBuilder($containerEntity)
    {
        //services
        $entityManager = $this->entityManager;
        $businessEntityHelper = $this->businessEntityHelper;

        if ($containerEntity === null) {
            throw new \Exception('The container entity parameter must not be null.');
        }

        //verify that the object has the query trait
        $this->checkObjectHasQueryTrait($containerEntity);

        //the business name of the container entity
        $businessEntityName = $containerEntity->getBusinessEntityName();

        //test that there is a business entity name
        if ($businessEntityName === null || $businessEntityName === '') {
            $containerId = $containerEntity->getId();
            throw new \Exception('The container entity ['.$containerId.'] does not have any businessEntityName.');
        }

        //the business class of the container entity
        $businessEntity = $businessEntityHelper->findById($businessEntityName);

        //test that there was a businessEntity
        if ($businessEntity === null) {
            throw new \Exception('The business entity was not found for the id:['.$businessEntityName.']');
        }

        $businessClass = $businessEntity->getClass();

        $itemsQueryBuilder = $entityManager
            ->createQueryBuilder()
            ->select('main_item')
            ->from($businessClass, 'main_item');

        return $itemsQueryBuilder;
    }

    /**
     * Check that the object is not null and has the query trait
     * @param \Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern $containerEntity
     *
     * @throws \Exception
     */
    protected function checkObjectHasQueryTrait($containerEntity)
    {
        if ($containerEntity === null) {
            throw new \Exception('The container entity parameter must not be null.');
        }

        //test that the containerEntity has the trait
        if (!method_exists($containerEntity, 'getQuery') || !method_exists($containerEntity, 'getBusinessEntityName')) {
            throw new \Exception('The object '.get_class($containerEntity).' does not have the QueryTrait.');
        }
    }

    /**
     * Get the results from the sql after adding the
     *
     * @param \Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern $containerEntity
     * @param QueryBuilder $itemsQueryBuilder
     *
     * @throws \Exception
     *
     * @return QueryBuilder The QB to list of objects
     */
    public function buildWithSubQuery($containerEntity, QueryBuilder $itemsQueryBuilder)
    {
        //services
        $entityManager = $this->entityManager;

        //test the container entity
        if ($containerEntity === null) {
            throw new \Exception('The container entity parameter must not be null.');
        }

        //verify that the object has the query trait
        $this->checkObjectHasQueryTrait($containerEntity);

        //get the query of the container entity
        $query = $containerEntity->getQuery();
        if ($query !== '' && $query !== null) {

            $subQuery = $this->entityManager->createQueryBuilder()
                                ->select('item.id')
                                ->from($itemsQueryBuilder->getRootEntities()[0], 'item');

            $itemsQueryBuilder
                ->andWhere('main_item.id IN ('.$subQuery->getQuery()->getDql().' '.$query.')');
        }
        $currentView = $this->currentView;

        // If the current page is a BEP, we parse all its properties and inject them as query parameters
        if ($currentView() && $currentView() instanceof BusinessEntityPage && null !== $currentEntity = $currentView()->getBusinessEntity()) {

            // NEW
            $metadatas = $this->entityManager->getClassMetadata(get_class($currentEntity));
            foreach ($metadatas->fieldMappings as $fieldName => $field) {
                if (strpos($query, ":".$fieldName) !== false) {
                    $itemsQueryBuilder->setParameter($fieldName, $metadatas->getFieldValue($currentEntity, $fieldName));
                }
            }
            foreach ($metadatas->associationMappings as $fieldName => $field) {
                if (strpos($query, ":".$fieldName) !== false) {
                    $itemsQueryBuilder->setParameter($fieldName, $metadatas->getFieldValue($currentEntity, $fieldName)->getId());
                }
            }

            if (strpos($query, ":currentEntity") !== false) {
                $itemsQueryBuilder->setParameter('currentEntity', $currentEntity->getId());
            }
        }

        return $itemsQueryBuilder;
    }
}
