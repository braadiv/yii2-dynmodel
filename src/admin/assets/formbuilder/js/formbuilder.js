/**
 * Forked https://github.com/dobtco/formbuilder
 * by Mirocow (mirocow.com)
 */

/**
 * Init riverts
 */
(function () {

		rivets.binders.input = {
				publishes: true,
				routine: rivets.binders.value.routine,

				bind: function (el) {
						return $(el).bind('input.rivets', this.publish);
				},

				unbind: function (el) {
						return $(el).unbind('input.rivets');
				}
		}

		/**
		 * Adapter
		 */
		rivets.configure({
				prefix: "rv",
				adapter: {

						subscribe: function (obj, keypath, callback) {
								callback.wrapped = function (m, v) {
										return callback(v);
								}
								return obj.on('change:' + keypath, callback.wrapped);
						},

						unsubscribe: function (obj, keypath, callback) {
								return obj.off('change:' + keypath, callback.wrapped);
						},

						read: function (obj, keypath) {
								if (keypath === "cid") {
										return obj.cid;
								}
								return obj.get(keypath);
						},

						publish: function (obj, keypath, value) {
								if (obj.cid) {
										return obj.set(keypath, value);
								} else {
										return obj[keypath] = value;
								}
						}
				}
		});

}).call(this);

/**
 * App
 */
