<script type="text/javascript">
(function( $ ) {
    'use strict';
    $(document).ready(function(){
        Hooks.add_filter('sejoli_order_action', function(text, data){
            if(
                0 !== data.product.has_followup_content &&
                'meta_data' in data &&
                null !== data.meta_data &&
                data.meta_data.hasOwnProperty('followup') ) {
                    var item = '',
                        template = $.templates('#order-followup-link-content');

                    $.each(data.meta_data.followup.admin,function(i,v){
                        item += template.render({
                            index : i,
                            link : sejoli_admin.followup.basic_link + data.ID + '/' + i,
                            status : ('' === v) ? '' : 'done'
                        })
                    });

                    text = text + item;
            }
            return text;
        });
    });

    $('body').on('click','.order-followup-link', function(){
        $(this).addClass('done');
    });
})(jQuery);
</script>
<script id='order-followup-link-content' type="text/x-jsrender">
<a class="item {{:status}} order-followup-link" href='{{:link}}' target='_blank'>Follow Up {{:index}}</a>
</script>
