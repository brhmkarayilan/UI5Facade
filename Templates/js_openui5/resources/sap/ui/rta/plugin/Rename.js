/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2018 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['jquery.sap.global','sap/ui/rta/plugin/Plugin','sap/ui/rta/plugin/RenameHandler','sap/ui/dt/Overlay','sap/ui/dt/ElementUtil','sap/ui/dt/OverlayUtil','sap/ui/dt/OverlayRegistry','sap/ui/rta/Utils'],function(q,P,R,O,E,a,b,U){"use strict";var c=P.extend("sap.ui.rta.plugin.Rename",{metadata:{library:"sap.ui.rta",properties:{oldValue:"string"},associations:{},events:{"editable":{},"nonEditable":{}}}});c.prototype.exit=function(){P.prototype.exit.apply(this,arguments);R._exit.call(this);};c.prototype.setDesignTime=function(d){R._setDesignTime.call(this,d);};c.prototype.startEdit=function(o){this._bPreventMenu=true;var e=o.getElement(),d=o.getDesignTimeMetadata(),D=d.getAction("rename",e).domRef;R.startEdit.call(this,{overlay:o,domRef:D,pluginMethodName:"plugin.Rename.startEdit"});};c.prototype.stopEdit=function(r){this._bPreventMenu=false;R._stopEdit.call(this,r,"plugin.Rename.stopEdit");};c.prototype.handler=function(o){this.startEdit(o[0]);};c.prototype.isRenameAvailable=function(o){return this._isEditableByPlugin(o);};c.prototype.isRenameEnabled=function(o){return this.isEnabled(o);};c.prototype.isEnabled=function(o){var i=true;var A=this.getAction(o);if(!A){i=false;}if(i&&typeof A.isEnabled!=="undefined"){if(typeof A.isEnabled==="function"){i=A.isEnabled(o.getElement());}else{i=A.isEnabled;}}if(i){var d=o.getDesignTimeMetadata();if(!d.getAssociatedDomRef(o.getElement(),A.domRef)){i=false;}}return i&&this.isMultiSelectionInactive.call(this,o);};c.prototype.registerElementOverlay=function(o){o.attachEvent("editableChange",R._manageClickEvent,this);P.prototype.registerElementOverlay.apply(this,arguments);};c.prototype._isEditable=function(o){var e=false;var d=o.getElement();var r=this.getAction(o);if(r&&r.changeType){if(r.changeOnRelevantContainer){d=o.getRelevantContainer();}e=this.hasChangeHandler(r.changeType,d);}if(e){return this.hasStableId(o);}return e;};c.prototype.deregisterElementOverlay=function(o){o.detachEvent("editableChange",R._manageClickEvent,this);o.detachBrowserEvent("click",R._onClick,this);this.removeFromPluginsList(o);};c.prototype._emitLabelChangeEvent=function(){var t=R._getCurrentEditableFieldText.call(this);if(this.getOldValue()!==t){this._$oEditableControlDomRef.text(t);try{var r;var o=this._oEditedOverlay.getElement();var d=this._oEditedOverlay.getDesignTimeMetadata();var e=this.getAction(this._oEditedOverlay);var v=this.getVariantManagementReference(this._oEditedOverlay,e);r=this.getCommandFactory().getCommandFor(o,"rename",{renamedElement:o,newValue:t},d,v);this.fireElementModified({"command":r});}catch(f){q.sap.log.error("Error during rename : ",f);}}};c.prototype.getMenuItems=function(o){return this._getMenuItems(o,{pluginId:"CTX_RENAME",rank:10,icon:"sap-icon://edit"});};c.prototype.getActionName=function(){return"rename";};c.prototype.isBusy=function(){return this._bPreventMenu;};return c;},true);