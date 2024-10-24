<?php
namespace exface\UI5Facade\Facades\Elements;
use exface\Core\DataTypes\StringDataType;
use exface\Core\Widgets\Tile;
use exface\Core\Widgets\Tiles;

/**
 * Renders a default container for NavTiles.
 * 
 * @method \exface\Core\Widgets\NavTiles getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class UI5NavTiles extends UI5Container
{
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Container::buildJsConstructor()
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        // If the NavTiles is the root widget of a view, it will have a header with the caption
        // of the first tile group - so just hide the caption of that group to avoid duplicates.
        $widget = $this->getWidget();
        if ($widget->hasParent() === false && $widget->hasWidgets()) {
            $widget->getWidgetFirst()->setHideCaption(true);
        }
        if ($widget->isHiddenIfEmpty() && $widget->countWidgetsVisible() === 0) {
            return '';
        }
        return parent::buildJsConstructor($oControllerJs);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Container::buildJsChildrenConstructors()
     */
    public function buildJsChildrenConstructors() : string
    {
        if ($this->getWidget()->isEmpty()) {
            return <<<JS

            new sap.m.FlexBox({
                height: "100%",
                width: "100%",
                justifyContent: "Center",
                alignItems: "Center",
                items: [
                    new sap.m.Text({
                        text: "{$this->getWidget()->getEmptyText()}"
                    })
                ]
            })

JS;
        }
        if ($this->hasIconTabBar() === true) {
            $navbar = $this->buildJsIconTabBar();
        } else {
            '';
        }
        return $navbar . ', ' . parent::buildJsChildrenConstructors();
    }

    protected function buildJsIconTabBar() : string
    {
        $this->getController()->addOnEventScript($this, 'TabSelect', <<<JS

            var oPanel;
            // get the selected key
            var sKey = oEvent.getParameter("key");

             // Find the corresponding panel that matches the key dynamically
            var oView = this.getView();
            oPanel = sap.ui.getCore().byId(sKey);
            if (oPanel && oPanel.getDomRef()) {
                oPanel.getDomRef().scrollIntoView({ behavior: "smooth" })
            }
JS);

        $this->getController()->addOnEventScript($this, 'FilterTiles', <<<JS
            console.log('searching');
JS);

        return <<<JS

        new sap.m.FlexBox("{$this->getId()}_navbox", {
            justifyContent: "SpaceBetween",
            backgroundDesign: "Solid",
            items: [
                new sap.m.IconTabHeader("{$this->getId()}_iconTabHeader", {
                    mode: "Inline",
                    select: {$this->getController()->buildJsEventHandler($this, 'TabSelect', true)},
                    items: [
                        {$this->buildJsIconTabBarItems()}
                    ]
                }).addStyleClass('customHeader'),
                new sap.m.SearchField({
                    placeholder: "Search...",
                    liveChange: {$this->getController()->buildJsEventHandler($this, 'FilterTiles', true)},
                    width: "100%"
                }).addStyleClass('customSearchField sapUiNoContentPadding')
            ]
        }).addStyleClass('responsiveFlexbox')
JS;
    }

    protected function buildJsIconTabBarItems() : string
    {
        $js = '';
        foreach ($this->getWidget()->getTiles() as $i => $tileGroup) {
            if ($i === 0) {
                $tileGroup->setHidden(true);
                continue;
            }
            $js .= $this->buildJsIconTabBarItem($tileGroup);
        }
        return $js;
    }

    protected function buildJsIconTabBarItem(Tiles $tileGroup) : string
    {
        $tabCaption = StringDataType::substringAfter($tileGroup->getCaption(), ' > ');
        $tabElement = $this->getFacade()->getElement($tileGroup);
        return <<<JS

                new sap.m.IconTabFilter({
                    key: "{$tabElement->getId()}",
                    text: "{$tabCaption}"
                }),
JS;
    }

    protected function hasIconTabBar() : bool
    {
        //return $this->getWidget()->getDepth() > 1;
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement::buildCssElementClass()
     */
    public function buildCssElementClass()
    {
        return 'exf-navtiles' . ($this->isFillingContainer() ? ' exf-panel-no-border' : '');
    }
}