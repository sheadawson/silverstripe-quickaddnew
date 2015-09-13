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
				.addClass()
				.text(ss.i18n._t('QUICKADDNEW.AddNew'))
				.attr('href', '#')
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
			var dialogHTMLURL =  action[0] + '/field/' + fieldName + '/AddNewFormHTML' + '?' + action[1];
			this.setURL(dialogHTMLURL.replace(/[\[\]']+/g,''));
			
			// configure the dialog
			this.getDialog().data("field", this).dialog({
				autoOpen: 	false,
				width:   	600,
				modal:    	true,
				title: 		this.data('dialog-title'),
				position: 	{ my: "center", at: "center", of: window }
			});

			// submit button loading state while form is submitting 
			this.getDialog().on("click", "button", function() {
				$(this).addClass("loading ui-state-disabled");
			});

			// handle dialog form submission
			this.getDialog().on("submit", "form", function() {
				
				var dlg = self.getDialog().dialog(),
					options = {};

				// if this is a multiselect field, send the existing values
				// along with the form submission so they can be included in the 
				// replacement field
				if(self.val() && typeof self.val() === 'object'){
					options.data = {
						existing : self.val().join(',')	
					}
				}

				options.success = function(response) {
					if($(response).is(".field")) {
						self.getDialog().empty().dialog("close");
						self.parents('.field:first').replaceWith(response);
					} else {
						self.getDialog().html(response);
					}
				}

				$(this).ajaxSubmit(options);

				return false;
			});
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
		}
	});
});


