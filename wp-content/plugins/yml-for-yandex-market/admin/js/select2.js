(function ($) {
	'use strict';

	$(document).ready(function () {
		// select2 - place 4 from 5 (with woocommerce serch)
		// simple multiple select with AJAX search
		$('#y4ym_select2_arr').select2({
			ajax: {
				url: ajaxurl, // AJAX URL is predefined in WordPress admin
				dataType: 'json',
				delay: 250, // delay in ms while typing when to perform a AJAX search
				data: function (params) {
					return {
						q: params.term, // search query
						action: 'y4ym_select2' // wp_ajax_{action} - AJAX action for admin-ajax.php
					};
				},
				processResults: function (data) {
					var options = [];
					if (data) {

						// data is the array of arrays, and each of them contains ID and the Label of the option
						$.each(data, function (index, text) { // do not forget that "index" is just auto incremented value
							options.push({ id: text[0], text: text[1] });
						});

					}
					return {
						results: options
					};
				},
				cache: true
			},
			minimumInputLength: 3 // the minimum of symbols to input before perform a search
		});
	});

})(jQuery);
