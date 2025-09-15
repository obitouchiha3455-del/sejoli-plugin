let sejoli;

(function( $ ) {

	'use strict';

	$.fn.serializeControls = function() {
		var data = {};

		function buildInputObject(arr, val) {
	    	if (arr.length < 1)
	      		return val;

			var objkey = arr[0];

			if (objkey.slice(-1) == "]") {
	      		objkey = objkey.slice(0,-1);
	    	}

			var result = {};

			if (arr.length == 1){
	      		result[objkey] = val;
	    	} else {
	      		arr.shift();
	      		var nestedVal = buildInputObject(arr,val);
	      		result[objkey] = nestedVal;
	    	}
	    	return result;
	  	}

	  	$.each(this.serializeArray(), function() {
	    	var val = this.value;
	    	var c = this.name.split("[");
	    	var a = buildInputObject(c, val);
	    		$.extend(true, data, a);
	  	});

		return data;
  	}

	sejoli = {
		var : {
			search : [],
			hari : ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
			bulan : ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
		},
		helper : {
			blockUI: function( selector = "" ) {
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
			unblockUI: function( selector = "" ) {
				if ( selector ) {
					$( selector ).unblock();
				} else {
					$.unblockUI();
				}
			},
			popupImage: function( title = "", image_url ) {
				var template = $.templates("#popup-image-tmpl");
				var htmlOutput = template.render({
					'image_url':image_url
				});
				$("#popup-image-wrap .header").html(title);
				$("#popup-image-wrap .content").html(htmlOutput);
				$('#popup-image-wrap').modal('show');
			},
			getFormData: function( selector = '.sejoli-form' ){
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
			select_2 : function(element, url, placeholder) {
				if($().select2) {
					$(element).select2({
						allowClear : true,
					   	placeholder: placeholder,
					   	minimumInputLength: 3,
					   	ajax : {
							url : url
					    },
					    width : '100%'
				   });
				}
			},
			daterangepicker: function(element) {
				if($().daterangepicker) {
					var start = moment().subtract(sejoli_admin.chart_daterange.interval, 'days');
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
						  	'Last 15 Days'  : [moment().subtract(14, 'days'), moment()],
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
		   filterData: function( element = '' ) {

			   sejoli.var.search = [];

			   element = ( '' == element ) ? '.sejoli-form-filter-holder' : element;

			   $(element).find('.filter').each(function(i, el){
				   let val = $(el).val();
				   let name = $(el).attr('name');

				   sejoli.var.search.push({
					   'val' : $(el).val(),
					   'name' : $(el).attr('name')
				   })
			   });
		   },
		   clearFilter: function() {
			   $('.sejoli-form-filter-holder').find('.filter').each(function(i, el){
				   console.log(el);
				   $(el).val('');
			   });
		   },
		   chartJS : function(element, datasets, labels) {
				var ctx = document.getElementById(element).getContext('2d');
				var myChart = new Chart(ctx, {
					type: 'line',
					data: {
						labels: labels,
						datasets: datasets
					},
					options: {
						maintainAspectRatio: false,
						aspectRatio: 2,
						responsive: true,
				        scales: {
				            yAxes: [{
				                ticks: {
				                    beginAtZero: true,
				                    stacked: true,
									callback: function(value) {
										// console.log(value);
										// return value;
										return sejoli.helper.formatPrice(value)
									}
				                }
				            }]
				        },
						tooltips: {
							callbacks: {
								label: function(tooltipItem, chart) {
									var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
				                    return datasetLabel + ': ' + sejoli.helper.formatPrice(tooltipItem.yLabel, 2);
								}
							}
						}
				    }
				});
		   }
		}
	}

	$(document).ready(function(){
		if($().select2) {
			let shipment_origin = sejoli.helper.select_2(
					"select[name='carbon_fields_compact_input[_shipment_origin]']",
					sejoli_admin.get_subdistricts.ajaxurl,
					sejoli_admin.get_subdistricts.placeholder
				)

			let shipment_cod_origin = sejoli.helper.select_2(
					"select[name='carbon_fields_compact_input[_shipment_cod_jne_origin]']",
					sejoli_admin.get_subdistricts.ajaxurl,
					sejoli_admin.get_subdistricts.placeholder
				)

			let select = $('#shipment_origin_select');

			select.select2({
			    ajax: {
			        url: sejoli_admin.get_subdistricts.ajaxurl,
			        dataType: 'json',
			        delay: 250,
			        data: function (params) {
			            return { term: params.term };
			        },
			        processResults: function (data) {
			            return { results: data };
			        }
			    }
			});

			// Sync ke hidden input Carbon Fields
			select.on('select2:select', function (e) {
			    const id = e.params.data.id;
			    const district = e.params.data.district;
			    $('input[name="carbon_fields_compact_input[_shipment_origin]"]').val(id);
			    $('input[name="carbon_fields_compact_input[_shipment_origin_name]"]').val(district);
			});

			// Inject value lama (jika ada)
			const savedId = $('input[name="carbon_fields_compact_input[_shipment_origin]"]').val();
			const savedDistrict = $('input[name="carbon_fields_compact_input[_shipment_origin_name]"]').val();

			if (savedId && savedDistrict) {
			    $.ajax({
			        url: sejoli_admin.get_subdistricts.ajaxurl,
			        data: { term: savedDistrict },
			        success: function (data) {
			            const items = data.results || data;
			            const found = items.find(item => item.id == savedId);
			            console.log(savedDistrict);
			            if (found) {
			                const option = new Option(found.text, found.id, true, true);
			            	// alert("OKss");
			            	// console.log(option);
			                select.append(option).trigger('change');
			            }
			        }
			    });
			}

		}

		sejoli.helper.select_2(
            "select[name='affiliate_id']",
            sejoli_admin.user.select.ajaxurl,
            sejoli_admin.affiliate.placeholder
        );

		$('#toplevel_page_crb_carbon_fields_container_sejoli').find('.wp-submenu .wp-first-item a').html(sejoli_admin.text.main);
		$('#toplevel_page_crb_carbon_fields_container_sejoli').find('.wp-submenu li:nth-child(3) a').html(sejoli_admin.text.notification);

		$('body').on('click', '.sejoli-check-autoresponder', function(){
		    let autoresponder_html = $("textarea[name='carbon_fields_compact_input[_autoresponder_html_code]']").val();

			$.ajax({
				type : 'POST',
				url : sejoli_admin.product.autoresponder.ajaxurl,
				dataType: 'json',
				data : {
					nonce : sejoli_admin.product.autoresponder.nonce,
					form : autoresponder_html
				},
				beforeSend : function() {
					console.log(sejoli_admin.product.autoresponder_check);
					$('#product-autoresponder-check').html(sejoli_admin.product.autoresponder_check);
				},
				success : function(response) {

					let content = '';

					console.log(response);

					if(response.valid) {

						content = '<span style="color:green;">HTML CODE Valid! <br /><br />';
						content += 'Form attributes : <br />';

						$.each(response.form, function(name, value){
							content += name + ': ' + value[0] + '<br />';
						});

						content += '<br /><br />Fields : <br />';

						$.each(response.fields, function(index, field){
							content += 'Field name : ' + field.name + ', type : ' + field.type + ', value : [' + field.value + ']<br />';
						});

						content += '</span>';

					} else {

						content = '<span style="color:red;">HTML CODE Error<br /><br />';
						content += response.messages.join("\r\n");
						content += '</span>';

					}

					$('#product-autoresponder-check').html(content);
				}

			})
		});
	});

})( jQuery );