(function () {

		var BuilderView, EditFieldView, Formbuilder, FormbuilderCollection, FormbuilderModel,
				ViewFieldView, _ref, _ref1, _ref2, _ref3, _ref4,
				__hasProp = {}.hasOwnProperty,
				__extends = function (child, parent) {

						for (var key in parent) {
								if (__hasProp.call(parent, key)) child[key] = parent[key];
						}

						function ctor() {
								this.constructor = child;
						}

						ctor.prototype = parent.prototype;
						child.prototype = new ctor();
						child.__super__ = parent.prototype;
						return child;
				}

		FormbuilderModel = (function (_super) {

				__extends(FormbuilderModel, _super);

				function FormbuilderModel() {
						_ref = FormbuilderModel.__super__.constructor.apply(this, arguments);
						return _ref;
				}

				FormbuilderModel.prototype.sync = function () {
				}

				FormbuilderModel.prototype.indexInDOM = function () {
						var $wrapper,
								_this = this;

						$wrapper = $(".fb-field-wrapper").filter((function (_, el) {
								return $(el).data('cid') === _this.cid;
						}));

						return $(".fb-field-wrapper").index($wrapper);
				}

				FormbuilderModel.prototype.is_input = function () {
						return Formbuilder.inputFields[
										this.get(Formbuilder.names.FIELD_TYPE)
										] != null;
				}

				return FormbuilderModel;

		})(Backbone.DeepModel);

		FormbuilderCollection = (function (_super) {

				__extends(FormbuilderCollection, _super);

				function FormbuilderCollection() {
						_ref1 = FormbuilderCollection.__super__.constructor.apply(this, arguments);
						return _ref1;
				}

				FormbuilderCollection.prototype.initialize = function () {
						return this.on('add', this.copyCidToModel);
				}

				FormbuilderCollection.prototype.model = FormbuilderModel;

				FormbuilderCollection.prototype.comparator = function (model) {
						return model.indexInDOM();
				}

				FormbuilderCollection.prototype.copyCidToModel = function (model) {
						return model.attributes.cid = model.cid;
				}

				return FormbuilderCollection;

		})(Backbone.Collection);

		ViewFieldView = (function (_super) {
				__extends(ViewFieldView, _super);

				function ViewFieldView() {
						_ref2 = ViewFieldView.__super__.constructor.apply(this, arguments);
						return _ref2;
				}

				ViewFieldView.prototype.className = "fb-field-wrapper";

				ViewFieldView.prototype.events = {
						'click .subtemplate-wrapper': 'focusEditView',
						'click .js-duplicate': 'duplicate',
						'click .js-clear': 'clear'
				}

				ViewFieldView.prototype.initialize = function (options) {
						this.parentView = options.parentView;
						this.listenTo(this.model, "change", this.render);
						return this.listenTo(this.model, "destroy", this.remove);
				}

				ViewFieldView.prototype.render = function () {
					tempClass = this.model.attributes.is_template? 'fieldTemp' : '';
					this.$el.addClass('response-field-' +
								this.model.get(
									Formbuilder.names.FIELD_TYPE
								)  
								+ ' ' + tempClass
							)
							.data('cid', this.model.cid)
							.html(
								Formbuilder.templates["view/base" + (!this.model.is_input() ? '_non_input' : '')]({
									rf: this.model
								})
							);
					return this;
				}

				ViewFieldView.prototype.focusEditView = function () {
						return this.parentView.createAndShowEditView(this.model);
				}

				ViewFieldView.prototype.clear = function (e) {
						var cb, x,
								_this = this;
						e.preventDefault();
						e.stopPropagation();
						cb = function () {
								_this.parentView.handleFormUpdate();
								return _this.model.destroy();
						}
						x = Formbuilder.options.CLEAR_FIELD_CONFIRM;
						switch (typeof x) {
								case 'string':
										if (confirm(x)) {
												return cb();
										}
										break;
								case 'function':
										return x(cb);
								default:
										return cb();
						}
				}

				ViewFieldView.prototype.duplicate = function () {
						var attrs;
						attrs = _.clone(this.model.attributes);
						delete attrs['id'];
						attrs['label'] += ' Copy';
						return this.parentView.createField(attrs, {
								position: this.model.indexInDOM() + 1,
								duplicate: true
						});
				}

				return ViewFieldView;

		})(Backbone.View);

		EditFieldView = (function (_super) {

				__extends(EditFieldView, _super);

				function EditFieldView() {
						_ref3 = EditFieldView.__super__.constructor.apply(this, arguments);
						return _ref3;
				}

				EditFieldView.prototype.className = "edit-response-field";

				EditFieldView.prototype.events = {
						'click .js-add-option': 'addOption',
						'click .js-remove-option': 'removeOption',
						'click .js-sort-up-option': 'sortOptionUp',
						'click .js-sort-down-option': 'sortOptionDown',
						'click .js-default-updated': 'defaultUpdated',
						'input .option-label-input': 'forceRender'
				}

				EditFieldView.prototype.initialize = function (options) {
						this.parentView = options.parentView;
						return this.listenTo(this.model, "destroy", this.remove);
				}

				EditFieldView.prototype.render = function () {
						this.$el.html(Formbuilder.templates["edit/base" + (!this.model.is_input() ? '_non_input' : '')]({
								rf: this.model
						}));

						rivets.bind(this.$el, {
								model: this.model
						});

						return this;
				}

				EditFieldView.prototype.remove = function () {
						this.parentView.editView = void 0;
						this.parentView.$el.find("[data-target=\"#addField\"]").click();
						return EditFieldView.__super__.remove.apply(this, arguments);
				}

				EditFieldView.prototype.addOption = function (e) {
						var $el, i, newOption, options;

						$el = $(e.currentTarget);
						i = this.$el.find('.option').index($el.closest('.option'));
						options = this.model.get(Formbuilder.names.OPTIONS) || [];
						newOption = {
								label: "",
								checked: false
						}
						if (i > -1) {
								options.splice(i + 1, 0, newOption);
						} else {
								options.push(newOption);
						}
						this.model.set(Formbuilder.names.OPTIONS, options);
						this.model.trigger("change:" + Formbuilder.names.OPTIONS);
						return this.forceRender();
				}

				EditFieldView.prototype.sortOptionUp = function (e) {
						var $el, index, options;

						$el = $(e.currentTarget);
						index = this.$el.find(".js-sort-up-option").index($el);
						options = this.model.get(Formbuilder.names.OPTIONS);
						tmp = options[index];
						tmp2 = options[index - 1];
						options[index] = tmp2;
						options[index - 1] = tmp;
						this.model.set(Formbuilder.names.OPTIONS, options);
						this.model.trigger("change:" + Formbuilder.names.OPTIONS);
						this.forceRender();
				}

				EditFieldView.prototype.sortOptionDown = function (e) {
						var $el, index, options;

						$el = $(e.currentTarget);
						index = this.$el.find(".js-sort-down-option").index($el);
						options = this.model.get(Formbuilder.names.OPTIONS);
						if (index < options.length - 1) {
								// console.log($el);
								tmp = options[index];
								tmp2 = options[index + 1];
								options[index] = tmp2;
								options[index + 1] = tmp;
								this.model.set(Formbuilder.names.OPTIONS, options);
								this.model.trigger("change:" + Formbuilder.names.OPTIONS);
								this.forceRender();
						}
				}

				EditFieldView.prototype.removeOption = function (e) {
						var $el, index, options;

						$el = $(e.currentTarget);
						index = this.$el.find(".js-remove-option").index($el);
						options = this.model.get(Formbuilder.names.OPTIONS);
						options.splice(index, 1);
						this.model.set(Formbuilder.names.OPTIONS, options);
						this.model.trigger("change:" + Formbuilder.names.OPTIONS);
						return this.forceRender();
				}

				EditFieldView.prototype.defaultUpdated = function (e) {
						var $el, index, options;

						$el = $(e.currentTarget);
						options = this.model.get(Formbuilder.names.OPTIONS);
						index = this.$el.find(".js-default-updated").index($el);
						if (options[index].checked) {
								options[index].checked = false;
						} else {
								options[index].checked = true;
								//this.$el.find(".js-default-updated").attr('checked', true);//.trigger('change');
						}
						return this.forceRender();
				}

				EditFieldView.prototype.forceRender = function () {
						return this.model.trigger('change');
				}

				return EditFieldView;

		})(Backbone.View);

		BuilderView = (function (_super) {

				__extends(BuilderView, _super);

				function BuilderView() {
						_ref4 = BuilderView.__super__.constructor.apply(this, arguments);
						return _ref4;
				}

				BuilderView.prototype.SUBVIEWS = [];

				BuilderView.prototype.events = {
						'click .js-save-form': 'saveForm',
						'click .fb-tabs a': 'showTab',
						'click .fb-add-field-types a': 'addField',
						'mouseover .fb-add-field-types': 'lockLeftWrapper',
						'mouseout .fb-add-field-types': 'unlockLeftWrapper'
				}

				BuilderView.prototype.initialize = function (options) {

						var selector = options.selector;

						this.uri = options.uri;
						this.formBuilder = options.formBuilder;

						if (selector != null) {
								this.setElement($(selector));
						}

						this.collection = new FormbuilderCollection;

						this.collection.bind('add', this.addOne, this);
						this.collection.bind('reset', this.reset, this);
						this.collection.bind('change', this.handleFormUpdate, this);
						this.collection.bind('destroy add reset', this.hideShowNoResponseFields, this);
						this.collection.bind('destroy', this.ensureEditViewScrolled, this);

						this.render();

						this.collection.reset(options.bootstrapData);

						return this.bindSaveEvent();
				}

				BuilderView.prototype.bindSaveEvent = function () {
						var _this = this;
						this.formSaved = true;
						this.saveFormButton = this.$el.find(".js-save-form");
						this.saveFormButton.attr('disabled', true).text(Formbuilder.dict.ALL_CHANGES_SAVED);

						if (!!Formbuilder.options.AUTOSAVE) {
								setInterval(function () {
										return _this.saveForm.call(_this);
								}, 5000);
						}

						$('form').submit(function (event) {
								_this.saveForm.call(_this);
						});
						return $(window).bind('beforeunload', function () {
								if (_this.formSaved) {
										return void 0;
								} else {
										return Formbuilder.dict.UNSAVED_CHANGES;
								}
						});
				}

				BuilderView.prototype.reset = function () {
						this.$responseFields.html('');
						return this.addAll();
				}

				BuilderView.prototype.render = function () {
						var subview, _i, _len, _ref5;

						this.$el.html(Formbuilder.templates['page']());
						this.$fbLeft = this.$el.find('.fb-left');
						this.$fbTabParent = this.$fbLeft.parents('.tab-pane:first');
						this.$responseFields = this.$el.find('.fb-response-fields');
						this.bindWindowScrollEvent();
						this.hideShowNoResponseFields();
						_ref5 = this.SUBVIEWS;
						for (_i = 0, _len = _ref5.length; _i < _len; _i++) {
								subview = _ref5[_i];
								new subview({
										parentView: this
								}).render();
						}
						return this;
				}

				BuilderView.prototype.bindWindowScrollEvent = function () {
						var _this = this;
						return $(window).on('scroll', function () {
								var maxMargin, newMargin;
								if (_this.$fbLeft.data('locked') === true) {
										return;
								}
								newMargin = Math.max(0, $(window).scrollTop() - _this.$el.offset().top);
								maxMargin = _this.$responseFields.height();
								if (_this.$fbTabParent.length > 0) {
										if (_this.$fbTabParent.hasClass('active')) {
												return _this.$fbLeft.css({
														'margin-top': Math.min(maxMargin, newMargin)
												});
										}
								} else {
										return _this.$fbLeft.css({
												'margin-top': Math.min(maxMargin, newMargin)
										});
								}
						});
				}

				BuilderView.prototype.showTab = function (e) {
						var $el, first_model, target;
						$el = $(e.currentTarget);
						target = $el.data('target');
						$el.closest('li').addClass('active').siblings('li').removeClass('active');
						$(target).addClass('active').siblings('.fb-tab-pane').removeClass('active');
						if (target !== '#editField') {
								this.unlockLeftWrapper();
						}
						if (target === '#editField' && !this.editView && (first_model = this.collection.models[0])) {
								return this.createAndShowEditView(first_model);
						}
				}

				BuilderView.prototype.addOne = function (responseField, _, options) {
						var $replacePosition, view;
						view = new ViewFieldView({
								model: responseField,
								parentView: this
						});

						if (options.$replaceEl != null) {
								return options.$replaceEl.replaceWith(view.render().el);
						} else if ((options.position == null) || options.position === -1) {
								return this.$responseFields.append(view.render().el);
						} else if (options.position === 0) {
								return this.$responseFields.prepend(view.render().el);
						} else if (($replacePosition = this.$responseFields.find(".fb-field-wrapper").eq(options.position))[0]) {
								return $replacePosition.before(view.render().el);
						} else {
								return this.$responseFields.append(view.render().el);
						}
				}

				BuilderView.prototype.setSortable = function () {
						var _this = this;
						if (this.$responseFields.hasClass('ui-sortable')) {
								this.$responseFields.sortable('destroy');
						}

						this.$responseFields.sortable({
								forcePlaceholderSize: true,
								placeholder: 'sortable-placeholder',
								stop: function (e, ui) {
										var rf;
										if (ui.item.data('field-type')) {
												rf = _this.collection.create(
														Formbuilder.helpers.defaultValueAttributes(ui.item.data('field-type')),
														{
																$replaceEl: ui.item
														}
												);
												_this.createAndShowEditView(rf);
										}
										_this.handleFormUpdate();
										return true;
								},
								update: function (e, ui) {
										if (!ui.item.data('field-type')) {
												return _this.ensureEditViewScrolled();
										}
								}
						});

						return this.setDraggable();
				}

				BuilderView.prototype.setDraggable = function () {
						var $addFieldButtons,
								_this = this;
						$addFieldButtons = this.$el.find("[data-field-type]");
						return $addFieldButtons.draggable({
								connectToSortable: this.$responseFields,
								helper: function () {
										var $helper;
										$helper = $("<div class='response-field-draggable-helper' />");
										$helper.css({
												width: _this.$responseFields.width(),
												height: '80px'
										});
										return $helper;
								}
						});
				}

				BuilderView.prototype.addAll = function () {
						this.collection.each(this.addOne, this);
						return this.setSortable();
				}

				BuilderView.prototype.hideShowNoResponseFields = function () {
						return this.$el.find(".fb-no-response-fields")[this.collection.length > 0 ? 'hide' : 'show']();
				}

				BuilderView.prototype.addField = function (e) {
						var field_type;
						field_type = $(e.currentTarget).data('field-type');
						return this.createField(Formbuilder.helpers.defaultValueAttributes(field_type));
				}

				BuilderView.prototype.createField = function (attrs, options) {
						var rf;
						rf = this.collection.create(attrs, options);
						this.createAndShowEditView(rf);
						return this.handleFormUpdate();
				}

				BuilderView.prototype.createAndShowEditView = function (model) {
						var $newEditEl, $responseFieldEl;
						$responseFieldEl = this.$el.find(".fb-field-wrapper").filter(function () {
								return $(this).data('cid') === model.cid;
						});
						$responseFieldEl.addClass('editing').siblings('.fb-field-wrapper').removeClass('editing');
						if (this.editView) {
								if (this.editView.model.cid === model.cid) {
										this.$el.find(".fb-tabs a[data-target=\"#editField\"]").click();
										this.scrollLeftWrapper($responseFieldEl);
										return;
								}
								this.editView.remove();
						}
						this.editView = new EditFieldView({
								model: model,
								parentView: this
						});
						$newEditEl = this.editView.render().$el;
						this.$el.find(".fb-edit-field-wrapper").html($newEditEl);
						this.$el.find(".fb-tabs a[data-target='#editField']").click();
						this.scrollLeftWrapper($responseFieldEl);
						return this;
				}

				BuilderView.prototype.ensureEditViewScrolled = function () {
						if (!this.editView) {
								return;
						}
						return this.scrollLeftWrapper($(".fb-field-wrapper.editing"));
				}

				BuilderView.prototype.scrollLeftWrapper = function ($responseFieldEl) {
						var _this = this;
						this.unlockLeftWrapper();
						if (!$responseFieldEl[0]) {
								return;
						}
						return $.scrollWindowTo((this.$el.offset().top + $responseFieldEl.offset().top) - this.$responseFields.offset().top, 200, function () {
								return _this.lockLeftWrapper();
						});
				}

				BuilderView.prototype.lockLeftWrapper = function () {
						return this.$fbLeft.data('locked', true);
				}

				BuilderView.prototype.unlockLeftWrapper = function () {
						return this.$fbLeft.data('locked', false);
				}

				BuilderView.prototype.handleFormUpdate = function () {
						if (this.updatingBatch) {
								return;
						}
						this.formSaved = false;
						return this.saveFormButton.removeAttr('disabled').text(Formbuilder.dict.SAVE_FORM);
				}

				BuilderView.prototype.saveForm = function (e) {
						var payload;
						if (this.formSaved) {
								return;
						}
						this.formSaved = true;
						this.saveFormButton.attr('disabled', true).text(Formbuilder.dict.ALL_CHANGES_SAVED);
						this.collection.sort();

						payload = JSON.stringify({
								fields: this.collection.toJSON()
						});

						if (Formbuilder.options.HTTP_ENDPOINT) {
								this.doAjaxSave(payload);
						}

						return this.formBuilder.trigger('save', payload);
				}

				BuilderView.prototype.doAjaxSave = function (payload) {
						var _this = this;
						return $.ajax({
								url: Formbuilder.options.HTTP_ENDPOINT,
								type: Formbuilder.options.HTTP_METHOD,
								data: payload,
								contentType: "application/json",
								success: function (data) {
										var datum, _i, _len, _ref5;
										_this.updatingBatch = true;
										for (_i = 0, _len = data.length; _i < _len; _i++) {
												datum = data[_i];
												if ((_ref5 = _this.collection.get(datum.cid)) != null) {
														_ref5.set({
																id: datum.id
														});
												}
												_this.collection.trigger('sync');
										}
										return _this.updatingBatch = void 0;
								}
						});
				}

				return BuilderView;

		})(Backbone.View);

		Formbuilder = (function () {

				Formbuilder.lang = function (key) {
						if (typeof formbuilder_lang !== "undefined" && typeof formbuilder_lang[key] !== "undefined") {
								return formbuilder_lang[key];
						}
						return key;
				}

				Formbuilder.helpers = {

						/**
						 * Default attributes setting
						 */
						defaultValueAttributes: function (field_type) {
								var attrs = {};

								attrs[Formbuilder.names.GROUP_NAME] = Formbuilder.dict.GROUP_NAME;
								attrs[Formbuilder.names.LABEL] = Formbuilder.dict.LABEL;
								attrs[Formbuilder.names.LABELEN] = Formbuilder.dict.LABELEN;
								attrs[Formbuilder.names.FIELD_TYPE] = field_type;
								attrs[Formbuilder.names.REQUIRED] = true;
								attrs[Formbuilder.names.MIN] = 0;
								attrs[Formbuilder.names.MAX] = 0;
								attrs[Formbuilder.names.MINLENGTH] = 0;
								attrs[Formbuilder.names.MAXLENGTH] = 0;
								attrs[Formbuilder.names.LOCKED] = false;
								attrs[Formbuilder.names.VISIBLE] = true;
								attrs[Formbuilder.names.SIZE] = 'large';
								attrs['field_options'] = {};

								/**
								 * Evaluate JS template from widget
								 */
								var _defaultAttributes = eval('(' + Formbuilder.fields[field_type].defaultAttributes + ')');

								return (typeof _defaultAttributes === "function"? _defaultAttributes(attrs) : void 0) || attrs;

						}

				}

				Formbuilder.options = {
						HTTP_ENDPOINT: '',
						HTTP_METHOD: 'POST',
						AUTOSAVE: false,
						CLEAR_FIELD_CONFIRM: false,
				}

				Formbuilder.names = {
						BUTTON_CLASS: 'btn',
						GROUP_NAME: 'group_name',
						LABEL: 'label',
						LABELEN: 'label_en',
						FIELD_TYPE: 'field_type',
						SIZE: 'field_options.size',
						UNITS: 'field_options.units',
						REQUIRED: 'field_options.required',
						LOCKED: 'field_options.locked',
						VISIBLE: 'field_options.visible',
						OPTIONS: 'field_options.options',
						DESCRIPTION: 'field_options.description',
						DESCRIPTIONEN: 'field_options.description_en',
						INCLUDE_OTHER: 'field_options.include_other_option',
						INCLUDE_BLANK: 'field_options.include_blank_option',
						INTEGER_ONLY: 'field_options.integer_only',
						MIN: 'field_options.min',
						MAX: 'field_options.max',
						MINLENGTH: 'field_options.minlength',
						MAXLENGTH: 'field_options.maxlength',
						LENGTH_UNITS: 'field_options.min_max_length_units',
						AREA_ROWS: 'field_options.area_size.rows',
						AREA_COLS: 'field_options.area_size.cols',
						MULTIPLE: 'field_options.list.multiple',
				}

				Formbuilder.dict = {
						ALL_CHANGES_SAVED: Formbuilder.lang('All changes saved'),
						SAVE_FORM: Formbuilder.lang('Save form'),
						UNSAVED_CHANGES: Formbuilder.lang('You have unsaved changes. If you leave this page, you will lose those changes!'),
						LABEL: Formbuilder.lang('بدون عنوان'),
						LABELEN: Formbuilder.lang('Untitled'),
						GROUP_NAME: Formbuilder.lang('default')
				}

				Formbuilder.fields = {}

				Formbuilder.inputFields = {}

				Formbuilder.registerField = function (name, opts) {
						var x, _i, _len, _ref5;

						_ref5 = ['view', 'edit'];

						for (_i = 0, _len = _ref5.length; _i < _len; _i++) {
								x = _ref5[_i];
								opts[x] = _.template(opts[x]);
						}

						opts.field_type = name;
						Formbuilder.fields[name] = opts;

						return Formbuilder.inputFields[name] = opts;
				}

				function Formbuilder(opts) {
						var args;

						if (opts == null) {
								opts = {}
						}

						_.extend(this, Backbone.Events);

						args = _.extend(opts, {
								formBuilder: this
						});

						getFieldTypes(opts, function (opts) {

								opts.formBuilder.mainView = new BuilderView(opts);
						});

				}

				/**
				 * Get remote field types
				 */
                function getFieldTypes(opts, callBack) {
                    $.ajax({
                        url: opts.uri,
                        dataType: "json",
                        success:function (response) {
                            if (response.status == 'success') {
                                _.each(response.types, function (f) {
                                    Formbuilder.registerField(f.name, f.formBuilder);
                                });
                            }
                            callBack(opts);
                        }
                    });
                }

				return Formbuilder;

		})();

		if (typeof module !== "undefined" && module !== null) {
				module.exports = Formbuilder;
		} else {
				window.Formbuilder = Formbuilder;
		}

}).call(this);

