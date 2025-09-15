let json_response;

(function( $ ) {
	'use strict';

	let sejoli_get_notification = function() {
		return {
			'invoices':         $('input[name=all-invoices]').val(),
			'total':            parseInt($('input[name=total-invoice]').val()),
			'send-email':       $('input[name=send-email]').is(':checked'),
			'email-title':      $('input[name=email-title]').val(),
			'email-content':    tinyMCE.get('email-content').getContent(),
			'send-whatsapp':    $('input[name=send-whatsapp]').is(':checked'),
			'whatsapp-content': $('textarea[name=whatsapp-content]').val(),
			'send-sms':         $('input[name=send-sms]').is(':checked'),
			'sms-content':      $('textarea[name=sms-content]').val()
		}
	}

	let sejoli_process_notification = function(data, order_total) {

		$.ajax({
			url:      sejoli_bulk.send_notification.ajaxurl,
			type:     'POST',
			dataType: 'json',
			data:     {
				data : data,
				'sejoli-nonce' : sejoli_bulk.send_notification.nonce
			},
			success:  function(response) {

				let total = parseInt(response.total),
					left = total,
					indicator = 0,
					tmpl = $.templates('#bulk-message'),
					html = '';

				indicator  = ((order_total - left) / order_total )* 100;
				html       = tmpl.render({
					'class' : 'success',
					'message' : response.message
				});

				console.log(total, left, html, indicator);

				$(html).appendTo('.bulk-process-info');

				$('#bulk-upload-progress').progress({
					percent : indicator
				});

				if(0 < left) {

					data = sejoli_process_notification(response, order_total);
				} else {
					confirm("Proses selesai");
				}
			}
		});

		return json_response;
	}

	$(document).ready(function(){

		sejoli.helper.blockUI('#bulk-notification-holder');
		sejoli.helper.daterangepicker("input[name='date-range']");

        $.ajax({
            url : sejoli_bulk.product.select.ajaxurl,
            data : {
                action : 'sejoli-product-options',
                nonce : sejoli_bulk.product.select.nonce
            },
            type : 'GET',
            dataType : 'json',
            beforeSend : function() {
            },
            success : function(response) {

                $('#product_id').select2({
                    allowClear: true,
                    placeholder: sejoli_bulk.product.placeholder,
                    data : response.results
                });

				sejoli.helper.unblockUI('#bulk-notification-holder');
            }
        });

		$('body').on('click', '.check-invoice', function(){
			$.ajax({
				url : sejoli_bulk.order.check.ajaxurl,
				data: {
					nonce:        sejoli_bulk.order.check.nonce,
					status:       $('[name=order-status]').val(),
					product:      $('[name=product]').val(),
					'date-range': $('[name=date-range]').val()
				},
				type:     'GET',
				dataType: 'json',
				beforeSend : function() {
					sejoli.helper.blockUI('#bulk-notification-holder');
				},
				success: function(response) {
					sejoli.helper.unblockUI('#bulk-notification-holder');
					let tmpl = $.templates('#bulk-message'),
						count = parseInt(response.total),
						html_class = '',
						html_message = '',
						valid = false;

					if(0 < count) {
						html_class = 'info';
						html_message = 'Ditemukan ' + count + ' invoice';
						valid = true;
					} else {
						html_class = 'warning';
						html_message = 'Tidak ditemukan invoice berdasarkan filter';
					}

					$('.invoice-report').html(tmpl.render({
						class	: html_class,
						message : html_message
					}));

					if(true === valid) {
						$('input[name=all-invoices]').val(response.orders.join('|'));
						$('input[name=total-invoice]').val(response.total);

						$('#bulk-notification-holder').find('.editor').show();
						$('#shortcode-list').show();
					} else {
						$('#bulk-notification-holder').find('.editor').hide();
					}
				}
			})
			return false;
		});

		$('body').on('click', '.send-notification', function(){
			let data = sejoli_get_notification(),
				total = parseInt(data.total);

			$('#bulk-upload-progress').show();

			sejoli_process_notification(data, total);
		});

		$('.ui.dropdown').dropdown();
		$('.ui.checkbox').checkbox();

	});

})( jQuery );
