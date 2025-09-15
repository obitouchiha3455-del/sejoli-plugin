<?php sejoli_header(); ?>

<h2 class="ui header"><?php echo __('Jaringan Anda', 'sejoli'); ?></h2>

<div id='sejoli-form-message' class="woocommerce-message" style='display:none'></div>

<div id="network-tree-area" data-show-contact="<?php echo sejolisa_carbon_get_theme_option( 'sejoli_affiliate_tool_data_kontak_aff' ); ?>"></div>
    <div id="network-tree-list"></div>
</div>

<div id="affiliate-detail-popup"></div>

<script>
(function( $ ) {
    'use strict';

    $(document).ready(function(){

        let blockMessage = $('#sejoli-form-message');
        let showContact  = $('#network-tree-area').data('show-contact');

        $('#network-tree-list').jstree({
            core:   {
                data:   {
                    url    : sejoli_member_area.affiliate.network.list.ajaxurl
                }
            },
            plugins : [ "themes", "json_data", "ui" ]
        });

        if( showContact === 1 ){

            $(document).on('click', '.jstree-anchor', function(){

                let dataID = parseInt($(this).data('id'));

                $.ajax({
                    url:  sejoli_member_area.affiliate.network.detail.ajaxurl,
                    type: 'GET',
                    data: {
                        'id': dataID
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        sejoli.block('#network-tree-area');
                        blockMessage.removeClass('woocommerce-error').hide().html('');
                    },
                    success: function(response) {
                        sejoli.unblock('#network-tree-area');

                        if(response.valid) {
                            let tmpl = $.templates('#affiliate-network-modal-content');

                            $('#affiliate-detail-popup').html(tmpl.render({
                                title: '<?php _e('Detil Downline', 'sejoli'); ?>',
                                user: response.user
                            }));

                            $.blockUI({
                                message: $('#affiliate-detail-popup'),
                                css: {
                                    width: '480px',
                                    border: 'none'
                                },
                                onBlock: function() {
                                $(".blockPage").addClass("sejoli-popup");
                                }
                            });

                            $('.blockOverlay')
                                .attr('title', '<?php _e('Klik untuk tutup', 'sejoli'); ?>')
                                .click($.unblockUI);

                        } else {
                            blockMessage.addClass('woocommerce-error').html(response.message).fadeIn();
                        }
                    }
                });

                return false;

                });

        }else{

            $('.jstree-anchor').click(function (e) {
                e.preventDefault();
            });

        }

    });
})( jQuery );
</script>

<!-- AFFILIATE NETWORK MODALCONTENT -->
<script id='affiliate-network-modal-content' type="text/x-jsrender">
<section class="affiliate-detail">
    <h2 class="affiliate-detail-title">{{:title}}</h2>
    <table border="0">
        <tbody>
            <tr>
                <th scope="row"><?php _e('Nama:', 'sejoli'); ?></th>
                <td>{{:user.name}}</td>
            </tr>
            {{if user.email}}
            <tr>
                <th scope="row"><?php _e('Email:', 'sejoli'); ?></th>
                <td>{{:user.email}}</td>
            </tr>
            {{/if}}
            {{if user.phone}}
            <tr>
                <th scope="row"><?php _e('Nomor Telpon:', 'sejoli'); ?></th>
                <td>{{:user.phone}}</td>
            </tr>
            {{/if}}
            {{if user.address}}
            <tr>
                <th scope="row"><?php _e('Alamat:', 'sejoli'); ?></th>
                <td>{{:user.address}}</td>
            </tr>
            {{/if}}

        </tbody>
    </table>
</section>
</script>

<?php sejoli_footer(); ?>