/**
 *  Templates
 */
(function () {

		this["Formbuilder"] = this["Formbuilder"] || {}
		this["Formbuilder"]["templates"] = this["Formbuilder"]["templates"] || {}

		// Base templates

		/*this["Formbuilder"]["templates"]["view/base_non_input"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {
						__p += '';

				}
				return __p
		}*/

		this["Formbuilder"]["templates"]["page"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;

				with (fied_settings) {

						__p +=
								((__t = ( Formbuilder.templates['partials/save_button']() )) == null ? '' : __t) +
								((__t = ( Formbuilder.templates['partials/left_side']() )) == null ? '' : __t) +
								((__t = ( Formbuilder.templates['partials/right_side']() )) == null ? '' : __t) +
								'<div class="fb-clear"></div>';

				}
				return __p
		}

		this["Formbuilder"]["templates"]["partials/save_button"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {

						__p += '<div class="fb-save-wrapper">  <button class="js-save-form btn-success ' +
								((__t = ( Formbuilder.names.BUTTON_CLASS )) == null ? '' : __t) +
								'">'+Formbuilder.lang('Save')+'</button></div>';

				}
				return __p
		}

		this["Formbuilder"]["templates"]["partials/settings"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape, __j = Array.prototype.join;

				with (fied_settings) {
						__p += '<div class="fb-tab-pane active" id="settingsFields"></div>';

				}
				return __p
		}

		this["Formbuilder"]["templates"]["partials/field_base_settings"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape, __j = Array.prototype.join;

				with (fied_settings) {

						__p += '<div class="fb-tab-pane active" id="addField"> <div class="fb-add-field-types"><div class="section">';
						_.each(_.sortBy(Formbuilder.inputFields, 'order'), function (f) {

								__p += '<a data-field-type="' +
										((__t = ( f.field_type )) == null ? '' : __t) +
										'" style="text-align: inherit;" class="btn-default btn-block ' +
										((__t = ( Formbuilder.names.BUTTON_CLASS )) == null ? '' : __t) +
										'">          ' +
										((__t = ( _.template(f.addButton)() )) == null ? '' : __t) +
										'</a>';
						});

						/*__p += '</div><div class="section">';
						_.each(_.sortBy(Formbuilder.nonInputFields, 'order'), function (f) {

								__p += '<a data-field-type="' +
										((__t = ( f.field_type )) == null ? '' : __t) +
										'" class="' +
										((__t = ( Formbuilder.names.BUTTON_CLASS )) == null ? '' : __t) +
										'">          ' +
										((__t = ( f.addButton )) == null ? '' : __t) +
										'</a>';
						});*/

						__p += '</div></div></div>';

				}
				return __p
		}

		this["Formbuilder"]["templates"]["partials/fied_settings"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;

				with (fied_settings) {
						__p += '<div class="fb-tab-pane" id="editField">  <div class="fb-edit-field-wrapper"></div></div>';

				}
				return __p
		}

		this["Formbuilder"]["templates"]["partials/left_side"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {

						__p += '<div class="fb-left">  <ul class="fb-tabs">' +
								'<li class="active"><a data-target="#addField">' + Formbuilder.lang('Add new field') + '</a></li>' +
								'<li><a data-target="#editField">' + Formbuilder.lang('Edit field') + '</a></li>' +
								'</ul><div class="fb-tab-content">' +
								((__t = ( Formbuilder.templates['partials/field_base_settings']() )) == null ? '' : __t) +
								((__t = ( Formbuilder.templates['partials/fied_settings']() )) == null ? '' : __t) +
								'  </div></div>';
				}

				return __p
		}

		this["Formbuilder"]["templates"]["partials/right_side"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {

						__p += '<div class="fb-right">  <div class="fb-no-response-fields">'+Formbuilder.lang('No response fields') +
								'</div>  <div class="fb-response-fields"></div></div>';

				}
				return __p
		}

		/**
		 * View temlates
		 */

		this["Formbuilder"]["templates"]["view/base"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {
						__p += '<div class="subtemplate-wrapper">  <div class="cover"></div>  ' +
								((__t = ( Formbuilder.templates['view/label']({rf: rf}) )) == null ? '' : __t) +
								((__t = ( Formbuilder.fields[
												rf.get(Formbuilder.names.FIELD_TYPE)
										].view({rf: rf}) )) == null ? '' : __t) +
								((__t = ( Formbuilder.templates['view/description']({rf: rf}) )) == null ? '' : __t) +
								((__t = ( Formbuilder.templates['view/duplicate_remove']({rf: rf}) )) == null ? '' : __t) +
								'</div>';

				}
				return __p
		}

		this["Formbuilder"]["templates"]["view/description"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;

				with (fied_settings) {

						__p += '<span class="help-block">  ' +
								((__t = ( rf.get(Formbuilder.names.DESCRIPTION) )) == null ? '' : __t) +
								'</span>';

				}
				return __p
		}

		this["Formbuilder"]["templates"]["view/duplicate_remove"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;

				with (fied_settings) {

						__p += '<div class="actions-wrapper">  <a class="js-duplicate ' +
								((__t = ( Formbuilder.names.BUTTON_CLASS )) == null ? '' : __t) +
								' btn-xs btn-success" title="' + Formbuilder.lang('Duplicate Field') + '"><i class="fa fa-plus-circle"></i></a>';

						__p += ' <a class="js-clear ' +
								((__t = ( Formbuilder.names.BUTTON_CLASS )) == null ? '' : __t) +
								' btn-xs btn-danger" title="' + Formbuilder.lang('Remove Field') + '"><i class="fa fa-minus-circle"></i></a>';

						__p += '</div>';

				}
				return __p
		}

		this["Formbuilder"]["templates"]["view/label"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape, __j = Array.prototype.join;

				with (fied_settings) {

						__p += '<label>  <span>' +
								((__t = ( rf.get(Formbuilder.names.LABEL) )) == null ? '' : __t);

						if (rf.get(Formbuilder.names.REQUIRED)) {
								__p += '    <abbr title="required">*</abbr>  ';
						}

						__p += '</label>';

				}
				return __p
		}

		/**
		 * Editor templates
		 */

		this["Formbuilder"]["templates"]["edit/base"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {

						__p +=
								((__t = ( Formbuilder.templates['edit/common']({rf: rf}) )) == null ? '' : __t) +
								((__t = ( Formbuilder.fields[
									rf.get(Formbuilder.names.FIELD_TYPE)
								].edit({rf: rf}) )) == null ? '' : __t);

				}
				return __p
		}

		this["Formbuilder"]["templates"]["edit/common"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {

						__p += '<div class="fb-edit-section-header"> ' + Formbuilder.lang('Field type:') + ' ' +
								((__t = ( rf.get(Formbuilder.names.FIELD_TYPE) )) == null ? '' : Formbuilder.lang(__t)) +
								', Attribure id: ' + rf.cid + '</div><div class="fb-common-wrapper"><div class="fb-label-description">' +
								((__t = ( Formbuilder.templates['edit/label_description']() )) == null ? '' : __t) +
								'</div><div class="fb-common-checkboxes">';
				}
				return __p
		}

		this["Formbuilder"]["templates"]["edit/label_description"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {

						__p +=
								// '<input type="text" data-rv-input="model.' +
								// ((__t = ( Formbuilder.names.GROUP_NAME )) == null ? '' : __t) +
								// '" placeholder="' + Formbuilder.lang('Group name') + '" />' +


								'<input type="text" data-rv-input="model.' +
								((__t = ( Formbuilder.names.LABEL )) == null ? '' : __t) +
								'" placeholder="' + Formbuilder.lang('Field label') + '" />' +

								'<input type="text" data-rv-input="model.' +
								((__t = ( Formbuilder.names.LABELEN )) == null ? '' : __t) +
								'" placeholder="' + Formbuilder.lang('Field label En') + '" />' +

								'<textarea class="form-control" data-rv-input="model.' +
								((__t = ( Formbuilder.names.DESCRIPTION )) == null ? '' : __t) +
								'"  placeholder="' + Formbuilder.lang('Add a longer description to this field') + '"></textarea>' +

								'<textarea class="form-control" data-rv-input="model.' +
								((__t = ( Formbuilder.names.DESCRIPTIONEN )) == null ? '' : __t) +
								'"  placeholder="' + Formbuilder.lang('Add a longer Description En to this fielD') + '"></textarea>';
				}
				return __p
		}

		this["Formbuilder"]["templates"]["edit/text_area"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {

						if(typeof hideSizeOptions === 'undefined'){

								__p += '<div class="fb-edit-section-header">' + Formbuilder.lang('Attributes') + '</div>' + Formbuilder.lang('Rows') +
								'<input type="text" data-rv-input="model.' +

								((__t = ( Formbuilder.names.AREA_ROWS )) == null ? '' : __t) +
								'" style="width: 60px" />&nbsp;&nbsp;' + Formbuilder.lang('Cols') + '<input type="text" data-rv-input="model.' +

								((__t = ( Formbuilder.names.AREA_COLS )) == null ? '' : __t) +
								'" style="width: 60px" />';

						}

				}
				return __p
		}

		/**
		 * Field settings template
		 */

		this["Formbuilder"]["templates"]["edit/options"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape, __j = Array.prototype.join;

				with (fied_settings) {

						__p += '<div class="fb-edit-section-header">' + Formbuilder.lang('Options') + '</div>';

						if (typeof includeBlank !== 'undefined') {
								__p += '  <label><input type="checkbox" data-rv-checked="model.' +
										((__t = ( Formbuilder.names.INCLUDE_BLANK )) == null ? '' : __t) +
										'" />    ' + Formbuilder.lang('Include blank') + '</label>';
						}

						__p += '<div class="option" style="display: inline-flex;align-items: center; border: 1px solid #d9e1e3;" data-rv-each-option="model.' +
								((__t = ( Formbuilder.names.OPTIONS )) == null ? '' : __t) + '">  ';

						if(typeof showCheckBox !== 'undefined'){
								__p += '<input type="checkbox" \
									class="js-default-updated" readonly="readonly" data-rv-checked="option:checked" />  ';
						}

						if(typeof showRadio !== 'undefined'){
								__p += '<input type="radio" name="'+rf.cid+'" \
									class="js-default-updated" readonly="readonly" data-rv-checked="option:checked" />  ';
						}

						__p += 	'<div class="col-sm-7"> ' +
									'<input type="text" data-rv-input="option:label" ' +
									'class="option-label-input" placeholder=" ' +
									'' + Formbuilder.lang('Option label') + '" />' +

									'<input type="text" data-rv-input="option:label_en" ' +
									'class="option-label-input" placeholder=" ' +
									'' + Formbuilder.lang('Option label En') + '" />';
						if(typeof includeIndexOption !== 'undefined'){
								__p += '<input type="number" data-rv-input="option:index_value" ' +
									'class="option-label-input" placeholder=" ' +
									'' + Formbuilder.lang('Option Value') + '" />';
						}
									

						__p +=	'</div><div class="col-sm-4"> ' +

									'<a class="js-sort-up-option btn-xs btn-success ' + ((__t = ( Formbuilder.names.BUTTON_CLASS )) == null ? '' : __t) +
									'" title="' + Formbuilder.lang('Sort up') + '"><i class="fa fa-arrow-up"></i></a> ' +

									'<a class="js-sort-down-option btn-xs btn-success ' + Formbuilder.names.BUTTON_CLASS +
									'" title="' + Formbuilder.lang('Sort down') + '"><i class="fa fa-arrow-down"></i></a> ' +

									'<a class="js-remove-option btn-xs btn-danger ' + ((__t = ( Formbuilder.names.BUTTON_CLASS )) == null ? '' : __t) +
									'" title="' + Formbuilder.lang('Remove Option') + '"><i class="fa fa-minus-circle"></i></a>' +

								'</div>';

						__p += '</div>';

						if (typeof includeOther !== 'undefined') {
								__p += '  <label>    <input type="checkbox" data-rv-checked="model.' +
										((__t = ( Formbuilder.names.INCLUDE_OTHER )) == null ? '' : __t) +
										'" />    ' + Formbuilder.lang('Include "other"') + '  </label>';
						}

						__p += '<div class="fb-bottom-add">  <a class="js-add-option ' +
								((__t = ( Formbuilder.names.BUTTON_CLASS )) == null ? '' : __t) +
								'">' + Formbuilder.lang('Add option') + '</a></div>';

						/*if (typeof useMultiple !== 'undefined') {
							__p += '  <label><input type="checkbox" data-rv-checked="model.' +
									((__t = ( Formbuilder.names.MULTIPLE )) == null ? '' : __t) +
									'" />    ' + Formbuilder.lang('Allow Multiple Selections') + '</label>';
						}*/
				}
				return __p
		}

		this["Formbuilder"]["templates"]["edit/size"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {

						__p += '<div class="fb-edit-section-header">' + Formbuilder.lang('Size') + '</div>' +
								'<select data-rv-value="model.' + ((__t = ( Formbuilder.names.SIZE )) == null ? '' : __t)+ '">' +
								'<option value="small">' + Formbuilder.lang('Small') + '</option>' +
								'<option value="medium">' + Formbuilder.lang('Medium') + '</option>' +
								'<option value="large">' + Formbuilder.lang('Large') + '</option>' +
								'</select>';
				}
				return __p
		}

		this["Formbuilder"]["templates"]["edit/field_options"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {

						__p += '<label>  <input type="checkbox" data-rv-checked="model.' +
								((__t = ( Formbuilder.names.REQUIRED )) == null ? '' : __t) +
								'" />  ' + Formbuilder.lang('Required') + '</label><br>';

						// __p += '<label>  <input type="checkbox" data-rv-checked="model.' +
						// 		((__t = ( Formbuilder.names.LOCKED )) == null ? '' : __t) +
						// 		'" />  ' + Formbuilder.lang('Read only') + '</label><br>';

						// __p += '<label>  <input type="checkbox" data-rv-checked="model.' +
						// 		((__t = ( Formbuilder.names.VISIBLE )) == null ? '' : __t) +
						// 		'" />  ' + Formbuilder.lang('Visible') + '</label>';

				}
				return __p
		}

		this["Formbuilder"]["templates"]["edit/min_max"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {

						__p += '<div class="fb-edit-section-header">' + Formbuilder.lang('Minimum / Maximum') + '</div>' + Formbuilder.lang('Above') +
								'<input type="text" data-rv-input="model.' +

								((__t = ( Formbuilder.names.MIN )) == null ? '' : __t) +
								'" style="width: 60px" />&nbsp;&nbsp;' + Formbuilder.lang('Below') + '<input type="text" data-rv-input="model.' +

								((__t = ( Formbuilder.names.MAX )) == null ? '' : __t) +
								'" style="width: 60px" />';

				}
				return __p
		}

		this["Formbuilder"]["templates"]["edit/min_max_length"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {

						__p += '<div class="fb-edit-section-header">' + Formbuilder.lang('Length Limit') + '</div>' + Formbuilder.lang('Min') + '&nbsp;' +
								'<input type="text" data-rv-input="model.' +

								((__t = ( Formbuilder.names.MINLENGTH )) == null ? '' : __t) +
								'" style="width: 60px" />' + Formbuilder.lang('Max') + '&nbsp;<input type="text" data-rv-input="model.' +

								((__t = ( Formbuilder.names.MAXLENGTH )) == null ? '' : __t) +
								'" style="width: 60px" />&nbsp;&nbsp;<select data-rv-value="model.';

								// ((__t = ( Formbuilder.names.LENGTH_UNITS )) == null ? '' : __t) +
								// '" style="width: auto;">  <option value="characters">' + Formbuilder.lang('characters') + '</option>  ' +
								// '<option value="words">' + Formbuilder.lang('words') + '</option></select>';
				}
				return __p
		}

		this["Formbuilder"]["templates"]["edit/integer_only"] = function (fied_settings) {
				fied_settings || (fied_settings = {});
				var __t, __p = '', __e = _.escape;
				with (fied_settings) {

						__p += '<div class="fb-edit-section-header">' + Formbuilder.lang('Integer only') + '</div><label>  ' +
								'<input type="checkbox" data-rv-checked="model.' +

								((__t = ( Formbuilder.names.INTEGER_ONLY )) == null ? '' : __t) +
								'" />  ' + Formbuilder.lang('Only accept integers') + '</label>';

				}
				return __p
		}

}).call(this);


