<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Interfaces\Widgets\iHaveFilters;
use exface\Core\Widgets\Dashboard;
use exface\UI5Facade\Facades\Elements\UI5AbstractElement;
use exface\Core\Facades\AbstractAjaxFacade\Interfaces\AjaxFacadeElementInterface;

/**
 * Renders a sap.f.DynamicPage for any Container
 * 
 * @author Andrej Kabachnik
 *
 * @method \exface\UI5FAcade\Facades\UI5Facade getFacade()
 */
class UI5DynamicPage extends UI5AbstractElement 
{    
    private $headerCollapsed = null;
    
    private $headerShowToolbar = false;

    private $content = null;

    private $headerToolbarJs = null;

    private $headerContentSnapped = null;

    private $id = null;
    
    /**
     * 
     *
     * @param string $content
     * @return string
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        $content = $this->buildJsConstructorsForContent() ?? '';
        $widget = $this->getWidget();

        // If the data widget is the root of the page, prefill data from the URL can be used
        // to prefill filters. The default prefill-logic of the view will not work, however,
        // because it will load data into the view's default model and this will not have any
        // effect on the table because it's default model is a different one. Thus, we need
        // to do the prefill manually at this point. 
        // If the widget is not the root, the URL prefill will be applied to the view normally
        // and it will work fine. 
        if ($widget->hasParent() === false) {
            $this->getController()->addOnInitScript($this->buildJsPrefillFiltersFromRouteParams());
        }
        
        $toolbar = $this->buildJsHeaderToolbarButtons() ?? '';
        
        // Add a title. If the dynamic page is actually the view, the title should be the name
        // of the page, the view represents - otherwise it's the caption of the table widget.
        // Since the back-button is also only shown when the dynamic page is the view itself,
        // we can use the corresponding getter here.
        $caption = $this->hasBackButton() ? $widget->getPage()->getName() : $this->getCaption();
        $title = <<<JS
        
                            new sap.m.Title({
                                text: "{$this->escapeJsTextValue($caption)}"
                            }),
                            
JS;
        
        // Place the back-button next to the title if we need one
        $backButton = <<<JS
                                    new sap.m.Button({
                                        icon: "sap-icon://nav-back",
                                        press: [oController.navBack, oController],
                                        type: sap.m.ButtonType.Transparent
                                    }).addStyleClass('exf-page-heading-btn'),
JS;
        if ($widget->getHideCaption() === true) {
            $title = '';
        }
        if ($this->hasBackButton() === false) {
            $backButton = '';
        }
        $titleExpanded = $this->buildJsHeaderTitle($title, $backButton);
        $textSnapped = $this->buildJsHeaderContentSnapped() ?? '';
        $titleSnapped = $this->buildJsHeaderTitle($textSnapped, $backButton);
        
        // Now build the page's code for the view
        return <<<JS
        
        new sap.f.DynamicPage("{$this->getId()}", {
            {$this->buildJsPropertyVisibile()}
            fitContent: true,
            preserveHeaderStateOnScroll: true,
            headerExpanded: (sap.ui.Device.system.phone === false),
            title: new sap.f.DynamicPageTitle({
				expandedHeading: [
                    {$titleExpanded}
				],
                snappedHeading: [
                    {$titleSnapped}
				],
				actions: [
				    {$toolbar}
				]
            }),
            
			header: new sap.f.DynamicPageHeader({
                pinnable: true,
				content: [
                    new sap.ui.layout.Grid({
                        defaultSpan: "XL2 L3 M4 S12",
                        containerQuery: true,
                        content: [
							{$this->getConfiguratorElement()->buildJsFilters()}
						]
                    })
				]
			}),
			
            content: [
                {$content}
            ]
        }).addStyleClass('{$this->buildCssElementClass()}')
JS;
    }
    
    protected function buildJsHeaderTitle(string $title, string $backButton) : string
    {
        return <<<JS
                            new sap.m.HBox({
                                height: "1.625rem",
                                renderType: 'Bare',
                                alignItems: 'Center',
                                items: [
                                    {$backButton}
                                    {$title}
                                ]
                            })
JS;
    }
    
    /**
     *
     * @return string
     */
    protected function buildJsFilterSummaryFunctionName() 
    {
        return "{$this->buildJsFunctionPrefix()}CountFilters";
    }
    
    protected function getConfiguratorElement() : UI5AbstractElement
    {
        return $this->getFacade()->getElement($this->getConfiguratorWidget());
    }

    protected function getConfiguratorWidget() : iHaveFilters
    {
        return $this->getWidget()->getConfiguratorWidget();
    }
    
    /**
     * Returns whether the dynamic page header should be collapsed or not, or if this has not been defined for this object.
     * 
     * @return bool|NULL
     */
    protected function isHeaderCollapsed() : ?bool
    {
        return $this->headerCollapsed;
    }
    
    /**
     * Set whether the dynamic page header of this widget should be collapsed or not.
     * 
     * @param bool $value
     * @return self
     */
    public function setHeaderCollapsed(bool $value) : AjaxFacadeElementInterface
    {
        $this->headerCollapsed = $value;
        return $this;
    }
    
