/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2017 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['jquery.sap.global','./library','sap/ui/core/Control','sap/ui/core/IconPool','sap/ui/core/Icon','sap/m/Button','sap/ui/model/BindingMode','sap/ui/Device','jquery.sap.keycodes'],function(q,l,C,I,a,B,b,D){"use strict";var L=l.ListKeyboardMode;var c=l.ListMode;var d=l.ListType;var e=l.ButtonType;var f=C.extend("sap.m.ListItemBase",{metadata:{library:"sap.m",properties:{type:{type:"sap.m.ListType",group:"Misc",defaultValue:d.Inactive},visible:{type:"boolean",group:"Appearance",defaultValue:true},unread:{type:"boolean",group:"Misc",defaultValue:false},selected:{type:"boolean",defaultValue:false},counter:{type:"int",group:"Misc",defaultValue:null},highlight:{type:"sap.ui.core.MessageType",group:"Appearance",defaultValue:"None"}},associations:{ariaLabelledBy:{type:"sap.ui.core.Control",multiple:true,singularName:"ariaLabelledBy"}},events:{tap:{deprecated:true},detailTap:{deprecated:true},press:{},detailPress:{}},designTime:true}});f.getAccessibilityText=function(o,g){if(!o||!o.getVisible||!o.getVisible()){return"";}var A;if(o.getAccessibilityInfo){A=o.getAccessibilityInfo();}if(!A||!o.getAccessibilityInfo){A=this.getDefaultAccessibilityInfo(o.getDomRef());}A=q.extend({type:"",description:"",children:[]},A);var h=sap.ui.getCore().getLibraryResourceBundle("sap.m"),t=A.type+" "+A.description+" ",T=o.getTooltip_AsString();if(A.enabled===false){t+=h.getText("CONTROL_DISABLED")+" ";}if(A.editable===false){t+=h.getText("CONTROL_READONLY")+" ";}if(!A.type&&T&&t.indexOf(T)==-1){t=T+" "+t;}A.children.forEach(function(i){t+=f.getAccessibilityText(i)+" ";});t=t.trim();if(g&&!t){t=h.getText("CONTROL_EMPTY");}return t;};f.getDefaultAccessibilityInfo=function(o){if(!o){return null;}var N=window.Node,g=window.NodeFilter,t=document.createTreeWalker(o,g.SHOW_TEXT+g.SHOW_ELEMENT,function(n){if(n.type===N.ELEMENT_NODE){if(n.classList.contains("sapUiInvisibleText")){return g.FILTER_SKIP;}if(n.getAttribute("aria-hidden")=="true"||n.style.visibility=="hidden"||n.style.display=="none"){return g.FILTER_REJECT;}return g.FILTER_SKIP;}return g.FILTER_ACCEPT;},false);var T=[];while(t.nextNode()){var n=t.currentNode;if(n.nodeType===N.TEXT_NODE){var s=(n.nodeValue||"").trim();if(s){T.push(s);}}}return{description:T.join(" ")};};f.prototype.DetailIconURI=I.getIconURI("edit");f.prototype.DeleteIconURI=I.getIconURI("sys-cancel");f.prototype.NavigationIconURI=I.getIconURI("slim-arrow-right");f.prototype.TagName="li";f.prototype.init=function(){this._active=false;this._bGroupHeader=false;this._bNeedsHighlight=false;};f.prototype.onAfterRendering=function(){this.informList("DOMUpdate",true);this._checkHighlight();};f.prototype.getBindingContextPath=function(m){var o=this.getList();if(o&&!m){m=(o.getBindingInfo("items")||{}).model;}var g=this.getBindingContext(m);if(g){return g.getPath();}};f.prototype.isSelectedBoundTwoWay=function(){var o=this.getBinding("selected");if(o&&o.getBindingMode()==b.TwoWay){return true;}};f.prototype.getList=function(){var p=this.getParent();if(p instanceof sap.m.ListBase){return p;}};f.prototype.getListProperty=function(p,F){var o=this.getList();if(o){p=q.sap.charToUpperCase(p);return o["get"+p]();}return F;};f.prototype.informList=function(E,p,P){var o=this.getList();if(o){var m="onItem"+E;if(o[m]){o[m](this,p,P);}}};f.prototype.informSelectedChange=function(s){var o=this.getList();if(o){o.onItemSelectedChange(this,s);this.bSelectedDelayed=undefined;}else{this.bSelectedDelayed=s;}};f.prototype.getAccessibilityType=function(o){return o.getText("ACC_CTR_TYPE_OPTION");};f.prototype.getGroupAnnouncement=function(){return this.$().prevAll(".sapMGHLI:first").text();};f.prototype.getAccessibilityDescription=function(o){var O=[],t=d,T=this.getType(),h=this.getHighlight(),s=this.getTooltip_AsString();if(this.getSelected()){O.push(o.getText("LIST_ITEM_SELECTED"));}if(h!="None"){O.push(o.getText("LIST_ITEM_STATE_"+h.toUpperCase()));}if(this.getUnread()&&this.getListProperty("showUnread")){O.push(o.getText("LIST_ITEM_UNREAD"));}if(this.getCounter()){O.push(o.getText("LIST_ITEM_COUNTER",this.getCounter()));}if(T==t.Navigation){O.push(o.getText("LIST_ITEM_NAVIGATION"));}else{if(T==t.Detail||T==t.DetailAndActive){O.push(o.getText("LIST_ITEM_DETAIL"));}if(T==t.Active||T==t.DetailAndActive){O.push(o.getText("LIST_ITEM_ACTIVE"));}}O.push(this.getGroupAnnouncement()||"");if(this.getContentAnnouncement){O.push((this.getContentAnnouncement(o)||"").trim());}if(s){O.push(s);}return O.join(" ");};f.prototype.getAccessibilityInfo=function(){var o=sap.ui.getCore().getLibraryResourceBundle("sap.m");return{type:this.getAccessibilityType(o),description:this.getAccessibilityDescription(o),focusable:true};};f.prototype.getMode=function(){return this.getListProperty("mode","");};f.prototype.updateAccessibilityState=function(A){var t=this.$();if(!t.length){return;}var i=t.parent().children(".sapMLIB");t.attr(q.extend({"aria-setsize":i.length,"aria-posinset":i.index(t)+1},A));};f.prototype.getDeleteControl=function(){if(this._oDeleteControl){return this._oDeleteControl;}this._oDeleteControl=new B({id:this.getId()+"-imgDel",icon:this.DeleteIconURI,type:e.Transparent,tooltip:sap.ui.getCore().getLibraryResourceBundle("sap.m").getText("LIST_ITEM_DELETE")}).addStyleClass("sapMLIBIconDel sapMLIBSelectD").setParent(this,null,true).attachPress(function(E){this.informList("Delete");},this);this._oDeleteControl._bExcludeFromTabChain=true;return this._oDeleteControl;};f.prototype.getDetailControl=function(){if(this._oDetailControl){return this._oDetailControl;}this._oDetailControl=new B({id:this.getId()+"-imgDet",icon:this.DetailIconURI,type:e.Transparent,tooltip:sap.ui.getCore().getLibraryResourceBundle("sap.m").getText("LIST_ITEM_EDIT")}).addStyleClass("sapMLIBType sapMLIBIconDet").setParent(this,null,true).attachPress(function(){this.fireDetailTap();this.fireDetailPress();},this);this._oDetailControl._bExcludeFromTabChain=true;return this._oDetailControl;};f.prototype.getNavigationControl=function(){if(this._oNavigationControl){return this._oNavigationControl;}this._oNavigationControl=new a({id:this.getId()+"-imgNav",src:this.NavigationIconURI,useIconTooltip:false,noTabStop:true}).setParent(this,null,true).addStyleClass("sapMLIBType sapMLIBImgNav");return this._oNavigationControl;};f.prototype.getSingleSelectControl=function(){if(this._oSingleSelectControl){return this._oSingleSelectControl;}this._oSingleSelectControl=new sap.m.RadioButton({id:this.getId()+"-selectSingle",groupName:this.getListProperty("id")+"_selectGroup",activeHandling:false,selected:this.getSelected()}).addStyleClass("sapMLIBSelectS").setParent(this,null,true).setTabIndex(-1).attachSelect(function(E){var s=E.getParameter("selected");this.setSelected(s);this.informList("Select",s);},this);return this._oSingleSelectControl;};f.prototype.getMultiSelectControl=function(){if(this._oMultiSelectControl){return this._oMultiSelectControl;}this._oMultiSelectControl=new sap.m.CheckBox({id:this.getId()+"-selectMulti",activeHandling:false,selected:this.getSelected()}).addStyleClass("sapMLIBSelectM").setParent(this,null,true).setTabIndex(-1).attachSelect(function(E){var s=E.getParameter("selected");this.setSelected(s);this.informList("Select",s);},this);return this._oMultiSelectControl;};f.prototype.getModeControl=function(u){var m=this.getMode(),g=c;if(!m||m==g.None){return;}if(m==g.Delete){return this.getDeleteControl();}var s=null;if(m==g.MultiSelect){s=this.getMultiSelectControl();}else{s=this.getSingleSelectControl();}if(s&&u){s.setSelected(this.getSelected());}return s;};f.prototype.getTypeControl=function(){var t=this.getType(),T=d;if(t==T.Detail||t==T.DetailAndActive){return this.getDetailControl();}if(t==T.Navigation){return this.getNavigationControl();}};f.prototype.destroyControls=function(g){g.forEach(function(s){s="_o"+s+"Control";if(this[s]){this[s].destroy("KeepDom");this[s]=null;}},this);};f.prototype.isActionable=function(){return this.getListProperty("includeItemInSelection")||this.getMode()==c.SingleSelectMaster||(this.getType()!=d.Inactive&&this.getType()!=d.Detail);};f.prototype.exit=function(){this._oLastFocused=null;this._checkHighlight(false);this.setActive(false);this.destroyControls(["Delete","SingleSelect","MultiSelect","Detail","Navigation"]);};f.prototype.isSelectable=function(){var m=this.getMode();return!(m==c.None||m==c.Delete);};f.prototype.getSelected=function(){if(this.isSelectable()){return this.getProperty("selected");}return false;};f.prototype.isSelected=f.prototype.getSelected;f.prototype.setSelected=function(s,g){s=this.validateProperty("selected",s);if(!this.isSelectable()||s==this.getSelected()){return this;}if(!g){this.informSelectedChange(s);}var S=this.getModeControl();if(S){S.setSelected(s);}this.updateSelectedDOM(s,this.$());this.setProperty("selected",s,true);return this;};f.prototype.updateSelectedDOM=function(s,t){t.toggleClass("sapMLIBSelected",s);t.attr("aria-selected",s);};f.prototype.setParent=function(p){C.prototype.setParent.apply(this,arguments);if(!p){this._bGroupHeader=false;return;}this.informList("Inserted",this.bSelectedDelayed);return this;};f.prototype.setBindingContext=function(){C.prototype.setBindingContext.apply(this,arguments);this.informList("BindingContextSet");return this;};f.prototype.isGroupHeader=function(){return this._bGroupHeader;};f.prototype.isIncludedIntoSelection=function(){var m=this.getMode(),M=c;return(m==M.SingleSelectMaster||(this.getListProperty("includeItemInSelection")&&(m==M.SingleSelectLeft||m==M.SingleSelect||m==M.MultiSelect)));};f.prototype._checkHighlight=function(n){if(n==undefined){n=(this.getVisible()&&this.getHighlight()!="None");}if(this._bNeedsHighlight!=n){this._bNeedsHighlight=n;this.informList("HighlightChange",n);}};f.prototype.hasActiveType=function(){var t=d,T=this.getType();return(T==t.Active||T==t.Navigation||T==t.DetailAndActive);};f.prototype.setActive=function(A){if(A==this._active){return this;}if(A&&this.getListProperty("activeItem")){return this;}var t=this.$();this._active=A;this._activeHandling(t);if(this.getType()==d.Navigation){this._activeHandlingNav(t);}if(A){this._activeHandlingInheritor(t);}else{this._inactiveHandlingInheritor(t);}this.informList("ActiveChange",A);};f.prototype.ontap=function(E){if(this._eventHandledByControl){return;}if(this.isIncludedIntoSelection()){if(this.getMode()==c.MultiSelect){this.setSelected(!this.getSelected());this.informList("Select",this.getSelected());}else if(!this.getSelected()){this.setSelected(true);this.informList("Select",true);}}else if(this.hasActiveType()){window.clearTimeout(this._timeoutIdStart);window.clearTimeout(this._timeoutIdEnd);this.setActive(true);if(D.os.ios){this.focus();}q.sap.delayedCall(180,this,function(){this.setActive(false);});q.sap.delayedCall(0,this,function(){this.fireTap();this.firePress();});}this.informList("Press",E.srcControl);};f.prototype.ontouchstart=function(E){this._eventHandledByControl=E.isMarked();var t=E.targetTouches[0];this._touchedY=t.clientY;this._touchedX=t.clientX;if(this._eventHandledByControl||E.touches.length!=1||!this.hasActiveType()){return;}this._timeoutIdStart=q.sap.delayedCall(100,this,function(){this.setActive(true);});};f.prototype.ontouchmove=function(E){if((this._active||this._timeoutIdStart)&&(Math.abs(this._touchedY-E.targetTouches[0].clientY)>10||Math.abs(this._touchedX-E.targetTouches[0].clientX)>10)){clearTimeout(this._timeoutIdStart);this._timeoutIdStart=null;this._timeoutIdEnd=null;this.setActive(false);}};f.prototype.ontouchend=function(E){if(E.targetTouches.length==0&&this.hasActiveType()){this._timeoutIdEnd=q.sap.delayedCall(100,this,function(){this.setActive(false);});}};f.prototype.ontouchcancel=f.prototype.ontouchend;f.prototype._activeHandlingNav=function(){};f.prototype._activeHandlingInheritor=function(){};f.prototype._inactiveHandlingInheritor=function(){};f.prototype._activeHandling=function(t){t.toggleClass("sapMLIBActive",this._active);if(D.system.Desktop&&this.isActionable()){t.toggleClass("sapMLIBHoverable",!this._active);}};f.prototype.onsapspace=function(E){if(E.srcControl!==this){return;}E.preventDefault();if(E.isMarked()||!this.isSelectable()){return;}if(this.getMode()==c.MultiSelect){this.setSelected(!this.getSelected());this.informList("Select",this.getSelected());}else if(!this.getSelected()){this.setSelected(true);this.informList("Select",true);}E.setMarked();};f.prototype.onsapenter=function(E){var o=this.getList();if(E.isMarked()||!o){return;}var k=L;if(E.srcControl!==this&&o.getKeyboardMode()==k.Edit){o.setKeyboardMode(k.Navigation);this._switchFocus(E);return;}if(E.srcControl!==this){return;}if(this.isIncludedIntoSelection()){this.onsapspace(E);}else if(this.hasActiveType()){E.setMarked();this.setActive(true);q.sap.delayedCall(180,this,function(){this.setActive(false);});q.sap.delayedCall(0,this,function(){this.fireTap();this.firePress();});}o.onItemPress(this,E.srcControl);};f.prototype.onsapdelete=function(E){if(E.isMarked()||E.srcControl!==this||this.getMode()!=c.Delete){return;}this.informList("Delete");E.preventDefault();E.setMarked();};f.prototype._switchFocus=function(E){var o=this.getList();if(!o){return;}var t=this.getTabbables();if(E.srcControl!==this){o._iLastFocusPosOfItem=t.index(E.target);this.focus();}else if(t.length){var F=o._iLastFocusPosOfItem||0;F=t[F]?F:-1;t.eq(F).focus();}E.preventDefault();E.setMarked();};f.prototype.onkeydown=function(E){if(E.isMarked()){return;}var k=q.sap.KeyCodes;if(E.which==k.F7){this._switchFocus(E);return;}if(E.which==k.F2){if(E.srcControl===this&&this.getType().indexOf("Detail")==0&&this.hasListeners("detailPress")||this.hasListeners("detailTap")){this.fireDetailTap();this.fireDetailPress();E.preventDefault();E.setMarked();}else{var o=this.getList();if(o){this.$().prop("tabIndex",-1);var K=L;o.setKeyboardMode(o.getKeyboardMode()==K.Edit?K.Navigation:K.Edit);this._switchFocus(E);}}}};f.prototype.getTabbables=function(){return this.$().find(":sapTabbable");};f.prototype.onsaptabnext=function(E){var o=this.getList();if(!o||E.isMarked()||o.getKeyboardMode()==L.Edit){return;}var g=this.getTabbables().get(-1)||this.getDomRef();if(E.target===g){o.forwardTab(true);E.setMarked();}};f.prototype.onsaptabprevious=function(E){var o=this.getList();if(!o||E.isMarked()||o.getKeyboardMode()==L.Edit){return;}if(E.target===this.getDomRef()){o.forwardTab(false);E.setMarked();}};f.prototype.onfocusin=function(E){var o=this.getList();if(!o||E.isMarked()){return;}if(E.srcControl===this){o.onItemFocusIn(this);return;}if(o.getKeyboardMode()==L.Edit||!q(E.target).is(":sapFocusable")){return;}q.sap.delayedCall(0,o,"setItemFocusable",[this]);E.setMarked();};f.prototype.onsapup=function(E){if(E.isMarked()||E.srcControl===this||this.getListProperty("keyboardMode")===L.Navigation){return;}this.informList("ArrowUpDown",E);};f.prototype.onsapdown=f.prototype.onsapup;return f;});