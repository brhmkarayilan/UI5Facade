/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2016 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["jquery.sap.global","./Base","sap/ui/fl/Utils","sap/ui/fl/changeHandler/JsControlTreeModifier"],function(q,B,F,J){"use strict";var M={};M.CHANGE_TYPE="moveElements";M.applyChange=function(c,s,p){function a(c,m,v,A){if(!c){throw new Error("No change instance");}var C=c.getContent();if(!C||!C.movedElements||C.movedElements.length===0){throw new Error("Change format invalid");}if(!c.getSelector().aggregation){throw new Error("No source aggregation supplied via selector for move");}if(!C.target||!C.target.selector){throw new Error("No target supplied for move");}if(!m.bySelector(C.target.selector,A,v)){throw new Error("Move target parent not found");}if(!C.target.selector.aggregation){throw new Error("No target aggregation supplied for move");}}function g(b,m,A,v){if(!b.selector&&!b.id){throw new Error("Change format invalid - moveElements element has no id attribute");}if(typeof b.targetIndex!=="number"){throw new Error("Missing targetIndex for element with id '"+b.selector.id+"' in movedElements supplied");}return m.bySelector(b.selector||b.id,A,v);}var m=p.modifier;var v=p.view;var A=p.appComponent;a(c,m,v,A);var C=c.getContent();var t=m.bySelector(C.target.selector,A,v);var S=c.getSelector().aggregation;var T=C.target.selector.aggregation;C.movedElements.forEach(function(b){var o=g(b,m,A,v);if(!o){F.log.warning("Element to move not found");return;}m.removeAggregation(s,S,o,v);m.insertAggregation(t,T,o,b.targetIndex);});return true;};M.buildStableChangeInfo=function(m){delete m.source.publicAggregation;delete m.target.publicAggregation;return m;};M.completeChangeContent=function(c,s,p){function a(){if(!s.movedElements){throw new Error("mSpecificChangeInfo.movedElements attribute required");}if(s.movedElements.length===0){throw new Error("MovedElements array is empty");}s.movedElements.forEach(function(e){if(!e.id){throw new Error("MovedElements element has no id attribute");}if(typeof(e.sourceIndex)!=="number"){throw new Error("SourceIndex attribute at MovedElements element is no number");}if(typeof(e.targetIndex)!=="number"){throw new Error("TargetIndex attribute at MovedElements element is no number");}});}a();var A=p.appComponent;var S=this.getSpecificChangeInfo(J,s);var m={aggregation:S.source.aggregation,type:S.source.type};var C=c.getDefinition();q.extend(C.selector,m);var b={aggregation:S.target.aggregation,type:S.target.type};C.changeType=M.CHANGE_TYPE;C.content={movedElements:[],target:{selector:J.getSelector(S.target.id,A,b)}};S.movedElements.forEach(function(e){var E=e.element||J.bySelector(e.id,A);C.content.movedElements.push({selector:J.getSelector(E,A),sourceIndex:e.sourceIndex,targetIndex:e.targetIndex});});};M.getSpecificChangeInfo=function(m,s){var S=s.source.parent||m.bySelector(s.source.id);var t=s.target.parent||m.bySelector(s.target.id);var a=s.source.aggregation;var T=s.target.aggregation;var b={source:{id:S.getId(),aggregation:a,type:m.getControlType(S)},target:{id:t.getId(),aggregation:T,type:m.getControlType(t)},movedElements:s.movedElements};return b;};return M;},true);