    /**
     * Getter for whether the back button of this page should be instanciated or not, or if this has not been defined.
     * 
     * @return bool
     */
    protected function hasBackButton() : bool
    {
        // No back-button if we are on the root view (there is nowhere to go back)
        if ($this->getView()->isWebAppRoot()) {
            return false;
        }
        
        // Show back-button if the table is the view root (and the view is not app root - see above)
        $viewRootEl = $this->getView()->getRootElement();
        if ($viewRootEl === $this) {
            return true;
        }
        
        // In all other cases see if any parent already has a back-button
        $parent = $this->getWidget()->getParent();
        while ($parent) {
            $parentEl = $this->getFacade()->getElement($parent);
            // If back-button found - don't show another one
            if ($parentEl->hasButtonBack() === true) {
                return false;
            }
            // If we reached the view root, stop looking (otherwise we will get a controller
            // not initialized exception!)
            if ($parentEl === $viewRootEl) {
                break;
            }
            // Next parent
            $parent = $parent->getParent();
        }
        
        // If no parent has a back-button, place one here
        return true;
    }
    
    /**
     * Setter for whether the toolbar for this page should be displayed or not.
     * 
     * @param bool $trueOrFalse
     * @return self
     */
    public function setShowToolbar(bool $trueOrFalse) : AjaxFacadeElementInterface
    {
        $this->headerShowToolbar = $trueOrFalse;
        return $this;
    }
    
    /**
     * Getter for whether the toolbar for this page should be displayed or not.
     * 
     * @return bool
     */
    protected function hasHeaderToolbar() : bool
    {
        return $this->headerShowToolbar;
    }

    /**
     * 
     * @param string $constructorJs
     * @return \exface\UI5Facade\Facades\Elements\Traits\UI5DynamicPage
     */
    public function setContentJs(string $constructorJs) : UI5DynamicPage
    {
        $this->content = $constructorJs;
        return $this;
    }

    /**
     * 
     * @return string
     */
    protected function buildJsConstructorsForContent() : ?string
    {
        return $this->content;
    }

    public function setHeaderToolbarJs(string $buttonConstructors) : UI5DynamicPage
    {
        $this->headerToolbarJs = $buttonConstructors;
        return $this;
    }

    protected function buildJsHeaderToolbarButtons() : ?string
    {
        return $this->headerToolbarJs;
    }

    public function setHeaderContentSnapped(string $constructors) : UI5DynamicPage
    {
        $this->headerContentSnapped = $constructors;
        return $this;
    }

    protected function buildJsHeaderContentSnapped() : ?string
    {
        return $this->headerContentSnapped;
    }

    public function setId(string $id) : UI5DynamicPage
    {
        $this->id = $id;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id ?? parent::getId();
    }

    
    
    /**
     * Returns the JS code to give filters default values if there is prefill data
     * @return string
     */
    protected function buildJsPrefillFiltersFromRouteParams() : string
    {
        $filters = $this->getConfiguratorWidget()->getFilters();
        foreach ($filters as $filter) {
            $alias = $filter->getAttributeAlias();
            $setFilterValues .= <<<JS
                
                                var alias = '{$alias}';
                                if (cond.expression === alias) {
                                    var condVal = cond.value;
                                    {$this->getFacade()->getElement($filter)->buildJsValueSetter('condVal')}
                                }
                                
JS;
        }
            
        return <<<JS

                setTimeout(function(){
                    var oViewModel = sap.ui.getCore().byId("{$this->getId()}").getModel("view");
                    var fnPrefillFilters = function() {
                        var oRouteData = oViewModel.getProperty('/_route');
                        if (oRouteData === undefined) return;
                        if (oRouteData.params === undefined) return;
                        
                        var oPrefillData = oRouteData.params.prefill;
                        if (oPrefillData === undefined) return;

                        if (oPrefillData.oId !== undefined && oPrefillData.filters !== undefined) {
                            var oId = oPrefillData.oId;
                            var routeFilters = oPrefillData.filters;
                            if (oId === '{$this->getWidget()->getMetaObject()->getId()}') {
                                if (Array.isArray(routeFilters.conditions)) {
                                    routeFilters.conditions.forEach(function (cond) {
                                        {$setFilterValues}
                                    })
                                }
                            }
                        }
                    };
                    var sPendingPropery = "/_prefill/pending";
                    if (oViewModel.getProperty(sPendingPropery) === true) {
                        var oPrefillBinding = new sap.ui.model.Binding(oViewModel, sPendingPropery, oViewModel.getContext(sPendingPropery));
                        var fnPrefillHandler = function(oEvent) {
                            oPrefillBinding.detachChange(fnPrefillHandler);
                            setTimeout(function() {
                                fnPrefillFilters();
                            }, 0);
                        };
                        oPrefillBinding.attachChange(fnPrefillHandler);
                        return;
                    } else {
                        fnPrefillFilters();
                    }
                }, 0);
                
JS;
    }

    public function buildCssElementClass()
    {
        $widget = $this->getWidget();
        return ($widget instanceof Dashboard) && $widget->getLayoutType() === Dashboard::LAYOUT_SPLIT ? 'sapUiNoContentPadding' : '';
    }
}