<?php

namespace Victoire\Bundle\CoreBundle\Form\Builder;

use Victoire\Bundle\CoreBundle\Annotations\Reader\AnnotationReader;

/**
 * Edit Page Type
 * @author lenybernard
 */
class EntityProxyFieldsBuilder
{
    private $annotationReader;

    /**
     * define form fields
     */
    public function __construct(AnnotationReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Build
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param string                                       $namespace
     *
     * @return array The all list of fields type to add for the entity namespace given
     */
    public function buildForEntityAndWidgetType(&$builder, $widgetType, $namespace)
    {
        //Try to add a new form for each entity with the correct annotation and business properties
        $businessProperties = $this->annotationReader->getBusinessProperties($namespace);
        $receiverProperties = $this->annotationReader->getReceiverProperties();

        if (!empty($receiverProperties[$widgetType])) {
            foreach ($receiverProperties[$widgetType] as $key => $_fields) {
                foreach ($_fields as $fieldKey => $fieldVal) {
                    //Check if entity has all the required receiver properties as business properties
                    if (isset($businessProperties[$key]) && is_array($businessProperties[$key]) && count($businessProperties[$key])) {
                        //Create form types with field as key and values as choices
                        //TODO Add some formatter Class or a buildField method responsible to create this type
                        $builder->add($fieldKey, 'choice', array(
                            'choices' => $businessProperties[$key],
                            'label' => 'widget_'.strtolower($widgetType).'.form.'.$fieldKey.'.label',
                        ));
                    } else {
                        throw new \Exception(sprintf('The Entity %s doesn\'t have a %s property, which is required by %s widget', $namespace, $key, $widgetType));
                    }
                }
            }
        }
    }
}
