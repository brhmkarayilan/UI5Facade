/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2017 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['jquery.sap.global', 'sap/ui/rta/command/FlexCommand'], function(jQuery, FlexCommand) {
	"use strict";

	/**
	 * Rename Element from one place to another
	 *
	 * @class
	 * @extends sap.ui.rta.command.FlexCommand
	 * @author SAP SE
	 * @version 1.52.5
	 * @constructor
	 * @private
	 * @since 1.34
	 * @alias sap.ui.rta.command.Rename
	 * @experimental Since 1.34. This class is experimental and provides only limited functionality. Also the API might be
	 *               changed in future.
	 */
	var Rename = FlexCommand.extend("sap.ui.rta.command.Rename", {
		metadata : {
			library : "sap.ui.rta",
			properties : {
				renamedElement : {
					type : "object"
				},
				newValue : {
					type : "string",
					defaultValue : "new text"
				}
			},
			associations : {},
			events : {}
		}
	});

	Rename.prototype._getChangeSpecificData = function() {

		var mSpecificInfo = {
				changeType : this.getChangeType(),
				selector : {
					id : this.getElement().getId()
				},
				renamedElement : {
					id : this.getRenamedElement().getId()
				},
				value : this.getNewValue()
		};

		return mSpecificInfo;
	};

	return Rename;

}, /* bExport= */true);