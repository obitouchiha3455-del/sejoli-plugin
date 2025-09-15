let sejoli;

function sejoli_get_nested_object(nestedObj, pathArr){
    return pathArr.reduce((obj, key) =>
        (obj && obj[key] !== 'undefined') ? obj[key] : '', nestedObj);
}

function sejoli_sanitize_title( str = '' )
{
	str = str.toString();
	str = str.replace(/^\s+|\s+$/g, ''); // trim
	str = str.toLowerCase();

	// remove accents, swap ñ for n, etc
	var from = "àáäâèéëêìíïîòóöôùúüûñçěščřžýúůďťň·/_,:;";
	var to   = "aaaaeeeeiiiioooouuuuncescrzyuudtn------";

	for (var i=0, l=from.length ; i<l ; i++)
	{
		str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
	}

	str = str.replace('.', '-') // replace a dot by a dash
		.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
		.replace(/\s+/g, '-') // collapse whitespace and replace by a dash
		.replace(/-+/g, '-') // collapse dashes
		.replace( /\//g, '' ); // collapse all forward-slashes

	return str;
}

(function( $ ) {
	'use strict';

	sejoli = {
		var : {
			search : [],
			hari : ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
			bulan : ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            productOptions : []
		},
		daterangepicker: function(element) {
			if($().daterangepicker) {
				var start = moment().subtract(29, 'days');
				var end = moment();

				$(element).daterangepicker({
					startDate: start,
					endDate: end,
					locale: {
						format: 'YYYY-MM-DD'
					},
					ranges: {
					  	'Today'         : [moment(), moment()],
					  	'Last 7 Days'   : [moment().subtract(6, 'days'), moment()],
					  	'Last 30 Days'  : [moment().subtract(29, 'days'), moment()],
					  	'This month'    : [moment().startOf('month'), moment().endOf('month')],
					  	'Last 3 Months' : [moment().subtract(3, 'month'), moment()],
					  	'Last 6 Months' : [moment().subtract(6, 'month'), moment()],
					  	'Last 1 Year'   : [moment().subtract(1, 'year'), moment()],
					  	'Last 2 Years'  : [moment().subtract(2, 'year'), moment()],
					}
				});
			}
		},
		filter: function(selector) {
			var data = [];
            var val = '';
            $( selector ).find('select,input,textarea').each(function(i,e){
                if ( $(e).attr('type') === 'checkbox' ){
                    if ( $(e).prop('checked') ) {
                        val = 1;
                    } else {
                        val = 0;
                    }
                } else {
                    val = $(e).val();
                }
                data.push({
                    'name': $(e).attr('name'),
                    'val': val,
                });
            });
            return data;
		},
		block: function( selector = "" ) {
			if ( selector ) {
				$( selector ).block({
					message: '<i class="huge notched circle loading icon"></i>',
					css: { backgroundColor: 'transparent', border: 0, color: '#fff' }
				});
			} else {
				$.blockUI({
					message: '<i class="huge notched circle loading icon"></i>',
					css: { backgroundColor: 'transparent', border: 0, color: '#fff' }
				});
			}
		},
		unblock: function( selector = "" ) {
			if ( selector ) {
				$( selector ).unblock();
			} else {
				$.unblockUI();
			}
		},
		convertdate: function(mysql_date, format) {

			if(!mysql_date) {
				return null;
			}

			let t = mysql_date.split(/[- :]/),
				   d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);

			let tanggal = d.getDate(),
				   xhari = d.getDay(),
				   xbulan = d.getMonth(),
				   xtahun = d.getYear();

			let hari = sejoli.var.hari[xhari],
				   bulan = sejoli.var.bulan[xbulan],
				   tahun = (xtahun < 1000)?xtahun + 1900 : xtahun;

			return tanggal + ' ' + bulan + ' ' + tahun;
		},
		formatPrice: function(angka) {
			var angka  = parseInt(angka);
			var rupiah   = '';
			var angkarev = angka.toString().split('').reverse().join('');
			for(var i = 0; i < angkarev.length; i++) if(i%3 == 0) rupiah += angkarev.substr(i,3)+'.';
			return rupiah.split('',rupiah.length-1).reverse().join('');
		},
		leaderBoard : {

			init: function() {

				sejoli.leaderBoard.renderData();

				$(document).on('click', '#leaderboard-filter-button', function(e){
					e.preventDefault();
					sejoli.leaderBoard.renderData();
				});

				sejoli.product.select2();

				sejoli.daterangepicker("#date-range");

				$('#date-range').val('');

			},
			renderData: function() {

				var date_range_str = $('#date-range').val();
				var date_range_arr = date_range_str.split(' - ');
				var start_date = date_range_arr[0];
				var end_date = date_range_arr[1];

				$.ajax({
					url : sejoli_member_area.ajaxurl,
					method: 'POST',
					data : {
						action: 'sejoli-statistic-commission',
						product_id: $('#product_id').val(),
						start_date: start_date,
						end_date: end_date,
                        order_status: 'completed',
						nonce: sejoli_member_area.leaderboard.nonce,
					},
					beforeSend : function() {
						sejoli.block();
					},
					success : function(data) {
						sejoli.unblock();
						// console.log(data);
						if ( data ) {

							$('#nodata-holder').hide();
							$('#leaderboard-table-holder').hide();

							var cards = [];
							for (var i = 0; i < data.slice(0,10).length; i++) {
								cards[i] = $.templates('#tmpl-leaderboard-card').render(data[i]);
							}

							if ( data.length < 10 ) {
								for (var i = data.length; i < 10; i++) {
									cards[i] = $.templates('#tmpl-leaderboard-card-placeholder').render();
								}
							}

							var tmpl = $.templates('#tmpl-leaderboard-cards');
							$('#leaderboard-cards-holder').html(tmpl.render({content:cards})).show();

							if ( data.length > 10 ) {

								var table = $.templates('#tmpl-leaderboard-table-row').render(data.slice(10));

								var tmpl = $.templates('#tmpl-leaderboard-table');
								$('#leaderboard-table-holder').html(tmpl.render({content:table})).show();
							}

						} else {
							var tmpl = $.templates('#tmpl-nodata');
							$('#nodata-holder').html(tmpl.render()).show();
							$('#leaderboard-cards-holder').hide();
							$('#leaderboard-table-holder').hide();
						}
					}
				});
			}
		},
		affiliate : {
			commission : {
				renderData : function() {

					sejoli.table.affiliate.commission = $('#commission').DataTable({
						"language"	: dataTableTranslation,
						'ajax'		: {
							'method': 'POST',
							'url'   : sejoli_member_area.ajaxurl,
							'data'  : function(data) {
								data.action = 'sejoli-commission-table'
								data.filter = sejoli.filter('#commission-filter');
								data.nonce = sejoli_member_area.commission.table.nonce;
							}
						},
						// "bLengthChange": false,
						"bFilter": false,
						"serverSide": true,
						pageLength : 50,
						lengthMenu : [
							[10, 50, 100, 200, -1],
							[10, 50, 100, 200, dataTableTranslation.all],
						],
						order: [
							[ 0, "desc" ]
						],
						columnDefs: [
							{
								targets: [1, 4],
								orderable: false
							},{
								targets: 0,
								data : 'ID',
								render : function(data, type, full) {
									let tmpl = $.templates('#order-detail'),
										subsctype = null;

									return tmpl.render({
										id : full.ID,
										order_id : full.order_id,
										product : full.product_name,
										date : ("0000-00-00 00:00:00" !== full.updated_at) ? sejoli.convertdate(full.updated_at) : sejoli.convertdate(full.created_at)
									})
								}
							},{
								targets: 1,
								width: '15%',
								data: 'affiliate_id',
								render : function(data, type, full) {
									return full.affiliate_name;
								}
							},{
								targets: 2,
								width: '32px',
								data : 'tier',
								className : 'center',
							},{
								targets: 3,
								width: '180px',
								data : 'commission',
								className : 'price',
								render : function(data ,type, full) {
									return sejoli_member_area.text.currency + sejoli.formatPrice(data);
								}
							},{
								targets: 4,
								width : '100px',
								data : 'status',
								render : function( data, type, full) {
									let tmpl = $.templates('#order-status'),
										status = full.status;

									if(1 === parseInt(full.paid_status)) {
										status = 'paid';
									}

									return tmpl.render({
										status : status,
										label : sejoli_member_area.commission.status[status],
										color : sejoli_member_area.color[status]
									});
								}
							}
						]
					});

					sejoli.table.affiliate.commission.on( 'preXhr.dt', function( e, settings, data ){
						sejoli.block('#commission');
					});

					sejoli.table.affiliate.commission.on( 'xhr.dt', function ( e, settings, json, xhr ) {
						sejoli.unblock('#commission');
					});

					sejoli.daterangepicker("#date-range");

					$('#date-range').val('');

					$(document).on('click', '#commission-filter-button', function(e){
						e.preventDefault();
						sejoli.table.affiliate.commission.ajax.reload();
					});

					$(document).on('click','.show-filter-form', function(){
						$('#filter-form-wrap').modal('show');
					});

					$(document).on('click','.filter-form',function(e){
						e.preventDefault();
						$('#filter-form-wrap').modal('hide');
						sejoli.table.affiliate.commission.ajax.reload();
					});

					sejoli.product.select2();

					$(document).on('click', '.order-detail-trigger', function(){
						let order_id = $(this).data('id');

						$.ajax({
							url  : sejoli_member_area.order.detail.ajaxurl,
							type : 'GET',
							data : {
								order_id : order_id,
								nonce    : sejoli_member_area.order.detail.nonce
							},
							beforeSend: function() {
								sejoli.block('#commission');
							},
							success : function(response) {
								sejoli.unblock('#commission');
								let tmpl = $.templates('#order-modal-content');
								let affiliate_name  = null,
									affiliate_phone = null,
									affiliate_email = null;
								let buyer_phone = null,
									buyer_email = null;

								if(0 != response.affiliate_id && response.affiliate !== null) {
									affiliate_name  = response.affiliate.data.display_name;
									affiliate_phone = response.affiliate.data.meta.phone;
									affiliate_email = response.affiliate.data.user_email;
								}

								if(response.user !== null) {
									buyer_phone = response.user.data.meta.phone;
									buyer_email = response.user.data.user_email;
								}

								let subscription = sejoli_member_area.subscription.type[response.type];
								let variants     = null;
								let variant_tmpl = $.templates('#order-variant-data');

								if( typeof response.meta_data.variants !== 'undefined' ) {
									variants = variant_tmpl.render( response.meta_data.variants )
								}

								let content = tmpl.render({
									id              : order_id,
									date            : sejoli.convertdate( response.created_at ),
									buyer_name      : response.user.data.display_name,
									buyer_email     : buyer_email,
									buyer_phone     : buyer_phone,
									variants        : variants,
									product_name    : response.product.post_title,
									quantity        : response.quantity,
									total           : sejoli_member_area.text.currency + sejoli.formatPrice( response.grand_total ),
									status          : sejoli_member_area.order.status[response.status],
									color           : sejoli_member_area.color[response.status],
									subscription    : ( null != subscription ) ? subscription.toUpperCase() : null,
									courier         : response.courier,
									address         : response.address,
									parent_order    : (response.order_parent_id > 0) ? response.order_parent_id : null,
									affiliate_name  : affiliate_name,
									affiliate_phone : affiliate_phone,
									affiliate_email : affiliate_email,
									shipping_data   : ( null != response.meta_data.shipping_data ) ? response.meta_data.shipping_data : null,
									markup_price    : sejoli_member_area.text.currency + sejoli.formatPrice( response.meta_data.markup_price ),
									ppn   			: ( null != response.meta_data.ppn ) ? (Math.round(response.meta_data.ppn * 100) / 100).toFixed(2) : null,
									ppn_total       : ( null != response.meta_data.ppn_total ) ? response.meta_data.ppn_total : null,
									unique_code     : ( null != response.meta_data.unique_code ) ? response.meta_data.unique_code : null,
									payment_gateway : response.payment_gateway
								});

								$('#commission-detail-holder').html(content).modal('show');
							}
						})
					});
				}
			},
			link: {
				init: function() {
					sejoli.affiliate.link.other();
				},
				processForm: function() {
					$.ajax({
						url : sejoli_member_area.ajaxurl,
						method: 'POST',
						dataType : 'json',
						data : {
							product_id : $('#product_id').val(),
							action: 'sejoli-product-affiliate-link-list',
							nonce : sejoli_member_area.affiliate.link.nonce,
						},
						beforeSend : function() {
							sejoli.block();
						},
						success : function(data) {
							sejoli.unblock();
							if ( !$.isEmptyObject( data ) ) {
								var template = $.templates("#affiliate-link-tmpl");
								var htmlOutput = template.render({'data':data});
								$("#affiliate-link-holder").html(htmlOutput);
								$('#aff-link-parameter').show();
							} else {
								$('#affiliate-link-holder').html('<div class="ui red message">Data not found!</div>');
							}
						}
					});
				},
				other: function() {
					$(document).on("click",'#affiliate-link-generator-button', function(e){
						e.preventDefault();
						$('#param-platform').val('').trigger('change');
						$('#aff-link-parameter').trigger('reset').hide();
						if ( $('#product_id').val() !== '' ) {
							sejoli.affiliate.link.processForm();
						} else {
							$('#affiliate-link-holder').html('<div class="ui red message">Please select a product</div>');
						}
					});

					sejoli.product_affiliate.select2();

					$(document).on('submit','#aff-link-parameter',function(e){
						e.preventDefault();

						var product_id = $('#product_id').val();

						if ( product_id === '' ) {
							alert('Silahkan generate produk terlebih dulu');
						} else {

							var param_platform = sejoli_sanitize_title($('#param-platform').val()),
                                param_id = sejoli_sanitize_title($('#param-id').val()),
                                param_coupon = $('#param-coupon').val();

							$('#affiliate-link-holder input').each(function(){

								var link = $(this).val(),
    								link_arr = link.split("?"),
    								link_new = link_arr[0];


                                if( param_platform && param_id ) {

									var separator = link_new.indexOf('?') !== -1 ? '&' : '?';
									link_new += separator + 'utm_source=' + param_platform + '&' + 'utm_media=' + param_id;

                                }

                                if( param_coupon) {
                                    var separator = link_new.indexOf('?') !== -1 ? '&' : '?';
                                    link_new += separator + 'coupon=' + param_coupon;
                                }

								$(this).val(link_new);

							});
						}
					});
				}
			},
			help: {
				init: function() {
					sejoli.affiliate.help.other();
				},
				processForm: function() {
					$.ajax({
						url : sejoli_member_area.ajaxurl,
						method: 'POST',
						dataType : 'json',
						data : {
							action: 'sejoli-product-affiliate-help-list',
							product_id: $('#product_id').val(),
							nonce: sejoli_member_area.affiliate.help.nonce,
						},
						beforeSend : function() {
							sejoli.block();
						},
						success : function(data) {
							sejoli.unblock();
							// console.log(data);

							if ( $.isArray( data ) && data.length !== 0 ) {

								var template = $.templates("#affiliate-help-tmpl");
								var htmlOutput = template.render({'data':data});
								$("#affiliate-help-holder").html(htmlOutput);

							} else {
								$('#affiliate-help-holder').html('<div class="ui red message">Data not found!</div>');
							}
						}
					});
				},
				other: function() {

					sejoli.product.select2();

					$(document).on("click",'#affiliate-help-filter-button', function(e){
						e.preventDefault();
						if ( $('#product_id').val() !== '' ) {
							sejoli.affiliate.help.processForm();
						} else {
							$('#affiliate-help-holder').html('<div class="ui red message">Please select a product</div>');
						}
					});

					$(document).on('click', '.help-detail', function(e){

						var key = $(this).data('key');
						var product_id = $(this).data('product-id');

						$.ajax({
							url : sejoli_member_area.get_affiliate_help_detail,
							method: 'POST',
							dataType : 'json',
							data : {
								key : key,
								product_id: product_id,
								security: sejoli_member_area.ajax_nonce
							},
							beforeSend : function() {
								sejoli.block();
							},
							success : function(data) {
								sejoli.unblock();
								// console.log(data);
								if ( data.length !== 0 ) {
									var tmpl = $.templates('#tmpl-affiliate-help-detail');
									$('#affiliate-help-detail-holder').html(tmpl.render(data)).modal('show');
								} else {
									$('#affiliate-help-detail-holder').html('<div class="ui red message">Data not found!</div>').modal('show');
								}
							}
						});
					})
				}
			},
			coupons: {
				init: function(){
					sejoli.affiliate.coupons.renderTable();

					$(document).on('click','.show-add-coupon-form', function(){
						$('#add-coupon-form-wrap').modal('show');
					});

					$(document).on('click','.show-filter-form', function(){
						$('#filter-form-wrap').modal('show');
					});

					var coupon_parent = $('.coupon_parent_id').select2({
						width: '100%',
						allowClear: true,
						placeholder: sejoli_member_area.coupon_select,
						ajax: {
							method: 'POST',
							url: sejoli_member_area.ajaxurl,
							data: function( params ) {
								return {
									term: params.term,
									action: 'sejoli-list-coupons',
									nonce: sejoli_member_area.coupon.list.nonce,
								}
							},
							processResults: function( data, page ) {
								return data;
							}
						}
					});

					$(document).on('click','.filter-form',function(e){
						e.preventDefault();
						$('#filter-form-wrap').modal('hide');
						sejoli.affiliate.coupons.table.ajax.reload();
					});

					$(document).on('click','.add-coupon',function(e){
						e.preventDefault();

						$.ajax({
							type: "POST",
							url: sejoli_member_area.ajaxurl,
							data: {
								action: 'sejoli-create-coupon',
								coupon_parent_id: $('#coupon_parent_id2').val(),
								code: $('#coupon_code').val(),
								nonce: sejoli_member_area.coupon.add.nonce,
							},
							beforeSend: function() {
								sejoli.block("#add-coupon-form-wrap");
							},
							success: function( response ){
								// console.log(response);
								var messages = '';

								sejoli.unblock("#add-coupon-form-wrap");
								if ( response.success ) {
									$('#add-coupon-message')
									.removeClass('ui error message')
									.addClass('ui success message');
									messages = 'Coupon '+response.data.coupon.code+' added successfully';

									$('#add-coupon-form').trigger("reset");

									setTimeout(function(){
										$('#add-coupon-form-wrap').modal('hide');
										sejoli.affiliate.coupons.table.ajax.reload();
										$('#add-coupon-message').hide()
									}, 1000);

								} else {
									$('#add-coupon-message')
									.removeClass('ui success message')
									.addClass('ui error message');
									var messages_dt = response.data.messages;
									if ( $.isArray( messages_dt ) && messages_dt.length > 0 ) {
										messages_dt.forEach( function( currentValue, index, arr ) {
											messages += '<p>'+currentValue+'</p>';
										}, messages );
									}
									if ( messages === '' ) {
										messages = 'Tambah kupon error';
									}
								}

								$('#add-coupon-message').html(messages).show();
							},
						});
					});
				},
				renderTable: function(){
					var theTable = $('#sejoli-affiliate-coupons').DataTable({
						language: dataTableTranslation,
						processing: false,
						searching: false,
						serverSide: true,
                        info: false,
						ajax: {
							type: 'POST',
							url: sejoli_member_area.ajaxurl,
							data: function(data) {
								data.action = 'sejoli-coupon-table';
								data.filter = sejoli.filter('#filter-form');
								data.nonce = sejoli_member_area.coupon.table.nonce;
							}
						},
						pageLength : 50,
						lengthMenu : [
							[10, 50, 100, 200, -1],
							[10, 50, 100, 200, dataTableTranslation.all],
						],
						order: [
							[ 0, "desc" ]
						],
						columnDefs: [
							{
								targets: [1],
								orderable: false
							},{
								targets: 0,
								data : 'ID',
								render: function(data, type, full) {
									let tmpl = $.templates("#sejoli-edit-coupon-tmpl");
									return tmpl.render({
										id : full.ID,
										parent : full.parent_code,
										code : full.code,
										limit_date : sejoli.convertdate(full.limit.date),
										limit_use : (0 === parseInt(full.limit.use)) ? null : full.limit.use,
										free_shipping : full.free_shipping,
                            			renewal_coupon : full.renewal_coupon
									 });
								}
							},{
								targets: 1,
								width: '15%',
								data : 'discount',
								className : 'price'
							},{
								targets: 2,
								width: '15%',
								data : 'usage',
								className : 'price'

							},{
								targets: 3,
								data : 'status',
								width : '100px',
								render : function(data, type, full) {
									let tmpl = $.templates('#coupon-status');
									return tmpl.render({
										label : sejoli_member_area.text.status[data],
										color : sejoli_member_area.color[data]
									});
								}
							}
						]
					});

					theTable.on('preXhr',function(){
						sejoli.block('#sejoli-affiliate-coupons');
					});

					theTable.on('xhr',function(){
						sejoli.unblock('#sejoli-affiliate-coupons');
					});

					sejoli.affiliate.coupons.table = theTable;
				},
				table: null,
			},
			order: {
				init: function() {
					sejoli.affiliate.order.renderTable();
					sejoli.affiliate.order.detail();

					sejoli.daterangepicker("#date-range");

					$('#date-range').val('');

					$(document).on('click','.show-filter-form', function(){
						$('#filter-form-wrap').modal('show');
					});

					$(document).on('click','.filter-form',function(e){
						e.preventDefault();
						$('#filter-form-wrap').modal('hide');
						sejoli.affiliate.order.table.ajax.reload();
					});

                    $(document).on('click', '.export-csv', function(){
                        $.ajax({
                            url :  sejoli_member_area.affiliate.export_prepare.ajaxurl,
                            type : 'POST',
                            dataType: 'json',
                            data : {
                                action : 'sejoli-order-export-prepare',
                                nonce : sejoli_member_area.affiliate.export_prepare.nonce,
                                data : sejoli.filter('#filter-form'),
                            },
                            beforeSend : function() {
                                sejoli.block('#affiliate-orders');
                            },
                            success : function(response) {
                                sejoli.unblock('#affiliate-orders');
                                window.location.href = response.url.replace(/&amp;/g, '&');
                            }
                        });
                        return false;
                    })

					sejoli.product.select2();

					$(document).on('click','.aff-order-follow-up',function(e){
						e.preventDefault();

						var order_id = $(this).data('id');
						var link = $(this).val();

						if ( link !== '' ) {
							link = link.replace("order_id", order_id);
							window.open(link, '_blank');
						}
					});
				},
				renderTable: function() {
					sejoli.affiliate.order.table = $('#affiliate-orders').DataTable({
						"language"	: dataTableTranslation,
						'ajax'		: {
							'method': 'POST',
							'url'   : sejoli_member_area.ajaxurl,
							'data'  : function(data) {
								data.filter   = sejoli.filter('#filter-form');
				                data.action   = 'sejoli-affiliate-order-table'
								data.nonce 	  = sejoli_member_area.affiliate.order.nonce;
							}
						},
						// "bLengthChange": false,
						"bFilter": false,
						"serverSide": true,
						pageLength : 50,
						lengthMenu : [
							[10, 50, 100, 200, -1],
							[10, 50, 100, 200, dataTableTranslation.all],
						],
						order: [
							[ 0, "desc" ]
						],
						columnDefs: [
							{
								targets: [1, 2, 3],
								orderable: false
							},
							{
								targets: 0,
								data : 'ID',
								render : function( data, type, full) {
									let tmpl = $.templates('#order-detail'),
										subsctype = null,
										quantity = null;

									if(1 < parseInt(full.quantity)) {
										quantity = full.quantity;
									}

									var followup = '';

									if ( full.product.has_followup_content ) {
										followup = sejoli_get_nested_object(full,['meta_data','followup','affiliate']);
									}

									return tmpl.render({
										followup: followup,
										id : full.ID,
										product : full.product_name,
										coupon : full.coupon_code,
										parent : (full.order_parent_id > 0) ? full.order_parent_id : null,
										date : ("0000-00-00 00:00:00" !== full.updated_at) ? sejoli.convertdate(full.updated_at) : sejoli.convertdate(full.created_at),
										type : sejoli_member_area.subscription.type[full.type],
										quantity : quantity,
									})
								}
							},{
								targets: 1,
								width: '15%',
								data : 'user_name'
							},{
								targets: 2,
								width: '15%',
								data : 'grand_total',
								className : 'price',
								render : function(data, type, full) {
									return sejoli_member_area.text.currency + sejoli.formatPrice(data)
								}
							},{
								targets: 3,
								data : 'status',
								width: '100px',
								render : function( data, type, full ) {
									let tmpl = $.templates('#order-status');
									return tmpl.render({
										label : sejoli_member_area.order.status[full.status],
										color : sejoli_member_area.color[full.status]
									});
								}
							}
						]
					});

					sejoli.affiliate.order.table.on( 'preXhr.dt', function( e, settings, data ){
						sejoli.block('#affiliate-orders');
					});

					sejoli.affiliate.order.table.on( 'xhr.dt', function ( e, settings, json, xhr ) {
						sejoli.unblock('#affiliate-orders');
					});
				},
				detail: function() {

					$(document).on('click', '.order-detail-trigger', function(){
						let order_id = $(this).data('id');

						$.ajax({
							url  : sejoli_member_area.order.detail.ajaxurl,
							type : 'GET',
							data : {
								order_id : order_id,
								nonce    : sejoli_member_area.order.detail.nonce
							},
							beforeSend: function() {
								sejoli.block('#affiliate-orders');
							},
							success : function(response) {
								sejoli.unblock('#affiliate-orders');
								let tmpl = $.templates('#order-modal-content');
								let affiliate_name  = null,
									affiliate_phone = null,
									affiliate_email = null;
								let buyer_phone = null,
									buyer_email = null;

								if(0 != response.affiliate_id && response.affiliate !== null) {
									affiliate_name  = response.affiliate.data.display_name;
									affiliate_phone = response.affiliate.data.meta.phone;
									affiliate_email = response.affiliate.data.user_email;
								}

								if(response.user !== null) {
									buyer_phone = response.user.data.meta.phone;
									buyer_email = response.user.data.user_email;
								}

								let subscription = sejoli_member_area.subscription.type[response.type];
								let variants     = null;
								let variant_tmpl = $.templates('#order-variant-data');

								// // console.log(typeof response.meta_data.variants);
								if( typeof response.meta_data.variants !== 'undefined' ) {
									variants = variant_tmpl.render( response.meta_data.variants )
								}

								let content = tmpl.render({
									id              : order_id,
									date            : sejoli.convertdate( response.created_at ),
									buyer_name      : response.user.data.display_name,
									buyer_email     : buyer_email,
									buyer_phone     : buyer_phone,
									variants        : variants,
									product_name    : response.product.post_title,
									quantity        : response.quantity,
									total           : sejoli_member_area.text.currency + sejoli.formatPrice( response.grand_total ),
									status          : sejoli_member_area.order.status[response.status],
									color           : sejoli_member_area.color[response.status],
									subscription    : ( null != subscription ) ? subscription.toUpperCase() : null,
									courier         : response.courier,
									address         : response.address,
									parent_order    : (response.order_parent_id > 0) ? response.order_parent_id : null,
									affiliate_name  : affiliate_name,
									affiliate_phone : affiliate_phone,
									affiliate_email : affiliate_email,
									shipping_data   : ( null != response.meta_data.shipping_data ) ? response.meta_data.shipping_data : null,
									markup_price    : sejoli_member_area.text.currency + sejoli.formatPrice( response.meta_data.markup_price ),
			                        ppn   			: ( null != response.meta_data.ppn ) ? (Math.round(response.meta_data.ppn * 100) / 100).toFixed(2) : null,
									ppn_total       : ( null != response.meta_data.ppn_total ) ? response.meta_data.ppn_total : null,
									unique_code     : ( null != response.meta_data.unique_code ) ? response.meta_data.unique_code : null,
			                        payment_gateway : response.payment_gateway
								});

								$('.order-modal-holder').html(content).modal('show');
							}
						})
					});
				}
			},
			bonus_editor: {
				table: {},
				init: function() {
					sejoli.affiliate.bonus_editor.dataTable();
					sejoli.affiliate.bonus_editor.popup();
				},
				dataTable: function() {

					var table = $('#affiliasi-bonus-editor').DataTable({
						responsive: true,
						"language"	: dataTableTranslation,
						'ajax'		: {
							'method': 'POST',
							'url'   : sejoli_member_area.ajaxurl,
							'data'  : function(data) {
								data.action = 'sejoli-product-table'
								data.nonce = sejoli_member_area.product.table.nonce;
							}
						},
						// "bLengthChange": false,
						"bFilter": false,
						"serverSide": true,
						pageLength : 50,
						lengthMenu : [
							[10, 50, 100, 200, -1],
							[10, 50, 100, 200, dataTableTranslation.all],
						],
						order: [
							[ 0, "desc" ]
						],
						columnDefs: [
							{
								targets: [0, 1],
								orderable: false
							},
							{
								targets: 0,
								width: '50%',
								data: 'title',
								render : function(data, type, full) {
									return data;
								}
							},
							{
								targets: 1,
								width: '50%',
								data : 'id',
								render : function(data, type, full) {
									var tmpl = $.templates('#button-template');
									return tmpl.render(full);
								}
							}
						]
					});

					table.on( 'preXhr.dt', function( e, settings, data ){
						sejoli.block('#affiliasi-bonus-editor');
					});

					table.on( 'xhr.dt', function ( e, settings, json, xhr ) {
						sejoli.unblock('#affiliasi-bonus-editor');
					});

					sejoli.affiliate.bonus_editor.table = table;

				},
				popup: function() {

					tinymce.init({
						selector: 'textarea#bonus',
						height: 200,
						menubar: false,
						plugins: [
						  'advlist autolink lists link image charmap print preview anchor',
						  'searchreplace visualblocks code fullscreen',
						  'insertdatetime media table paste code help wordcount'
						],
						toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
						content_css: [
						  '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
						  '//www.tiny.cloud/css/codepen.min.css'
						]
					});

					$("#bonus-editor-modal").modal({
						closable: false,
						detachable: false,
					});

					$(document).on('click','.edit-bonus-toggle',function(e){

						$('#bonus-editor-form').trigger('reset');
						$('.alert-holder').html('');

						e.preventDefault();

						$("#bonus-editor-modal").modal("show");

						var product_id = $(this).data('id');
						var product_title = $(this).data('title');
						$('#product_id').val(product_id);
						$('#product_title').html(product_title);

						// ajax get bonus
						$.ajax({
							url : sejoli_member_area.affiliate.bonus_editor.get.ajaxurl,
							type : 'GET',
							data : {
								product_id : product_id,
								nonce : sejoli_member_area.affiliate.bonus_editor.get.nonce
							},
							beforeSend: function() {
								sejoli.block('#bonus-editor-modal');
							},
							success : function(response) {
								sejoli.unblock('#bonus-editor-modal');
								// console.log(response);

								if ( typeof response.content !== 'undefined' ) {

									$(tinymce.get('bonus').getBody()).html(response.content);

								}

							}
						});

					});

					$(document).on('submit','#bonus-editor-form',function(e){

						e.preventDefault();

						var content = $('#bonus').val();
						var product_id = $('#product_id').val();

						// ajax save bonus
						$.ajax({
							url : sejoli_member_area.ajaxurl,
							type : 'POST',
							data : {
								content: content,
								product_id : product_id,
								action: 'sejoli-affiliate-update-bonus-content',
								nonce : sejoli_member_area.affiliate.bonus_editor.update.nonce
							},
							beforeSend: function() {
								sejoli.block('#bonus-editor-modal');
							},
							success : function(response) {
								sejoli.unblock('#bonus-editor-modal');
								// console.log(response);

								var data = {};

								if ( response.value ) {
									data.type = 'success';
								} else {
									data.type = 'error';
								}

								data.messages = response.messages;

								var template = $.templates("#alert-template");
								var htmlOutput = template.render(data);
								$(".alert-holder").html(htmlOutput);

							}
						});

					});

				}
			},
			facebook_pixel: {
				table: {},
				init: function() {
					sejoli.affiliate.facebook_pixel.dataTable();
					sejoli.affiliate.facebook_pixel.popup();
				},
				dataTable: function() {

					var table = $('#affiliasi-facebook-pixel').DataTable({
						responsive: true,
						"language"	: dataTableTranslation,
						'ajax'		: {
							'method': 'POST',
							'url'   : sejoli_member_area.ajaxurl,
							'data'  : function(data) {
								data.action = 'sejoli-product-table'
								data.nonce = sejoli_member_area.product.table.nonce;
							}
						},
						// "bLengthChange": false,
						"bFilter": false,
						"serverSide": true,
						pageLength : 50,
						lengthMenu : [
							[10, 50, 100, 200, -1],
							[10, 50, 100, 200, dataTableTranslation.all],
						],
						order: [
							[ 0, "desc" ]
						],
						columnDefs: [
							{
								targets: [0, 1],
								orderable: false
							},
							{
								targets: 0,
								width: '50%',
								data: 'title',
								render : function(data, type, full) {
									return data;
								}
							},
							{
								targets: 1,
								width: '50%',
								data : 'id',
								render : function(data, type, full) {
									var tmpl = $.templates('#button-template');
									return tmpl.render(full);
								}
							}
						]
					});

					table.on( 'preXhr.dt', function( e, settings, data ){
						sejoli.block('#affiliasi-facebook-pixel');
					});

					table.on( 'xhr.dt', function ( e, settings, json, xhr ) {
						sejoli.unblock('#affiliasi-facebook-pixel');
					});

					sejoli.affiliate.facebook_pixel.table = table;

				},
				popup: function() {

					$(document).on('click','.edit-bonus-toggle',function(e){

						$('#facebook-pixel-form').trigger('reset');
						$('.alert-holder').html('');

						e.preventDefault();

						$("#facebook-pixel-modal").modal("show");

						var product_id = $(this).data('id');
						var product_title = $(this).data('title');
						$('#product_id').val(product_id);
						$('#product_title').html(product_title);

						// ajax get bonus
						$.ajax({
							url : sejoli_member_area.affiliate.facebook_pixel.get.ajaxurl,
							type : 'GET',
							data : {
								product_id : product_id,
								nonce : sejoli_member_area.affiliate.facebook_pixel.get.nonce
							},
							beforeSend: function() {
								sejoli.block('#facebook-pixel-modal');
							},
							success : function(response) {
								sejoli.unblock('#facebook-pixel-modal');

								if ( typeof response.id_pixel !== 'undefined' ) {

									$('#id_pixel').val(response.id_pixel)

									var template = $.templates("#fb-pixel-links-template");
									var htmlOutput = template.render({links:response.links});
									$(".fb-pixel-links-holder").html(htmlOutput);

								}

							}
						});

					});

					$(document).on('submit','#facebook-pixel-form',function(e){

						e.preventDefault();

						var id_pixel = $('#id_pixel').val();
						var product_id = $('#product_id').val();

						// ajax update
						$.ajax({
							url : sejoli_member_area.ajaxurl,
							type : 'POST',
							data : {
								id_pixel: id_pixel,
								product_id : product_id,
								action: 'sejoli-affiliate-update-facebook-pixel',
								nonce : sejoli_member_area.affiliate.facebook_pixel.update.nonce
							},
							beforeSend: function() {
								sejoli.block('#facebook-pixel-modal');
							},
							success : function(response) {
								sejoli.unblock('#facebook-pixel-modal');

								var data = {};

								if ( response.value ) {
									data.type = 'success';
								} else {
									data.type = 'error';
								}

								data.messages = response.messages;

								var template = $.templates("#alert-template");
								var htmlOutput = template.render(data);
								$(".alert-holder").html(htmlOutput);

							}
						});

					});

				}
			}
		},
		order: {
			table: {},
			init: function() {
				sejoli.order.renderTable();
				sejoli.order.detail();

				sejoli.daterangepicker("#date-range");

			    $('#date-range').val('');

				$(document).on('click','.show-filter-form', function(){
					$('#filter-form-wrap').modal('show');
				});

				$(document).on('click','.filter-form',function(e){
					e.preventDefault();
					$('#filter-form-wrap').modal('hide');
					sejoli.order.table.ajax.reload();
				});

				sejoli.product.select2();
			},
			renderTable: function() {
				sejoli.order.table = $('#orders').DataTable({
					"language"	: dataTableTranslation,
					'ajax'		: {
						'method': 'POST',
						'url'   : sejoli_member_area.ajaxurl,
						'data'  : function(data) {
							data.filter   = sejoli.filter('#filter-form');
							data.action   = 'sejoli-order-table'
							data.nonce 	  = sejoli_member_area.order.table.nonce;
						}
					},
					// "bLengthChange": false,
					"bFilter": false,
					"serverSide": true,
					pageLength : 50,
					lengthMenu : [
						[10, 50, 100, 200, -1],
						[10, 50, 100, 200, dataTableTranslation.all],
					],
					order: [
						[ 0, "desc" ]
					],
					columnDefs: [
						{
							targets: [1, 2, 3],
							orderable: false
						},
						{
							targets: 0,
							data : 'ID',
							render : function( data, type, full) {
								let tmpl = $.templates('#order-detail'),
									subsctype = null,
									quantity = null;

								if(1 < parseInt(full.quantity)) {
									quantity = full.quantity;
								}

								return tmpl.render({
									id : full.ID,
									product : full.product_name,
									coupon : full.coupon_code,
									parent : (full.order_parent_id > 0) ? full.order_parent_id : null,
									date : ("0000-00-00 00:00:00" !== full.updated_at) ? sejoli.convertdate(full.updated_at) : sejoli.convertdate(full.created_at),
									type : sejoli_member_area.subscription.type[full.type],
									quantity : quantity,
								})
							}
						},{
							targets: 1,
							width: '15%',
							data : 'affiliate_name'
						},{
							targets: 2,
							width: '15%',
							data : 'grand_total',
							className : 'price',
							render : function(data, type, full) {
								return sejoli_member_area.text.currency + sejoli.formatPrice(data)
							}
						},{
							targets : 3,
							data : 'status',
							width: '100px',
							render : function( data, type, full ) {
								let tmpl = $.templates('#order-status');
								return tmpl.render({
									label : sejoli_member_area.order.status[full.status],
									color : sejoli_member_area.color[full.status]
								});
							}
						}
					]
				});

				sejoli.order.table.on( 'preXhr.dt', function( e, settings, data ){
					sejoli.block('#orders');
				});

				sejoli.order.table.on( 'xhr.dt', function ( e, settings, json, xhr ) {
					sejoli.unblock('#orders');
				});
			},
			detail: function() {

				$(document).on('click', '.order-detail-trigger', function(){
					let order_id = $(this).data('id');

					$.ajax({
						url  : sejoli_member_area.order.detail.ajaxurl,
						type : 'GET',
						data : {
							order_id : order_id,
							nonce    : sejoli_member_area.order.detail.nonce
						},
						beforeSend: function() {
							sejoli.block('#orders');
						},
						success : function(response) {
							console.log("Public Order");

							sejoli.unblock('#orders');
							let tmpl            = $.templates('#order-modal-content');
							let affiliate_name  = null,
								affiliate_phone = null,
								affiliate_email = null;
							let buyer_phone = null,
								buyer_email = null;

							if(0 != response.affiliate_id && response.affiliate !== null) {
								affiliate_name  = response.affiliate.data.display_name;
								affiliate_phone = response.affiliate.data.meta.phone;
								affiliate_email = response.affiliate.data.user_email;
							}

							if(response.user !== null) {
								buyer_phone = response.user.data.meta.phone;
								buyer_email = response.user.data.user_email;
							}

							let subscription = sejoli_member_area.subscription.type[response.type];
							let variants     = null;
							let variant_tmpl = $.templates('#order-variant-data');

							if( typeof response.meta_data.variants !== 'undefined' ) {
								variants = variant_tmpl.render( response.meta_data.variants )
							}

							let content = tmpl.render({
								id              : order_id,
								date            : sejoli.convertdate( response.created_at ),
								buyer_name      : response.user.data.display_name,
								buyer_email     : buyer_email,
								buyer_phone     : buyer_phone,
								variants        : variants,
								product_name    : response.product.post_title,
								quantity        : response.quantity,
								total           : sejoli_member_area.text.currency + sejoli.formatPrice( response.grand_total ),
								status          : sejoli_member_area.order.status[response.status],
								color           : sejoli_member_area.color[response.status],
								subscription    : ( null != subscription ) ? subscription.toUpperCase() : null,
								courier         : response.courier,
								address         : response.address,
								parent_order    : (response.order_parent_id > 0) ? response.order_parent_id : null,
								affiliate_name  : affiliate_name,
								affiliate_phone : affiliate_phone,
								affiliate_email : affiliate_email,
								markup_price    : sejoli_member_area.text.currency + sejoli.formatPrice( response.meta_data.markup_price ),
								ppn   			: ( null != response.meta_data.ppn ) ? (Math.round(response.meta_data.ppn * 100) / 100).toFixed(2) : null,
								ppn_total       : ( null != response.meta_data.ppn_total ) ? response.meta_data.ppn_total : null,
								unique_code     : ( null != response.meta_data.unique_code ) ? response.meta_data.unique_code : null,
								shipping_data   : ( null != response.meta_data.shipping_data ) ? response.meta_data.shipping_data : null,
								confirm_date    : sejoli.convertdate(response.confirm_date),
                        		confirm_detail  : response.confirm_detail,
                        		payment_gateway : response.payment_gateway.toUpperCase(),
		                        payment_channel : (null != response.payment_data) ? response.payment_data.payment_channel : null,
		                        payment_fee     : (null != response.payment_data) ? sejoli_member_area.text.currency + sejoli.formatPrice(response.payment_data.payment_fee) : null
							});

							$('.order-modal-holder').html(content).modal('show');
						}
					})
				});
			}
		},
        subscription: {
			table: {},
			init: function() {
				sejoli.subscription.renderTable();
				sejoli.subscription.detail();

				sejoli.daterangepicker("#date-range");

				$('#date-range').val('');

				$(document).on('click','.show-filter-form', function(){
					$('#filter-form-wrap').modal('show');
				});

				$(document).on('click','.filter-form',function(e){
					e.preventDefault();
					$('#filter-form-wrap').modal('hide');
					sejoli.subscription.table.ajax.reload();
				});

				sejoli.product.select2();
			},
			renderTable: function() {
				sejoli.subscription.table = $('#orders').DataTable({
					"language"	: dataTableTranslation,
					'ajax'		: {
						'method': 'POST',
						'url'   : sejoli_member_area.ajaxurl,
						'data'  : function(data) {
							data.filter   = sejoli.filter('#filter-form');
							data.action   = 'sejoli-subscription-table'
							data.nonce 	  = sejoli_member_area.order.table.nonce;
						}
					},
					// "bLengthChange": false,
					"bFilter": false,
					"serverSide": true,
					pageLength : 50,
					lengthMenu : [
						[10, 50, 100, 200, -1],
						[10, 50, 100, 200, dataTableTranslation.all],
					],
					order: [
						[ 0, "desc" ]
					],
                    createdRow : function(row, data, index) {
                        if(true == data.expired) {
                            $(row).addClass('expired');
                        }
                    },
					columnDefs: [
						{
							targets: [0, 1, 2, 3],
							orderable: false
						},
						{
							targets: 0,
							data : 'ID',
							render : function( data, type, full) {
								let tmpl = $.templates('#order-detail'),
									subsctype = null,
									quantity = null;

								if(1 < parseInt(full.quantity)) {
									quantity = full.quantity;
								}

								return tmpl.render({
									id : full.order_id,
									product : full.product_name,
									coupon : full.coupon_code,
                                    order_id: full.order_id,
									parent : (full.order_parent_id > 0) ? full.order_parent_id : null,
									date : ("0000-00-00 00:00:00" !== full.updated_at) ? sejoli.convertdate(full.updated_at) : sejoli.convertdate(full.created_at),
									type : sejoli_member_area.subscription.type[full.type],
									quantity : quantity,
                                    status: full.status,
                                    renewal: full.renewal,
                                    expired: full.expired,
                                    link: full.link
								})
							}
						},{
							targets: 1,
							width: '15%',
							render: function(data, type, full) {
                                return sejoli.convertdate(full.end_date);
                            }
						},{
							targets: 2,
							width: '15%',
							render : function(data, type, full) {
                           
                                if(false == full.expired) {
                                    return full.day_left + ' hari';
                                } else {
                                    return '-';
                                }
							}
						},{
							targets: 3,
							data : 'status',
							width: '100px',
							render : function( data, type, full ) {
								let tmpl = $.templates('#order-status');
								return tmpl.render({
									label : sejoli_member_area.text.status[full.status],
									color : sejoli_member_area.color[full.status]
								});
							}
						}
					]
				});

				sejoli.subscription.table.on( 'preXhr.dt', function( e, settings, data ){
					sejoli.block('#orders');
				});

				sejoli.subscription.table.on( 'xhr.dt', function ( e, settings, json, xhr ) {
					sejoli.unblock('#orders');
				});
			},
			detail: function() {

				$(document).on('click', '.order-detail-trigger', function(){
					let order_id = $(this).data('id');

					$.ajax({
						url  : sejoli_member_area.order.detail.ajaxurl,
						type : 'GET',
						data : {
							order_id : order_id,
							nonce    : sejoli_member_area.order.detail.nonce
						},
						beforeSend: function() {
							sejoli.block('#orders');
						},
						success : function(response) {
							sejoli.unblock('#orders');
							let tmpl = $.templates('#order-modal-content');
							let affiliate_name  = null,
								affiliate_phone = null,
								affiliate_email = null;
							let buyer_phone = null,
								buyer_email = null;

							if(0 != response.affiliate_id && response.affiliate !== null) {
								affiliate_name  = response.affiliate.data.display_name;
								affiliate_phone = response.affiliate.data.meta.phone;
								affiliate_email = response.affiliate.data.user_email;
							}

							if(response.user !== null) {
								buyer_phone = response.user.data.meta.phone;
								buyer_email = response.user.data.user_email;
							}

							let subscription = sejoli_member_area.subscription.type[response.type];
							let variants     = null;
							let variant_tmpl = $.templates('#order-variant-data');

							if( typeof response.meta_data.variants !== 'undefined' ) {
								variants = variant_tmpl.render( response.meta_data.variants )
							}

							let content = tmpl.render({
								id              : order_id,
								date            : sejoli.convertdate( response.created_at ),
								buyer_name      : response.user.data.display_name,
								buyer_email     : buyer_email,
								buyer_phone     : buyer_phone,
								variants        : variants,
								product_name    : response.product.post_title,
								quantity        : response.quantity,
								total           : sejoli_member_area.text.currency + sejoli.formatPrice( response.grand_total ),
								status          : sejoli_member_area.order.status[response.status],
								color           : sejoli_member_area.color[response.status],
								subscription    : ( null != subscription ) ? subscription.toUpperCase() : null,
								courier         : response.courier,
								address         : response.address,
								parent_order    : (response.order_parent_id > 0) ? response.order_parent_id : null,
								affiliate_name  : affiliate_name,
								affiliate_phone : affiliate_phone,
								affiliate_email : affiliate_email,
								markup_price    : sejoli_member_area.text.currency + sejoli.formatPrice( response.meta_data.markup_price ),
								ppn   			: ( null != response.meta_data.ppn ) ? (Math.round(response.meta_data.ppn * 100) / 100).toFixed(2) : null,
								ppn_total       : ( null != response.meta_data.ppn_total ) ? response.meta_data.ppn_total : null,
								unique_code     : ( null != response.meta_data.unique_code ) ? response.meta_data.unique_code : null,
								payment_gateway : response.payment_gateway
							});

							$('.order-modal-holder').html(content).modal('show');
						}
					})
				});
			}
		},
		profile: {
			init: function() {
				sejoli.profile.submit();
			},
			submit: function() {

				$('#kecamatan').select2({
					allowClear: true,
					placeholder: sejoli_member_area.district_select,
					minimumInputLength: 1,
					ajax: {
						url: sejoli_member_area.ajax_url,
						type: 'post',
						// dataType: 'json',
						data: function (params) {
							return {
								term: params.term,
								sejoli_ajax_nonce: sejoli_member_area.get_subdistrict,
							};
						},
						processResults: function (data, params) {
							return {
								results: data.results,
							};
						},
					}
				});

				$(document).on('submit','#profile',function(e){

					e.preventDefault();

					var data = $(this).serialize();
					data += '&kecamatan_name=' + encodeURIComponent($('#kecamatan').find(":selected").text());

					$.ajax({
						url : sejoli_member_area.update_profile,
						type: 'post',
						data : data,
						beforeSend : function() {
							sejoli.block();
						},
						success : function( response ) {
							sejoli.unblock();
							// console.log(response);

							data = {};

							if ( response.valid ) {
								data.type = 'success';

								setTimeout(function(){
									location.reload(true);
								}, 2000);

							} else {
								data.type = 'error';
							}

							data.messages = response.messages;

							var template = $.templates("#alert-template");
							var htmlOutput = template.render(data);
							$(".profile-alert-holder").html(htmlOutput);

						}
					});

				})

			}
		},
		table : {
			wallet: {

			},
			affiliate : {
				commission : {

				}
			}
		},
		product: {
			select2:function() {
                let options;

                $.ajax({
                    url : sejoli_member_area.ajaxurl,
                    data : {
                        action : 'sejoli-product-options',
                        nonce : sejoli_member_area.product.select.nonce
                    },
                    type : 'GET',
                    dataType : 'json',
                    beforeSend : function() {

                    },
                    success : function(response) {

                        $('#product_id').select2({
                            allowClear: true,
                            placeholder: sejoli_member_area.product.placeholder,
                            width:'100%',
                            data : response.results,
                            templateResult : function(data) {
                                return $("<textarea/>").html(data.text).text();
                            },
                            templateSelection : function(data) {
                                return $("<textarea/>").html(data.text).text();
                            }
                        });
                    }
                });



			}
		},
		product_affiliate: {
			select2:function() {
                let options;

                $.ajax({
                    url : sejoli_member_area.ajaxurl,
                    data : {
                        action : 'sejoli-product-affiliate-options',
                        nonce : sejoli_member_area.product.select.nonce
                    },
                    type : 'GET',
                    dataType : 'json',
                    beforeSend : function() {

                    },
                    success : function(response) {

                        $('#product_id').select2({
                            allowClear: true,
                            placeholder: sejoli_member_area.product.placeholder,
                            width:'100%',
                            data : response.results,
                            templateResult : function(data) {
                                return $("<textarea/>").html(data.text).text();
                            },
                            templateSelection : function(data) {
                                return $("<textarea/>").html(data.text).text();
                            }
                        });
                    }
                });



			}
		},
		akses: {
			init: function(){
				sejoli.akses.renderData();
			},
			renderData: function(){

				$.ajax({
					url: sejoli_member_area.akses.ajaxurl,
					type: 'get',
					data: {
						nonce: sejoli_member_area.akses.nonce
					},
					beforeSend : function() {
						sejoli.block();
					},
					success : function(data) {
						sejoli.unblock();
						// console.log(data);

						var template = $.templates("#item-template");
						var htmlOutput = template.render({'products':data});
						$(".item-holder").html(htmlOutput);

					}
				});

			}
		},
		license: {
			table: {},
			init: function() {

				sejoli.license.dataTable();

			},
			dataTable: function() {

				sejoli.license.table = $('#sejoli-license').DataTable({
					language: dataTableTranslation,
					searching: false,
					processing: false,
					serverSide: true,
					ajax: {
						type: 'POST',
						url: sejoli_member_area.ajaxurl,
						data: function(data) {
							data.filter = sejoli.filter('#license-filter');
							data.action   = 'sejoli-license-table';
							data.security = sejoli_member_area.ajax_nonce;
							data.backend  = true;
						}
					},
					pageLength : 50,
					lengthMenu : [
						[10, 50, 100, 200, -1],
						[10, 50, 100, 200, dataTableTranslation.all],
					],
					order: [
						[ 0, "desc" ]
					],
					columnDefs: [
						{
							targets: [2, 3],
							orderable: false
						},{
							targets: 0,
							data : 'ID',
							render: function(data, type, full) {

								let tmpl = {
									edit : $.templates("#sejoli-edit-license-tmpl")
								}
								return tmpl.edit.render({
									id : full.ID,
									code : full.code,
									order : full.order_id,
									product : full.product_name,
								});

							}
						},{
							targets: 1,
							width: '20%',
							data: 'owner_name'
						},{
							targets: 2,
							width: '20%',
							data : 'string',
						},{
							targets: 3,
							data : 'subscription_status',
							width : '100px',
							render : function(data, type, full) {
								if(data == 'expired'){
									data = 'inactive';
								}
								let tmpl = $.templates('#license-status');
								return tmpl.render({
									label : sejoli_member_area.text.status[data],
									color : sejoli_member_area.color[data]
								});
							}
						}
					]
				});

				sejoli.license.table.on('preXhr',function(){
					console.log('load');
					sejoli.block('#sejoli-license');
				});

				sejoli.license.table.on('xhr',function(){
					console.log('loaded');
					sejoli.unblock('#sejoli-license');
				});

				$(document).on('click','.show-filter-form', function(){
					$('#filter-form-wrap').modal('show');
				});

				$(document).on('click','.filter-form',function(e){
					e.preventDefault();
					$('#filter-form-wrap').modal('hide');
					sejoli.license.table.ajax.reload();
				});

				sejoli.product.select2();

			},
		}
    }

	$(document).ready(function(){

        if (typeof ClipboardJS === 'function') {

    		var clipboard = new ClipboardJS('.copy-btn' );
    		clipboard.on('success', function(e) {
    			if ( e.text !== '' ) {
    				$(e.trigger)
    				.attr('title', 'Copied!')
    				.popup('fixTitle')
    				.popup({
    					onCreate: function() {
    					$(e.trigger)
    						.popup({
    							variation: 'inverted',
    							position: 'right center',
    						})
    							.popup('toggle')
    					;
    					}
    				})
    				.popup('show')
    				.popup('toggle')
    			}
    		})
    		clipboard.on('error', function(e) {
    			if ( e.text !== '' ) {
    				$(e.trigger)
    				.attr('title', 'Press Ctrl+C to copy')
    				.popup('fixTitle')
    				.popup({
    					onCreate: function() {
    					$(e.trigger)
    						.popup({
    							variation: 'inverted',
    							position: 'right center',
    						}).popup('toggle')
    					;
    					}
    				})
    				.popup('show')
    				.popup('toggle')
    			}
    		});
        }

        if($().select2) {
    		$('.select2-filled').select2({
    			width:'100%',
    		});
        }

	});

})( jQuery );
