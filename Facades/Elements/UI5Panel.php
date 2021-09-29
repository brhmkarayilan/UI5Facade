<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Interfaces\Widgets\iFillEntireContainer;
use exface\UI5Facade\Facades\Interfaces\UI5ControlWithToolbarInterface;
use exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryLayoutTrait;
use exface\UI5Facade\Facades\Elements\Traits\UI5HelpButtonTrait;
use exface\Core\Interfaces\Widgets\iContainOtherWidgets;
use exface\Core\Interfaces\WidgetInterface;
use exface\Core\DataTypes\StringDataType;
use exface\Core\Widgets\WidgetGroup;
use exface\UI5Facade\Facades\Interfaces\UI5CompoundControlInterface;
use exface\Core\CommonLogic\WidgetDimension;
use exface\Core\Factories\WidgetDimensionFactory;
use exface\Core\Widgets\ImageGallery;

/**
 * Generates a `sap.m.Panel` with a `sap.ui.layout.form.Form` inside for a Panel widget.
 * 
 * The `sap.ui.layout.form.Form` basically takes care of layouting the widgets inside the
 * Panel. The UI5 Form control was chosen over a generic grid to make a panel layout its children 
 * the same way as a Form widget does.  
 * 
 * **NOTE:**  the inner Form is only rendered if a layout is required - if the Panel is 
 * filled out by a single filler widget, there will be no UI5 form inside, but just the
 * UI5 control of the inner widget.
 * 
 * ## Structure
 * 
 * The UI5 Form has the following structure:
 * 
 * - sap.ui.layout.form.Form
 *      - sap.ui.layout.form.FormContainer
 *          - sap.ui.layout.form.Element
 *              - sap.m.Label
 *              - sap.m.Input
 *          - sap.ui.layout.form.Element
 *              - ...
 *      - sap.ui.layout.form.FormContainer
 *          - ...
 *          
 * A FormContainer is rendered for every group of inner widgets (e.g. a WidgetGroup or simply
 * all the inner widgets if they are not packed into WidgetGroups). A FormElement is a "line"
 * in the form, which typically consist of a value widget (rendered as sap.m.Label and its
 * actual control) or even an InlineGroup. Larger widgets like tables are also packed into
 * FormElements, but they don't have a preceeding label.
 * 
 * ## Additions and tweaks to the `sap.ui.layout.form.Form`
 * 
 * ### Width of inner widgets (FormContainer layoutData)
 * 
 * The `sap.ui.layout.form.ResponsiveGridLayout` used as the form's layout allows to change
 * the width of every FormContainer by addint a `layoutData` to `FormContainer`. 
 * 
 * However, the automatic positionion of FormContainers only works well if none of them
 * really has that `layouData`. If they do, the FormContainers strangely get equal padding on
 * either side regardless of their position in the grid. See `needsFormContainerLayoutData()` 
 * and its occurrences for more details. 
 * 
 * ### Modifying containerQuery
 * 
 * See `buildJsLaoutFormFixes()` for details.
 * 
 * ## Public methods
 * 
 * This class also provides a public methods to generate the inner layout without the Panel: 
 * `buildJsLayoutContstructor()`. This method is used for example by the `UI5Dialog` to
 * to render layouts for it's tabs and by the `UI5Tab` itself too.
 * 
 * @author Andrej Kabachnik
 * 
 * @method Panel getWidget()
 *
 */
class UI5Panel extends UI5Container
{
    use JqueryLayoutTrait;
    use UI5HelpButtonTrait;
    
    private $gridClasses = [];
    
    const FORM_MAX_CELLS = 12;
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Container::buildJsConstructor()
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        $panel = <<<JS

                new sap.m.Panel("{$this->getId()}", {
                    {$this->buildJsPropertyHeight()}
                    content: [
                        {$this->buildJsLayoutConstructor()}
                    ],
                    {$this->buildJsProperties()}
                }).addStyleClass("sapUiNoContentPadding {$this->buildCssElementClass()} {$this->buildCssGridClass()}")

JS;
        if ($this->hasPageWrapper() === true) {
            $headerContent = $this->getWidget()->getHideHelpButton() === false ? $this->buildJsHelpButtonConstructor($oControllerJs) : '';
            return $this->buildJsPageWrapper($panel, '', $headerContent);
        }
        
