<?php
namespace exface\OpenUI5Template\Template\Elements;

/**
 * Generates OpenUI5 inputs
 *
 * @author Andrej Kabachnik
 *        
 */
class ui5Input extends ui5Text
{
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\OpenUI5Template\Template\Elements\ui5Text::buildJsControlConstructor()
     */
    protected function buildJsControlConstructor()
    {
        return <<<JS
        new sap.m.Input("{$this->getId()}", {
            {$this->buildJsProperties()}
        })
JS;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\OpenUI5Template\Template\Elements\ui5AbstractElement::buildJsProperties()
     */
    public function buildJsProperties()
    {
        if ($height = $this->getHeight()) {
            $height_option = 'height: "' . $height . '",';
        }
        $options = parent::buildJsProperties() . '
            width: "100%",
            required: ' . ($this->getWidget()->isRequired() ? 'true' : 'false') . ',
            ' . $this->buildJsPropertyValue() . '
            ' . $height_option . '
            ' . $this->buildJsPropertyVisibile();
        return $options;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::getHeight()
     */
    public function getHeight()
    {
        if ($this->getWidget()->getHeight()->isUndefined()) {
            return '';
        }
        return parent::getHeight();
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsPropertyValue()
    {
        $value = $this->getWidget()->getValueWithDefaults();
        return ($value ? 'value: "' . $this->buildJsTextValue($value) . '",' : '');
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsPropertyEditable()
    {
        return 'editable: true, ';
    }
    
    protected function buildJsPropertyType()
    {
        // TODO derive other types from data type
        return 'Text';
    }
}
?>