/*!
 * OpenUI5
 * (c) Copyright 2009-2020 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["sap/ui/fl/apply/_internal/changes/descriptor/Applier","sap/ui/fl/apply/_internal/changes/descriptor/ApplyStrategyFactory","sap/ui/fl/apply/_internal/flexState/FlexState","sap/ui/fl/apply/_internal/flexState/ManifestUtils","sap/ui/performance/Measurement","sap/ui/fl/Utils"],function(A,a,F,M,b,U){"use strict";var P={preprocessManifest:function(m,c){if(!U.isApplication(m,true)||!c.id){return Promise.resolve(m);}b.start("flexStateInitialize","Initialization of flex state",["sap.ui.fl"]);var C=c.componentData||{};var r=M.getFlexReference({manifest:m,componentData:C});if(!M.getChangeManifestFromAsyncHints(c.asyncHints)){F.initialize({componentData:C,asyncHints:c.asyncHints,rawManifest:m,componentId:c.id,reference:r,partialFlexState:true}).then(b.end.bind(undefined,"flexStateInitialize"));return Promise.resolve(m);}return F.initialize({componentData:C,asyncHints:c.asyncHints,rawManifest:m,componentId:c.id,reference:r,partialFlexState:true}).then(function(){b.end("flexStateInitialize");b.start("flexAppDescriptorMerger","Client side app descriptor merger",["sap.ui.fl"]);return a.getRuntimeStrategy();}).then(function(R){var d=F.getAppDescriptorChanges(r);return A.applyChanges(m,d,R);}).then(function(m){b.end("flexAppDescriptorMerger");return m;});}};return P;},true);