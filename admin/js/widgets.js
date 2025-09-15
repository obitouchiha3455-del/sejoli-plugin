(function( $ ) {
	'use strict';

    let sejoliWidgets = {
        helper : {
            beforeLoad : function(element){
                $(element).find('.ui.list').html(sejoli_widgets.text.loading)
            },
            appendData : function(element, data){
                let template = $.templates('#sejoli-widget-item');
                $(element).find('.ui.list').html(template.render(data));
            }
        },
        allTimeOmset : () => {
            $.ajax({
                url : sejoli_widgets.all_time_omset.url,
                type : 'GET',
                data : sejoli_widgets.all_time_omset.data,
                beforeSend : function() {
                    sejoliWidgets.helper.beforeLoad('#sejoli-all-time-omset');
                },
                success : function(response) {
                    sejoliWidgets.helper.appendData('#sejoli-all-time-omset', response);
                }
            })
        },
        monthlyOmset : () => {
            $.ajax({
                url : sejoli_widgets.monthly_omset.url,
                type : 'GET',
                data : sejoli_widgets.monthly_omset.data,
                beforeSend : function() {
                    sejoliWidgets.helper.beforeLoad('#sejoli-monthly-omset');
                },
                success : function(response) {
                    sejoliWidgets.helper.appendData('#sejoli-monthly-omset', response);
                }
            })
        },
		onemonthagoOmset : () => {
            $.ajax({
                url : sejoli_widgets.onemonthago_omset.url,
                type : 'GET',
                data : sejoli_widgets.onemonthago_omset.data,
                beforeSend : function() {
                    sejoliWidgets.helper.beforeLoad('#sejoli-onemonthago-omset');
                },
                success : function(response) {
                    sejoliWidgets.helper.appendData('#sejoli-onemonthago-omset', response);
                }
            })
        },
		twomonthsagoOmset : () => {
            $.ajax({
                url : sejoli_widgets.twomonthsago_omset.url,
                type : 'GET',
                data : sejoli_widgets.twomonthsago_omset.data,
                beforeSend : function() {
                    sejoliWidgets.helper.beforeLoad('#sejoli-twomonthsago-omset');
                },
                success : function(response) {
                    sejoliWidgets.helper.appendData('#sejoli-twomonthsago-omset', response);
                }
            })
        },
        allTimeProduct : () => {
            $.ajax({
                url : sejoli_widgets.all_time_product.url,
                type : 'GET',
                data : sejoli_widgets.all_time_product.data,
                beforeSend : function() {
                    sejoliWidgets.helper.beforeLoad('#sejoli-all-time-product');
                },
                success : function(response) {
                    sejoliWidgets.helper.appendData('#sejoli-all-time-product', response);
                }
            })
        },
        monthlyProduct : () => {
            $.ajax({
                url : sejoli_widgets.monthly_product.url,
                type : 'GET',
                data : sejoli_widgets.monthly_product.data,
                beforeSend : function() {
                    sejoliWidgets.helper.beforeLoad('#sejoli-monthly-product');
                },
                success : function(response) {
                    sejoliWidgets.helper.appendData('#sejoli-monthly-product', response);
                }
            })
        },
		onemonthagoProduct : () => {
            $.ajax({
                url : sejoli_widgets.onemonthago_product.url,
                type : 'GET',
                data : sejoli_widgets.onemonthago_product.data,
                beforeSend : function() {
                    sejoliWidgets.helper.beforeLoad('#sejoli-onemonthago-product');
                },
                success : function(response) {
                    sejoliWidgets.helper.appendData('#sejoli-onemonthago-product', response);
                }
            })
        },
		twomonthsagoProduct : () => {
            $.ajax({
                url : sejoli_widgets.twomonthsago_product.url,
                type : 'GET',
                data : sejoli_widgets.twomonthsago_product.data,
                beforeSend : function() {
                    sejoliWidgets.helper.beforeLoad('#sejoli-twomonthsago-product');
                },
                success : function(response) {
                    sejoliWidgets.helper.appendData('#sejoli-twomonthsago-product', response);
                }
            })
        },
        allTimeCommission : () => {
            $.ajax({
                url : sejoli_widgets.all_time_commission.url,
                type : 'GET',
                data : sejoli_widgets.all_time_commission.data,
                beforeSend : function() {
                    sejoliWidgets.helper.beforeLoad('#sejoli-all-time-commission');
                },
                success : function(response) {
                    sejoliWidgets.helper.appendData('#sejoli-all-time-commission', response);
                }
            })
        },
        monthlyCommission : () => {
            $.ajax({
                url : sejoli_widgets.monthly_commission.url,
                type : 'GET',
                data : sejoli_widgets.monthly_commission.data,
                beforeSend : function() {
                    sejoliWidgets.helper.beforeLoad('#sejoli-monthly-commission');
                },
                success : function(response) {
                    sejoliWidgets.helper.appendData('#sejoli-monthly-commission', response);
                }
            })
        },
		onemonthagoCommission : () => {
            $.ajax({
                url : sejoli_widgets.onemonthago_commission.url,
                type : 'GET',
                data : sejoli_widgets.onemonthago_commission.data,
                beforeSend : function() {
                    sejoliWidgets.helper.beforeLoad('#sejoli-onemonthago-commission');
                },
                success : function(response) {
                    sejoliWidgets.helper.appendData('#sejoli-onemonthago-commission', response);
                }
            })
        },
		twomonthsagoCommission : () => {
            $.ajax({
                url : sejoli_widgets.twomonthsago_commission.url,
                type : 'GET',
                data : sejoli_widgets.twomonthsago_commission.data,
                beforeSend : function() {
                    sejoliWidgets.helper.beforeLoad('#sejoli-twomonthsago-commission');
                },
                success : function(response) {
                    sejoliWidgets.helper.appendData('#sejoli-twomonthsago-commission', response);
                }
            })
        }
    }

    $(document).ready(function(){
        sejoliWidgets.allTimeOmset();
        sejoliWidgets.monthlyOmset();
		sejoliWidgets.onemonthagoOmset();
		sejoliWidgets.twomonthsagoOmset();

        sejoliWidgets.allTimeProduct();
        sejoliWidgets.monthlyProduct();
		sejoliWidgets.onemonthagoProduct();
		sejoliWidgets.twomonthsagoProduct();

        sejoliWidgets.allTimeCommission();
        sejoliWidgets.monthlyCommission();
		sejoliWidgets.onemonthagoCommission();
		sejoliWidgets.twomonthsagoCommission();
    });

})(jQuery);
