jQuery.entwine("quickaddnew", function($) {
	var fieldSelector = '.field.quickaddnew-field .quickaddnew-field';

	$(".quickaddnew-button").entwine({
		onmatch: function() {
			var self = this;
		},

		onclick: function() {
			this.siblings(fieldSelector).showDialog();
			return false;
		}
	});

	$(fieldSelector).entwine({
		Loading: null,
		Dialog:  null,
		URL:  null,
		onmatch: function() {
			var self = this;

			//Check to see if quickaddnew has been bound to this field before, sometimes jQuery plugins like Select2
			//will trigger a binding a second time that we don't want.
			if($(this).parents().children('.quickaddnew-button').length > 0) {
				return;
			}
			// create add new button
			var button = $("<button />")
				.attr('type', 'button')
				.attr('href', '#')
				.text(ss.i18n._t('QUICKADDNEW.AddNew'))
				.addClass("quickaddnew-button ss-ui-button ss-ui-button-small")
				.appendTo(self.parents('div:first'));

			// create dialog
			var dialog = $("<div />")
				.addClass("quickaddnew-dialog")
				.appendTo(self.parents('div:first'));

			this.setDialog(dialog);

			// set URL
			var fieldName = this.attr('name');
			if (this.hasClass('checkboxset')) fieldName = this.find('input:checkbox').attr('name').replace(/\[[0-9]+\]/g, '');
			var action = this.parents('form').attr('action').split('?', 2); //add support for url parameters e.g. ?locale=en_US when using Translatable
			
			var dialogHTMLURL = this.data('quickaddnew-action');
			if (!dialogHTMLURL) {
				// Fallback to default action
				dialogHTMLURL = action[0] + '/field/' + fieldName + '/AddNewFormHTML';
			}
			if (action[1]) {
				dialogHTMLURL += '?' + action[1];
			}
			dialogHTMLURL = dialogHTMLURL.replace(/[\[\]']+/g,'');
			this.setURL(dialogHTMLURL);

			// configure the dialog
			this.getDialog().data("field", this).dialog({
				autoOpen: 	false,
				width:   	600,
				modal:    	true,
				title: 		this.data('dialog-title'),
				position: 	{ my: "center", at: "center", of: window }
			});

			// handle dialog form submission
			this.getDialog().on("submit", "form", function() {

				var dlg = self.getDialog().dialog(),
					options = {};

				var $submitButtons = $(this).find('input[type="submit"], button[type="submit"]');
				$submitButtons.addClass("loading ui-state-disabled");

				// if this is a multiselect field, send the existing values
				// along with the form submission so they can be included in the
				// replacement field
				if(self.val() && typeof self.val() === 'object'){
					options.data = {
						existing : self.val().join(',')
					};
				}

				options.success = function(res) {
					var $response = $(res);
					if($response.is('.field')) {
						self.getDialog().empty().dialog('close');
						var $newInput = $response.find(self[0].tagName);
						// Replace <select> <option>'s rather than the entire HTML block
						// to avoid JS hooks being lost on the frontend.
						if ($newInput[0] && $newInput[0].tagName === 'SELECT') {
							self.html($newInput.children());

							// Support legacy and new chosen
							self.trigger('liszt:updated').trigger('chosen:updated');
							// Support select2
							self.trigger('change.select2');
						} else {
							self.parents('.field:first').replaceWith(res);
						}
					} else {
						self.getDialog().html(res);
					}
				};
				options.complete = function() {
					$submitButtons.removeClass("loading ui-state-disabled");
				};

				$(this).ajaxSubmit(options);

				return false;
			});

			this._super();
		},

		showDialog: function(url) {
			var dlg = this.getDialog();
			// Check to see we have a dialog, other jquery plugins like Select2 can get bound to by accident
			if (dlg !== null) {
				dlg.empty().dialog("open").parent().addClass("loading");

				dlg.load(this.getURL(), function(){
					dlg.parent().removeClass("loading");
					// set focus to first input element
					dlg.find('form :input:visible:enabled:first').focus();
				});
			}
			this._super();
		}
	});
});


