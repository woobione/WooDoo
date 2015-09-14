/**
 * Expense Checkbox Deluxifier for jQuery
 * @author Anton Netterwall
 */
(function( $ ) {
	window.CheckboxDeluxe = function (options) {
		this.options = $.extend({
			autoLoad: false,
			checkboxSelector: 'input[type=checkbox]:not(.normal)',
			checkboxDeluxeClass: 'checkbox-deluxe',
			checkboxDeluxeSliderClass: 'checkbox-deluxe-slider',
			checkboxDeluxeActiveClass: 'active',
			checkboxDeluxeFocusClass: 'focus',
			checkboxDeluxeDisabledClass: 'disabled',
			checkboxDeluxeLabelClass: 'label',
			checkboxDeluxeOnLabelClass: 'on',
			checkboxDeluxeOffLabelClass: 'off',
			onLabel: 'Ja',
			offLabel: 'Nej'
		}, options);

		// Constructor
		this.initialize = function () {
			if (this.options.autoLoad) {
				var parentThis = this;
				$(function () {
					parentThis.AddDeluxifier(parentThis.options.checkboxSelector);
				});
			}
		};

		this.initialize();
	};

	// Add deluxifier for specified selector
	CheckboxDeluxe.prototype.AddDeluxifier = function (checkboxSelector) {
		var self = this;

		$(checkboxSelector).each(function () {
			var checkbox = $(this);
			checkbox.css({ 'position': 'absolute', 'width': '0px', 'height': '0px', 'min-width': '0px', 'padding': '0px', 'margin': '0px', 'z-index': '0' });
			checkbox.wrap('<div class="' + self.options.checkboxDeluxeClass + '"></div>')
				.after('<div class="' + self.options.checkboxDeluxeSliderClass + '"></div><div class="' + self.options.checkboxDeluxeOnLabelClass + ' ' + self.options.checkboxDeluxeLabelClass + '">' + self.options.onLabel + '</div><div class="' + self.options.checkboxDeluxeOffLabelClass + ' ' + self.options.checkboxDeluxeLabelClass + '">' + self.options.offLabel + '</div>')
				.change(function () { $(this).parent('.' + self.options.checkboxDeluxeClass).toggleClass(self.options.checkboxDeluxeActiveClass, $(this).prop('checked')); })
				.focus(function () { $(this).parent('.' + self.options.checkboxDeluxeClass).addClass(self.options.checkboxDeluxeFocusClass); })
				.blur(function () { $(this).parent('.' + self.options.checkboxDeluxeClass).removeClass(self.options.checkboxDeluxeFocusClass); })
				.parent('.' + self.options.checkboxDeluxeClass)
				.attr('title', checkbox.attr('title'))
				.toggleClass(self.options.checkboxDeluxeActiveClass, checkbox.prop('checked'))
				.toggleClass(self.options.checkboxDeluxeDisabledClass, checkbox.prop('disabled'))
				.click(function () {
					checkbox.trigger('click');
					checkbox.focus();
				});
		});

		return this;
	};
}( jQuery ));