

({
	extendsFrom: 'RecordView',
	events: {
		'click #qbconfigTab > .tab > a:not(.dropdown-toggle)': 'setActiveTab',
		'click [name=validate_button]': 'validateClicked',
		'click [name=download_button]': 'downloadClicked',
		'change [name=qb_user_list]': 'userListChange',
		'click [name=clear_button]': 'clearClicked'
	},
	initialize: function (options) {
		app.view.View.prototype.initialize.call(this, options);
		this.handleActiveTab();
		this.context.on('button:save_button:click', this.saveClicked, this);
	},
	_render: function () {
		app.view.View.prototype._render.call(this);
	},
	handleActiveTab: function () {
		var activeTabHref = this.getActiveTab(), activeTab = this.$('#qbconfigTab > .tab > a[href="' + activeTabHref + '"]');
		if (activeTabHref && activeTab) {
			activeTab.tab('show');
		} else if (this.meta.useTabsAndPanels && this.checkFirstPanel()) {
			this.$('#qbconfigTab a:first').tab('show');
		}
	},
	getActiveTab: function () {
		var activeTabHref = app.user.lastState.get(app.user.lastState.key('activeTab', this));
		if (!activeTabHref) {
			activeTabHref = this.$('#qbconfigTab > .tab:first-child > a').attr('href') || '';
			app.user.lastState.set(app.user.lastState.key('activeTab', this), activeTabHref.substring(0, activeTabHref.indexOf(this.cid)));
		}
		else {
			activeTabHref += this.cid;
		}
		return activeTabHref;
	},
	setActiveTab: function (event) {
		var tabTarget = this.$(event.currentTarget).attr('href'), tabKey = app.user.lastState.key('activeTab', this), cidIndex = tabTarget.indexOf(this.cid);
		tabTarget = tabTarget.substring(0, cidIndex);
		app.user.lastState.set(tabKey, tabTarget);
	},
	validateClicked: function () {
		var self = this;
		if (_.isEmpty(self.userId)) {
			self.userId = app.user.id;
		}
		var licenseKey = self.model.get('license_key');
		var userId = self.userId;
		app.alert.show('validating', {level: 'process', title: app.lang.get('LBL_VALIDATE_LICENSE', self.module)});
		app.api.call('create', app.api.buildURL('QBConfig', 'validateLicense'), {license: licenseKey, userId: userId}, {
			success: function (response) {
				console.log(response);
				app.alert.dismiss('validating');
				if(response === true) {
					app.alert.show('valid_licennse', {level: 'success', title: app.lang.get('LBL_VALID', self.module), autoClose: true});
				} else {
					app.alert.show('invalid_license', {level: 'error', title: app.lang.get('LBL_INVALID', self.module), autoClose: true});
				}
			}
		});
	},
	downloadClicked: function () {
		if (this.model.id === '' || this.model.id === null) {
			app.alert.show('save_record_error', {level: 'error', title: app.lang.get('LBL_WARNING_SAVE_RECORD', this.module), autoClose: true});
			return false;
		}
		app.alert.show('download_file_loading', {level: 'process', title: app.lang.getAppString('LBL_LOADING')});
		var url = app.api.buildURL('QBConfig', 'qbFile', {id: this.model.id}, {platform: app.config.platform});
		if (_.isEmpty(url)) {
			app.logger.error('Unable to get the vCard download uri.');
			return;
		}
		app.api.fileDownload(url, {
			complete: function () {
				app.alert.dismiss('download_file_loading');
			},
			error: function (data) {
				app.error.handleHttpError(data, {});
			}
		}, {
			iframe: this.$el
		});

	},
	/**
	 * @function clearClicked
	 * @description called when Clear Mapping button clicked
	 * @returns {undefined}
	 */
	clearClicked: function () {
		var self = this;
		if (_.isEmpty(self.userId)) {
			self.userId = app.user.id;
		}
		app.alert.show('clear_mapping_confirmation', {
			level: 'confirmation',
			title: app.lang.get('LBL_CLEAR_MAPPING_CONFIRM', self.module),
			onConfirm: function () {
				app.alert.show('clear_mapping_loading', {level: 'process', title: app.lang.getAppString('LBL_PROCESSING_REQUEST')});
				app.api.call('read', app.api.buildURL('QBConfig/clearMapping/' + self.userId), null, {
					success: function () {
						app.alert.dismiss('clear_mapping_loading');
						app.alert.show('success', {level: 'success', title: app.lang.get('LBL_MAPPED_SUCCESS', self.module), autoClose: true});
						app.alert.dismiss('record_saving');
					}
				});
			}
		});


	},
	/**
	 * @function userListChange
	 * @description reloading configuration on selected user base
	 * @returns {undefined}
	 */
	userListChange: function (evt) {
		this.userId = evt.target.value;
		var self = this;
		app.alert.show('change_user', {
			level: 'confirmation',
			title: app.lang.get('LBL_CHANGE_USER', self.module),
			onConfirm: function () {
				self.loadData();
			}
		});


	},
	saveClicked: function () {
		console.log('save clicked');
		var userId = !_.isEmpty(this.userId) ? this.userId : app.user.id;
		if (this.model.get('connector_password') === '' || this.model.get('connector_password') === undefined) {
			app.alert.show('missing_field', {level: 'error', title: app.lang.getAppString('ERR_MISSING_REQUIRED_FIELDS'), autoClose: true});
			$('[name=connector_password]').closest('.row-fluid').addClass('error');
			return false;
		}
		this.model.set('assigned_user_id', userId);
		this.sync_date = this.model.get('sugar_last_sync_date');
		delete(this.model.attributes.sugar_last_sync_date);
		app.alert.show('record_saving', {level: 'process', title: app.lang.getAppString('LBL_SAVING')});
		if (this.model.id != null) {
			this.model.set('name', app.user.get('full_name') + ' - Config');
			this.model.set('id', this.model.id);
			this.model.doValidate(this.getFields(this.module), _.bind(this.saveConfig, this));
		} else {
			this.model.doValidate(this.getFields(this.module), _.bind(this.saveConfig, this));
		}
	},
	saveConfig: function (isValid) {
		$('[name=clear_button]').removeClass('disabled');
		var self = this;
		if (isValid) {
			this.setButtonStates(this.STATE.VIEW);
			this.model.set('account_customer', $('[name=account_customer]').is(':checked'));
			this.model.set('customer_account', $('[name=customer_account]').is(':checked'));
			this.model.set('product_item', $('[name=product_item]').is(':checked'));
			this.model.set('item_product', $('[name=item_product]').is(':checked'));
			this.model.set('quotes_estimate', $('[name=quotes_estimate]').is(':checked'));
			this.model.set('sales_history_invoice', $('[name=sales_history_invoice]').is(':checked'));
			this.model.set('qb_customer_create', $('[name=qb_customer_create]').is(':checked'));
			this.model.set('sugar_account_create', $('[name=sugar_account_create]').is(':checked'));
			this.model.save({}, {
				success: function () {
					app.alert.show('success', {level: 'success', title: app.lang.getAppString('LBL_RECORD_SAVED'), autoClose: true});
					app.alert.dismiss('record_saving');
					self.model.set('sugar_last_sync_date', self.sync_date);
				}
			});
		}
	},
	/**
	 * @function: loadData
	 * @params: options 
	 * @Description:Load the data (if exists) with API call and render the view
	 */
	loadData: function () {
		var self = this;
		if (_.isEmpty(self.userId)) {
			self.userId = app.user.id;
		}
		app.api.call('read', app.api.buildURL('QBConfig/getConfig/' + self.userId), {}, {
			success: function (data) {
				self.model.id = data.id;
				self.sync_date = data.sugar_last_sync_date;
				if (typeof (data.id) == 'undefined' || data.id == '') {
					self.model = app.data.createBean('QBConfig');
				} else {
					var qbconfig = app.data.createBean('QBConfig', {id: data.id});
					qbconfig.fetch({
						success: function () {
							_.each(self.meta.tabs, function (tab, val) {
								_.each(tab.fields, function (field, value) {
									self.model.set(field.name, qbconfig.get(field.name));
								});
							});
							self.model.set("qb_user_list", self.userId);
							self.model.set("sugar_last_sync_date", self.sync_date);
							self.render();
							$('[name=account_customer]').attr('checked', data.account_customer == '1' ? true : false);
							$('[name=customer_account]').attr('checked', data.customer_account == '1' ? true : false);
							$('[name=product_item]').attr('checked', data.product_item == '1' ? true : false);
							$('[name=item_product]').attr('checked', data.item_product == '1' ? true : false);
							$('[name=quotes_estimate]').attr('checked', data.quotes_estimate == '1' ? true : false);
							$('[name=sales_history_invoice]').attr('checked', data.sales_history_invoice == '1' ? true : false);
							$('[name=qb_customer_create]').attr('checked', data.qb_customer_create == '1' ? true : false);
							$('[name=sugar_account_create]').attr('checked', data.sugar_account_create == '1' ? true : false);
							if (typeof (data.id) != 'undefined' && data.id != '' && data.id != null) {
								$('[name=clear_button]').removeClass('disabled');
							}
						}
					});
				}
			}
		});
	}

})
