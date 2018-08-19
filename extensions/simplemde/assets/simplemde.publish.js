(function($, Simplemde, undefined) {
	'use strict';
	
	$.fn.initSimplemde = function (options) {
	
		return this.each(function (index, element) {
		
			// Editor Configuration
			// https://github.com/NextStepWebs/simplemde-markdown-editor/
			
			var configuration = $.extend({
				element: element,
				spellChecker: false,
				status: false,
				tabSize: 4,
				indentWithTabs: false,
				forceSync: true,
				autoDownloadFontAwesome: false,
				toolbar: ["heading-1","heading-2","heading-3","|","bold","italic","|","link","|","unordered-list","ordered-list","|","horizontal-rule"]
			}, options );
			
			var simplemde = new SimpleMDE(configuration);
			
			$(element).closest('.field').addClass('field-simplemde');
			
			// Calculate min height based on the number of rows (default textboxes)
			// or already existing height-class (multilingual textboxes)
			
			var minHeight;
			var isMultilingualTextbox = $(element).closest('.field').is('.field-multilingual_textbox');
			
			// Multilingual textboxes
			
			if (isMultilingualTextbox) {
				
				var size = $(element).attr("class").match(/size[\w-]*\b/).toString().substr(5);
				
				if (size === 'small') {
					minHeight = '5';
				} else if (size === 'medium') {
					minHeight = '20';
				} else if (size === 'large') {
					minHeight = '35';
				} else if (size === 'huge') {
					minHeight = '50';
				}
				
			// Default textboxes
				
			} else {
				
				var rows = $(element).attr('rows');
				
				if (rows <= 1) {
					minHeight = '2.6';
				} else if (rows <= 4) {
					minHeight = '8.9';
				} else if (rows <= 7) {
					minHeight = '15';
				} else if (rows <= 10) {
					minHeight = '21.3';
				} else if (rows <= 13) {
					minHeight = '27.6';
				} else if (rows <= 16) {
					minHeight = '33.9';
				} else {
					minHeight = (rows === '0') ? false : ((( Math.round (rows * 206.666666 - 1) ) / 100));
				}
				
			}
			
			// Append height to editor
			
			$(element).siblings('.CodeMirror').css(
				'min-height', minHeight+'rem'
			).find('.CodeMirror-scroll').css(
				'min-height', minHeight+'rem'
			);
		});
	};
	
}(this.jQuery, this.Simplemde));


(function($, undefined) {
	'use strict';
	$(function() {
		$('textarea[class*="markdown"], textarea[class*="commonmark"]').initSimplemde();
	});
}(this.jQuery));
