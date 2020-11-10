;(function ($, w) {
	var $window = $(w);
	var $slider  	= $( "#tor-range-slider" ),
		$rangeInput = $('.tor-range-input');

	$slider.slider({
		range: true,
		orientation: "horizontal",
  		min: $slider.data('min'),
  		max: $slider.data('max'),
  		values: $slider.data('defaults'),
  		step: $slider.data('step'),
		slide: (event, ui) => {
			$rangeInput.val(ui.values[0] + ':' + ui.values[1]);
			$('.tor-range-values-min').html(ui.values[0]);
			$('.tor-range-values-max').html(ui.values[1]);
		},

		stop: (event, ui) => {
			$rangeInput.trigger('change');
			// $('.tor-apply-filters').trigger('click');
			ajaxify();
		}
	});

	$(document).on('change', '.tor-category-filter,.tor-tag-filter', function(event) {
		event.preventDefault();
		$(this).parent().children('.child').show();
		ajaxify();
	});

	$(document).on('change','.tor-attribute-filter', function(event) {
		let name = this.name;
		var selected = $(this).find('option:selected').val();
		
		if( name in TorFilter.product_attributes  ) {
			console.log(selected);
			if( selected ) {
				TorFilter.product_attributes[name].push(selected);
			} else {
				TorFilter.product_attributes[name] = [];
			}
		}

		ajaxify();
	});


	$(document).on('change','.tor-color-filter', function(event) {
		let name = this.name;
		if( name in TorFilter.product_attributes ) {
			if( $(this).is(":checked") ) {
				TorFilter.product_attributes[name].push( this.value );
			} else {
				let att = TorFilter.product_attributes[name].filter( item => item != this.value );
				TorFilter.product_attributes[name] = att;
			}
		}
		ajaxify();
	});



	$(document).on('click','.tor-apply-filters', function(event) {
		event.preventDefault();
		ajaxify();
	});

	$(document).on('click','.tor-reset-filters', function(event) {
		
		$(".tor-category-filter").prop("checked", false);
		$(".tor-tag-filter").prop("checked", false);
		$(".tor-color-filter").prop("checked", false);
		$(".tor-size-filter").prop("checked", false);
  		
  		$slider.slider("values", 0, $slider.data('min') );
  		$slider.slider("values", 1, $slider.data('max') );
		
		$('.tor-range-values-min').html( $slider.data('min') );
		$('.tor-range-values-max').html( $slider.data('max') );

		ajaxify();
	});

    // $('.tor-tag-js-filter').select2();

    $('.tor-loading').show();

	function ajaxify() {

	    var product_cat   = [],
	    	product_tag   = [],
	    	product_color = [],
	    	product_size = [];

		$('.tor-category-filter:checked').each(function(i){
          	product_cat[i] = $(this).val();
        });

        $('.tor-tag-filter:checked').each(function(i){
          	product_tag[i] = $(this).val();
        });

        $('.tor-color-filter:checked').each(function(i){
          	product_color[i] = $(this).val();
        });

        $('.tor-size-filter:checked').each(function(i){
          	product_size[i] = $(this).val();
        });

        var provider = $('.tor-category').data('provider')
        var price_range = $('.tor-range-input').val(),
        queryId = 'default';

        const requestData  = {};
		
		requestData.action = 'tor_filters';
		requestData.provider = provider;
		
		requestData.query = {
			'_tax_query_product_cat': product_cat,
			'_tax_query_product_tag': product_tag,
			// '_tax_query_pa_color': product_color,
			// '_tax_query_pa_size': product_size,
			'_meta_query_price_range': price_range
		};

		requestData.query = {
			...requestData.query,
			...TorFilter.product_attributes
		}

		console.log( requestData.query );

		/*
		if( product_cat.length ) {
			requestData.query._tax_query_product_cat = product_cat;
		}

		if( product_tag.length ) {
			requestData.query._tax_query_product_tag = product_tag;
		}

		if( product_tag.length ) {
			requestData.query._tax_query_product_tag = product_tag;
		}

		if( product_color.length ) {
			requestData.query._tax_query_pa_color = product_color;
		}

		if( product_size.length ) {
			requestData.query._tax_query_pa_size = product_size;
		}
		*/

		requestData.props 	 = getNesting( TorFilter, 'props', provider, queryId ) || {};
		requestData.defaults = getNesting( TorFilter, 'queries', provider, queryId ) || {};
		requestData.settings = getNesting( TorFilter, 'settings', provider, queryId ) || {};

		// if (paged > 1) {
		// 	requestData.paged = paged;
		// }

		$.ajax({
			url: TorFilter.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: requestData,
			beforeSend: function( xhr ) {
				$('.tor-loading').show();	
			},
			success: function(response) {

			},
			complete: function() {
				$('.tor-loading').hide();	
			},

			error: function() {

			}
		})
	}

	$('#loading-image').bind('ajaxStart', function(){
	    $(this).show();
	}).bind('ajaxStop', function(){
	    $(this).hide();
	});


	function getNesting( obj ) {
		const nesting = Array.from(arguments).splice(1);
		let isNestingExist = true;

		for (let key of nesting) {
			if (!obj[key]) {
				isNestingExist = false
				break;
			}

			obj = obj[key];
		}

		return isNestingExist ? obj : false;
	}

} (jQuery, window));