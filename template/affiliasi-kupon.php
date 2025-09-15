<?php sejoli_header(); ?>
    <h2 class="ui header"><?php _e('Affiliasi Kupon', 'sejoli'); ?></h2>
    <div class="wrap">
        <div class="sejoli-wrap">
            <div class="postbox">
                <div class="inside">
                    <div class="box-action">
                        <button class="ui primary button show-filter-form"><i class="filter icon"></i> <?php _e( 'Filter Data', 'sejoli' ); ?></button>
                        <button class="ui primary button show-add-coupon-form"><i class="plus icon"></i> <?php _e( 'Tambah Kupon', 'sejoli' ); ?></button>
                    </div>
                    <table id="sejoli-affiliate-coupons" class="ui striped single line table" style="width:100%;word-break: break-word;white-space: normal;">
                        <thead>
                            <tr>
                                <th><?php _e('Detil',       'sejoli'); ?></th>
                                <th><?php _e('Discount',    'sejoli'); ?></th>
                                <th><?php _e('Penggunaan',  'sejoli'); ?></th>
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
                                <th><?php _e('Discount',    'sejoli'); ?></th>
                                <th><?php _e('Penggunaan',  'sejoli'); ?></th>
                                <th><?php _e('Status',      'sejoli'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <br class="clear">
        </div>
    </div>

    <div id="filter-form-wrap" class="ui small modal">
        <i class="close icon"></i>
        <div class="header">
            <?php _e( 'Filter Data', 'sejoli' ); ?>
        </div>
        <div class="content">
            <form id="filter-form" class="ui form">
                <div class="field">
                    <label><?php _e( 'Pencarian Kupon', 'sejoli' ); ?></label>
                    <input name="code" id="code" class="" placeholder="<?php _e( 'Pencarian Kupon', 'sejoli' ); ?>">
                </div>
                <div class="field">
                    <label><?php _e( 'Status Kupon', 'sejoli' ); ?></label>
                    <select name="status" id="status" class="select2-filled">
                        <option value=""><?php _e('--Pilih Status Kupon--', 'sejoli'); ?></option>
                        <option value="pending"><?php _e('Tidak aktif', 'sejoli'); ?></option>
                        <option value="active"><?php _e('Aktif', 'sejoli'); ?></option>
                        <option value="need-approve"><?php _e('Butuh persetujuan', 'sejoli'); ?></option>
                    </select>
                </div>
            </form>
        </div>
        <div class="actions">
            <button class="ui primary button filter-form"><?php _e( 'Filter', 'sejoli' ); ?></button>
        </div>
    </div>

    <div id="add-coupon-form-wrap" class="ui small modal">
        <i class="close icon"></i>
        <div class="header">
            <?php _e( 'Tambah Kupon', 'sejoli' ); ?>
        </div>
        <div class="content">
            <div class="ui message warning">
                <?php _e('<strong>PERHATIAN</strong> : Kupon yang sudah anda buat tidak bisa diubah kembali', 'sejoli'); ?>
            </div>
            <form id="add-coupon-form" class="ui form">
                <div class="required field">
                    <label><?php _e( 'Kupon Utama', 'sejoli' ); ?></label>
                    <select name="coupon_parent_id" id="coupon_parent_id2" class="coupon_parent_id" required>
                    </select>
                </div>
                <div class="required field">
                    <label><?php _e( 'Kode Kupon', 'sejoli' ); ?></label>
                    <input type="text" name="code" id="coupon_code" placeholder="<?php _e( 'Kode Kupon', 'sejoli' ); ?>" required>
                </div>
                <div id="add-coupon-message">
                </div>
            </form>
        </div>
        <div class="actions">
            <button class="ui primary button add-coupon"><?php _e( 'Simpan', 'sejoli' ); ?></button>
        </div>
    </div>

    <script id="sejoli-edit-coupon-tmpl" type="text/x-jsrender">
        <div class="coupon-action">
            <div class="ui fluid action input">
                <input id="aff-coupon-{{:code}}" name="aff-coupon-{{:code}}" type="text" value="{{:code}}" readonly>
                <button class="ui right labeled icon button copy-btn" data-clipboard-target="#aff-coupon-{{:code}}"><i class="copy icon"></i> <?php _e( 'Copy', 'sejoli' ); ?></button>
            </div>
            <hr />
            {{if parent}}
            <span class="ui teal label"><i class="tag icon"></i>{{:parent}}</span>
            {{/if}}

            {{if limit_date}}
            <span class="ui red label"><i class="calendar outline icon"></i>{{:limit_date}}</span>
            {{/if}}

            {{if limit_use}}
            <span class="ui red label"><i class="redo icon"></i>{{:limit_use}}</span>
            {{/if}}
            
            {{if renewal_coupon}}
            <span class="ui green label"><i class="refresh icon"></i><?php _e( 'RENEWAL COUPON', 'sejoli' ); ?></span>
            {{/if}}

            {{if free_shipping}}
            <span class="ui green label"><i class="truck icon"></i>FREE SHIPPING</span>
            {{/if}}
        </div>
    </script>
    <script id='coupon-status' type="text/x-jsrender">
        <div class="ui horizontal label boxed" style="background-color:{{:color}};">{{:label}}</div>
    </script>

    <script>
    (function( $ ) {
        'use strict';
        $(document).ready(function() {
            sejoli.affiliate.coupons.init();
        });
    })( jQuery );
    </script>

<?php sejoli_footer(); ?>