        return $panel;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::buildJsProperties()
     */
    public function buildJsProperties()
    {
        return parent::buildJsProperties() . $this->buildjsPropertyHeaderText();
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsPropertyHeaderText() : string
    {
        if ($this->hasHeaderToolbar() === false && $caption = $this->getCaption()) {
            return 'headerText: "' . $caption . '",';
        }
        return '';
    }
    
    /**
     * 
     * @return bool
     */
    protected function hasHeaderToolbar() : bool
    {
        return false;
    }
                
    /**
     * Returns the UI5 constructor for the inner form (or the filler widget if no form is required)
     * 
     * @param array $widgets
     * @return string
     */
    public function buildJsLayoutConstructor(array $widgets = null) : string
    {
        $widgets = $widgets ?? $this->getWidget()->getWidgets();
        
        if (! $this->isLayoutRequired()) {     
            return $this->buildJsChildrenConstructors($widgets);
        }
        
        return $this->buildJsLayoutForm($widgets);
    }
    
    /**
     *
     * @return string
     */
    public function buildJsChildrenConstructors(array $widgets = null) : string
    {
        $js = '';
        $widgets = $widgets ?? $this->getWidget()->getWidgets();
        foreach ($widgets as $widget) {
            $js .= ($js ? ",\n" : '') . $this->getFacade()->getElement($widget)->buildJsConstructor();
        }        
        return $js;
    }
    
    /**
     * The inner layout (e.g. Form) is required if the panel has multiple widgets to layout.
     * 
     * However, we still need to distinguish between smaller and larger widgets because the inner layout
     * has significant effect on the look&feel: padding, positioning, etc. Thus, we can only skip the
     * layout if the panel is filled out completely. On the other hand, if it has a single input widget,
     * it should still have a layout!
     * 
     * @return bool
     */
    protected function isLayoutRequired() : bool
    {
        $widget = $this->getWidget();        
        if ($widget->isFilledBySingleWidget()) {
            return false;
        }
        
        foreach ($widget->getWidgets() as $child) {
            if ($this->getFacade()->getElement($child)->needsContainerContentPadding() === true) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Tunes the configuration of responsive grids used in sap.ui.layout.form.Form.
     * 
     * The sap.ui.layout.form.ResponsiveGridLayout uses sap.m.Grid internally with 
     * containerQuery=true, which causes forms in small (non-maximizd) dialogs or 
     * split panels to use full-width labels. This does not look nice on large screens. 
     * Stand-alone sap.m.Grid controls have a special property `containerQuery` used to 
     * determine if a small container on a large screen is to be treated as a small screen. 
     * Unfortunately there does not seem to be a way to change that property within a form, 
     * so this method injects some hacky JS to deal with it.
     * 
     * Different UI5 facade elements may have different implementations depending on how 
     * the grid is used there.
     * 
     * @return string
     */
    protected function buildJsLayoutFormFixes() : string
    {
        $fixContainerQueryJs = <<<JS
        
                var oForm = typeof(oEvent) !== 'undefined' ? oEvent.srcControl : null;
                (function(){
                    var sFormId, sGridId, oGrid;
                    if (oForm !== null) {
                        sFormId = oEvent.srcControl.getId();
                    } else {
                        sFormId = "{$this->getId()}";
                    }
                    if (! sFormId) return;
                    
                    sGridId = $("#" + sFormId + " > .sapUiFormResGrid > .sapUiRespGrid").attr("id");
                    if (! sGridId) {
                        sGridId = $("#" + sFormId + " > .sapUiFormResGrid > .sapUiRGLContainer > .sapUiRGLContainerCont > .sapUiRespGrid").attr("id");
                    }                    
                    if (! sGridId) return;

                    oGrid = sap.ui.getCore().byId(sGridId);
                    if (oGrid !== undefined) {
                        oGrid.setContainerQuery(false);
                    }
                })();
                    
JS;
        $this->addPseudoEventHandler('onAfterRendering', $fixContainerQueryJs);
        // Also call the fix after the view was rendered because the pseudo event does not seem
        // to work on the LoginForm if placed in the app directly and not in a dialog.
        $this->getController()->addOnInitScript('setTimeout(function(){' . $fixContainerQueryJs . '}, 100);');
        
        return '';
    }
    
    /**
     * 
     * @param array $widgets
     * @param string $toolbarConstructor
     * @param string $id
     * @return string
     */
    protected function buildJsLayoutForm(array $widgets, string $toolbarConstructor = null, string $id = null)
    {
        $this->buildJsLayoutFormFixes();
        
        $cols = $this->getNumberOfColumns();
        $id = $id === null ? '' : "'{$id}',";
        
        switch ($cols) {
            case $cols > 3:
                $properties = <<<JS

                columnsXL: {$cols},
    			columnsL: 3,
    			columnsM: 2,  

JS;
            break;
            case 3:
                $properties = <<<JS
                
                columnsXL: {$cols},
    			columnsL: {$cols},
    			columnsM: 2,
    			
JS;
                break;
            default:
                $properties = <<<JS
                
                columnsXL: {$cols},
    			columnsL: {$cols},
    			columnsM: {$cols},
    			
JS;
        }
        
        if ($toolbarConstructor !== null && $toolbarConstructor !== '') {
            $toolbar = 'toolbar: ' . $toolbarConstructor;
        }
        
        $phoneLabelSpan = $this->isEditable() ? '12' : '5';
        $editable = $this->isEditable() ? 'true' : 'false';
        $content = $this->buildJsLayoutFormContent($widgets);
        
        return <<<JS
        
            new sap.ui.layout.form.Form({$id} {
                editable: $editable,
                layout: new sap.ui.layout.form.ResponsiveGridLayout('', {
                    adjustLabelSpan: true,
        			labelSpanXL: 5,
        			labelSpanL: 4,
        			labelSpanM: 4,
        			labelSpanS: {$phoneLabelSpan},
        			emptySpanXL: 0,
        			emptySpanL: 0,
        			emptySpanM: 0,
        			emptySpanS: 0,
                    {$properties}
        			singleContainerFullSize: true
                }),
                formContainers: [
                    {$content}
                ],
                {$toolbar}
            }).addStyleClass('{$this->buildCssElementClass()} {$this->buildCssGridClass()}')
            {$this->buildJsPseudoEventHandlers()}
            
JS;
    }
    
    protected function buildJsConstructorFormGroup(array $widgets, WidgetInterface $containerWidget = null) : string
    {
        $js = '';
        $nonGroupWidgets = [];
        $hiddenWidgets = [];

        foreach ($widgets as $widget) {
            if ($widget instanceof WidgetGroup || $widget instanceof iFillEntireContainer) {
                
                if (! empty($nonGroupWidgets)) {
                    $js .= $js !== '' ? ",\n" : '';
                    $js .= $this->buildJsConstructorFormContainer($nonGroupWidgets, $containerWidget);
                    $nonGroupWidgets = [];
                }
                
                $js .= $js !== '' ? ",\n" : '';
                if ($widget instanceof WidgetGroup) {
                    $js .= $this->buildJsConstructorFormGroup($widget->getWidgets(), $widget);
                } else {
                    $js .= $this->buildJsConstructorFormContainer([$widget], $containerWidget);
                }
            } else {
                if ($widget->isHidden()) {
                    $hiddenWidgets[] = $widget;
                } else {
                    $nonGroupWidgets[] = $widget;
                }
            }            
        }
        $js .= $js !== '' ? ",\n" : '';
        $nonGroupWidgets = array_merge($nonGroupWidgets, $hiddenWidgets);
        
        if (! empty($nonGroupWidgets)) {
            $js .= $this->buildJsConstructorFormContainer($nonGroupWidgets, $containerWidget);
        }
        
        return $js;
    }
    
    protected function buildJsConstructorFormContainer(array $widgets, WidgetInterface $containerWidget = null) : string
    {
        $title = '';
        $layout = '';
        $width = null;
        
        if ($containerWidget !== null && ! $containerWidget->getWidth()->isUndefined()) {
            $width = $containerWidget->getWidth(); 
        } elseif ($containerWidget instanceof WidgetGroup && $containerWidget->getWidth()->isUndefined()) {
            $width = WidgetDimensionFactory::createFromString($this->getWorkbench(), '1');
        } else {
            foreach ($widgets as $widget) {
                if (! $widget->getWidth()->isUndefined()) {
                    $width = $widget->getWidth();
                    break;
                }
            }
        }
        
        // Only add a layoutData if the width is explicitly set. Otherwise all the FormContainers
        // will get equal padding on both sides regardless of their position in the grid. See
        // `needsFormContainerLayoutData()` and its occurrences for more details.
        if ($width && $this->needsFormContainerLayoutData()) {
            $span = $this->buildJsFormGroupSpan($width);
            if ($span) {
                $layout = "layoutData: new sap.ui.layout.GridData('', {span: '{$span}'}),";
            }
        }
        
        // If we have inherited the width from the only inner widget, it should now be
        // full-width relative to the FormContainer
        if (count($widgets) === 1 && $width) {
            $widgets[0]->setWidth('100%');
        }
        
        if (count($widgets) === 1 && $widgets[0] instanceof iFillEntireContainer) {
            $this->addPseudoEventHandler('onAfterRendering', "$('#{$this->getFacade()->getElement($widgets[0])->getId()}').closest('.sapUiRGLContainer').parent().addClass('exf-formcontainer-invisible');");
        }
        
        if ($containerWidget instanceof WidgetGroup) {
            $title = $containerWidget->getCaption() ? 'text: "' . $containerWidget->getCaption() . '",' : '';
        }
        
        $title = "title: new sap.ui.core.Title({{$title}}),";
        $js .= <<<JS
    new sap.ui.layout.form.FormContainer({
        {$title}
        {$layout}
        formElements: [
            {$this->buildJsConstructorFormElement($widgets)}
        ]
    })
        
JS;
        return $js;
    }
    
    protected function buildJsConstructorFormElement(array $widgets) : string
    {
        $js = '';
        foreach ($widgets as $widget) {
            $label = '';
            $fields = '';
            $element = $this->getFacade()->getElement($widget);
            if ($element instanceof UI5CompoundControlInterface) {
                if ($widget->getHideCaption() !== true  && ! $widget->isHidden() && $labelConstructor = $element->buildJsConstructorForLabel()) {
                    $label = 'label: ' . $labelConstructor;
                }
                $element->setRenderCaptionAsLabel(false);
                $fields = $element->buildJsConstructor();
                $element->setRenderCaptionAsLabel(true);
            } else {
                $fields= $element->buildJsConstructor();
            }
            $js .= $js !== '' ? ",\n" : '';
            $js .= <<<JS
            new sap.ui.layout.form.FormElement( {
                {$label}
                fields: [
                    {$fields}
                ]
            })
JS;
        } 
        return $js;
    }
    
    protected function buildJsLayoutFormContent (array $widgets) : string
    {
        if (empty($widgets)) {
            return '';
        }
        $js = $this->buildJsConstructorFormGroup($widgets, $this->getWidget());
        return $js;
    }
    
    protected function buildJsFormGroupSpan(WidgetDimension $width) : ?string
    {
        switch (true) {
            case $width->isMax():
                $width = self::FORM_MAX_CELLS;
                return "XL{$width} L{$width} M{$width}";
            case $width->isPercentual():
                $width = StringDataType::substringBefore($width->getValue(), '%');
                $width = round(self::FORM_MAX_CELLS/100*$width);
                return "XL{$width} L{$width} M{$width}";
            case $width->isRelative():
                $columns = $this->getNumberOfColumns();
                switch($columns) {
                    case $columns > 3:
                        $colXL = $columns;
                        $colL = 3;
                        $colM = 2;
                        break;
                    case 3:
                        $colXL = $columns;
                        $colL = $columns;
                        $colM = 2;
                        break;
                    default:
                        $colXL = $columns;
                        $colL = $columns;
                        $colM = $columns;
                }
                $widthXL = round(self::FORM_MAX_CELLS/$colXL * $width->getValue());
                if ($widthXL > self::FORM_MAX_CELLS) {
                    $widthXL = self::FORM_MAX_CELLS;
                }
                $widthL = round(self::FORM_MAX_CELLS/$colL * $width->getValue());
                if ($widthL > self::FORM_MAX_CELLS) {
                    $widthL = self::FORM_MAX_CELLS;
                }
                $widthM = round(self::FORM_MAX_CELLS/$colM * $width->getValue());
                if ($widthM > self::FORM_MAX_CELLS) {
                    $widthM = self::FORM_MAX_CELLS;
                }
                return "XL{$widthXL} L{$widthL} M{$widthM}";
            default:
                return null;
        }
    }
    
    /**
     * Returns TRUE if the form/panel contains active inputs and FALSE otherwise
     * 
     * A UI5-form is marked editable if it contains at least one visible input widget.
     * Non-editable forms are more compact, so it is a good idea only to use editable
     * ones if really editing.
     * 
     * @return bool
     */
    protected function isEditable() : bool
    {
        if ($this->getWidget()->isReadonly()) {
            return false;
        }
        foreach ($this->getWidget()->getInputWidgets() as $input){
            if (! $input->isHidden() && ! $input->isReadOnly()) {
                return true;
            }
        }
        return false;
    }
                    
    /**
     * Returns the default number of columns to layout this widget.
     *
     * @return integer
     */
    public function getNumberOfColumnsByDefault() : int
    {
        return $this->getFacade()->getConfig()->getOption("WIDGET.PANEL.COLUMNS_BY_DEFAULT");
    }
    
    /**
     * Returns if the the number of columns of this widget depends on the number of columns
     * of the parent layout widget.
     *
     * @return boolean
     */
    public function inheritsNumberOfColumns() : bool
    {
        return true;
    }
    
    /**
     * The layouting is done by `sap.ui.layout.form.Form` automatically - no explicit trigger needed!
     * 
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryLayoutTrait::buildJsLayouter()
     */
    public function buildJsLayouter() : string
    {
        return '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement::buildCssElementClass()
     */
    public function buildCssElementClass()
    {
        return 'exf-panel' . ($this->needsFormContainerLayoutData() ? ' exf-form-mixed-width' : '');
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Container::buildJsPropertyHeight()
     */
    protected function buildJsPropertyHeight() : string
    {
        $widget = $this->getWidget();
        if (! $widget->getHeight()->isUndefined()) {
            return "height: '{$this->getHeight()}',";
        }
        $parent = $widget->getParent();
        if ($parent && ($parent instanceof iContainOtherWidgets)) {
            $visibleSiblingsCnt = $parent->countWidgets(function($child){
               return $child->isHidden() === false; 
            });
            if ($visibleSiblingsCnt > 1) {
                return '';
            }
        }
        return parent::buildJsPropertyHeight();
    }
    
    protected function addCssGridClass(string $className) : UI5Panel
    {
        $this->gridClasses[] = $className;
        return $this;
    }
    
    protected function buildCssGridClass() : string
    {
        return implode(' ', array_unique($this->gridClasses));
    }
    
    /**
     * Returns TRUE if at least one of the FormContainers requires a layoutData and FALSE otherwise.
     * 
     * @return bool
     */
    protected function needsFormContainerLayoutData() : bool
    {
        $customWidths = [];
        foreach ($this->getWidget()->getWidgets() as $child) {
            if (! $child->getWidth()->isUndefined()) {
                switch (true) {
                    case $child->getWidth()->isMax():
                        $val = '100%';
                        break;
                    case $child->getWidth()->getValue() == 1:
                        continue 2;
                    default:
                        $val = $child->getWidth()->getValue();
                        break;
                }
                $customWidths[] = $val;
            }
        }
        $customWidths = array_unique($customWidths);
        return ! empty($customWidths);
    }
}