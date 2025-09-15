<?php sejoli_header(); ?>
    <h2 class="ui header"><?php _e('Affiliasi Komisi', 'sejoli'); ?></h2>
    <button class="ui primary button show-filter-form"><i class="filter icon"></i> <?php _e( 'Filter Data', 'sejoli' ); ?></button>
    <table id="commission" class="ui striped single line table" style="width:100%;word-break: break-word;white-space: normal;">
        <thead>
            <tr>
                <th><?php _e('Detil',       'sejoli'); ?></th>
                <th><?php _e('Affiliasi',   'sejoli'); ?></th>
                <th><?php _e('Tier',        'sejoli'); ?></th>
                <th><?php _e('Total Komisi',       'sejoli'); ?></th>
                <th><?php _e('Status',      'sejoli'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5"><?php _e('Tidak ada data yang bisa ditampilkan', 'sejoli'); ?></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th><?php _e('Detil',       'sejoli'); ?></th>
                <th><?php _e('Affiliasi',   'sejoli'); ?></th>
                <th><?php _e('Tier',        'sejoli'); ?></th>
                <th><?php _e('Total Komisi',       'sejoli'); ?></th>
                <th><?php _e('Status',      'sejoli'); ?></th>
            </tr>
        </tfoot>
    </table>

    <?php
    include('affiliasi-komisi-filter.php');
    ?>

    <div id="commission-detail-holder" class="ui modal">
    </div>

    <script id='order-modal-content' type="text/x-jsrender">
    <i class="close icon"></i>
    <div class="header">
        <?php _e('Detil Order {{:id}}', 'sejoli'); ?>
    </div>
    <div class="content">
        <div class="ui divided selection list">
            <div class="item">
                <span class="ui large main blue horizontal label"><?php _e('Tanggal', 'sejoli'); ?></span>
                {{:date}}
            </div>
            <div class="item">
                <span class="ui large main blue horizontal label"><?php _e('Nama Pembeli', 'sejoli'); ?></span>
                {{:buyer_name}}
            </div>
            {{if buyer_email}}
            <div class="item">
                <span class="ui large main blue horizontal label"><?php _e('Kontak', 'sejoli'); ?></span>
                <span class='ui grey label'><i class="phone icon"></i>{{:buyer_phone}}</span>
                <span class='ui grey label'><i class="envelope icon"></i>{{:buyer_email}}</span>
            </div>
            {{/if}}
            <div class="item">
                <span class="ui large main blue horizontal label" style='float:left;'><?php _e('Produk', 'sejoli'); ?></span>
                <span class="order-product-detail">
                    {{:product_name}} X{{:quantity}} <br />
                    {{:variants}}
                </span>
            </div>
            {{if ppn}}
                <div class='item'>
                    <span class="ui large main blue horizontal label"><?php _e('PPN', 'sejoli'); ?> ({{:ppn}}%)</span>
                    {{:ppn_total}}
                </div>
            {{/if}}
            {{if unique_code}}
                <div class='item'>
                    <span class="ui large main blue horizontal label"><?php _e('Biaya Transaksi', 'sejoli'); ?></span>
                    {{:unique_code}}
                </div>
            {{/if}}
            <div class="item">
                <span class="ui large main blue horizontal label"><?php _e('Total', 'sejoli'); ?></span>
                {{:total}}
            </div>
            {{if courier}}
            <div class='item'>
                <span class="ui large main blue horizontal label"><?php _e('Kurir', 'sejoli'); ?></span>
                {{:courier}}
            </div>
            {{/if}}
            {{if markup_price !== 'Rp. NaN'}}
            <div class='item'>
                <span class="ui large main blue horizontal label"><?php _e('Biaya COD', 'sejoli'); ?></span>
                {{:markup_price}}
            </div>
            {{/if}}
            {{if shipping_data }}
                {{if shipping_data.resi_number}}
                    <div class='item'>
                        <span class="ui large main blue horizontal label"><?php _e('Nomor Resi', 'sejoli'); ?></span>
                        {{:shipping_data.resi_number}}
                    </div>
                {{/if}}
            {{/if}}
            {{if address}}
            <div class='item'>
                <span class="ui large main blue horizontal label"><?php _e('Alamat Pengiriman', 'sejoli'); ?></span>
                {{:address}}
            </div>
            {{/if}}
            <div class="item">
                <span class="ui large main blue horizontal label"><?php _e('Status', 'sejoli'); ?></span>
                <span class="ui large horizontal label" style="background-color:{{:color}};color:white;">{{:status}}</span>
            </div>

            {{if subscription }}
            <div class="item">
                <span class="ui large main blue horizontal label"><?php _e('Tipe Langganan', 'sejoli'); ?></span>
                <span class="ui brown label" style='text-transform:uppercase;'><i class="clock icon"></i>{{:subscription}}</span>
            </div>
            {{/if}}

            {{if parent_order}}
            <div class="item">
                <span class="ui large main blue horizontal label"><?php _e('Invoice Asal', 'sejoli'); ?></span>
                <span class="ui pink label" style='text-transform:uppercase;'><i class="redo icon"></i>INV {{:parent_order}}</span>
            </div>
            {{/if}}
            
        </div>
    </div>
    </script>
    <script id='order-variant-data' type="text/x-jsrender">
    <span style='text-transform:capitalize;'>{{:type}}</span> : {{:label}} <br />
    </script>

    <script id="tmpl-commission-detail" type="text/x-js-render">
        <?php include('affiliasi-komisi-detail-tmpl.php'); ?>
    </script>

    <script id='tmpl-commission-cair' type="text/x-jsrender">
        <i class="{{:cair}} icon"></i>
    </script>

    <script id='tmpl-commission-button-group' type="text/x-jsrender">
        <div class="ui small basic icon buttons">
            <button data-id='{{:id}}' class="ui button commission-detail"><i class="eye icon"></i></button>
        </div>
    </script>

    <script id='order-detail' type="text/x-jsrender">
        <button type='button' class='order-detail-trigger ui mini button' data-id='{{:order_id}}' data-commission='{{:id}}'>DETAIL</button>
        <strong>
            {{:product}}
        </strong>
        <div style='line-height:220%'>
            <span class="ui olive label">INV {{:order_id}}</span>
            <span class="ui teal label"><i class="calendar outline icon"></i>{{:date}}</span>
        </div>
    </script>

    <script id='order-status' type="text/x-jsrender">
        <div class="ui horizontal label boxed {{:status}}" style="background-color:{{:color}};">{{:label}}</div>
    </script>

    <script>
    (function( $ ) {
        'use strict';
        $(document).ready(function(){

            sejoli.affiliate.commission.renderData();

        });
    })( jQuery );
    </script>
<?php sejoli_footer(); ?>
