/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2018 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["sap/ui/thirdparty/jquery","sap/ui/fl/Utils","sap/ui/base/EventProvider","sap/ui/fl/registry/ChangeRegistry","sap/ui/fl/core/FlexVisualizer"],function(q,U,E,C,F){"use strict";var a=function(c,s){if(!c){U.log.error("sap.ui.fl.core.EventDelegate: Control required");}if(!s){U.log.error("sap.ui.fl.core.EventDelegate: Supported registry items required");}E.apply(this);this._oControl=c;this._oSupportedRegistryItems=s;};a.prototype=Object.create(E.prototype||null);a.registerControl=function(c){if(c){var i=0;if(c.aDelegates){for(i=0;i<c.aDelegates.length;i++){var t="";if(c.aDelegates[i].oDelegate&&c.aDelegates[i].oDelegate.getType){t=(c.aDelegates[i].oDelegate.getType());}if(t==="Flexibility"){return;}}}a.registerExplicitChanges(c);}};a.registerExplicitChanges=function(c){var r=C.getInstance();var p={controlType:U.getControlType(c)};var s=r.getRegistryItems(p);if(Object.keys(s).length>0){c.addEventDelegate(new a(c,s));}};a.unregisterControl=function(){};a.prototype.onmouseover=function(e){e.stopPropagation();if(e.handled){return;}else{e.handled=true;}if(F.isPersonalizationMode()){if(this._oControl&&!q(this._oControl.getDomRef()).hasClass("sapuiflex-highlight")){F.showDialog(this._oControl);}}};a.prototype.onmouseout=function(e){if(F.isPersonalizationMode()){if(this._oControl){F.closeDialog();}}};return a;},true);