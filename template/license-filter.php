<div id="filter-form-wrap" class="ui small modal">
    <i class="close icon"></i>
    <div class="header">
        <?php _e( 'Filter Data', 'sejoli' ); ?>
    </div>
    <div class="content">
        <form id="license-filter" class="ui form">
            <div class="field">
                <label><?php _e( 'Lisensi', 'sejoli' ); ?></label>
                <input type="text" class='filter' name="code" value="" placeholder="<?php _e('Lisensi', 'sejoli'); ?>">
            </div>            
            <div class="field">
                <label><?php _e( 'Penanda', 'sejoli' ); ?></label>
                <input type="text" class='filter' name="string" value="" placeholder="<?php _e('Penanda', 'sejoli'); ?>">
            </div>
            <div class="field">
                <label><?php _e( 'Invoice', 'sejoli' ); ?></label>
                <input type="text" class='filter' name="order_id" value="" placeholder="<?php _e('Invoice', 'sejoli'); ?>">
            </div>
            <div class="field">
                <label><?php _e( 'Produk', 'sejoli' ); ?></label>
                <select id="product_id" name="product_id" class="filter-data">
                    <option value=""><?php _e( '--Pilih Produk--', 'sejoli' ); ?></option>
                </select>
            </div>
            <div class="field">
                <label><?php _e( 'Status Lisensi', 'sejoli' ); ?></label>
                <select class="autosuggest filter" name="status">
                    <option value=""><?php _e('Status Lisensi', 'sejoli'); ?></option>
                    <option value="pending"><?php _e('Tidak aktif', 'sejoli'); ?></option>
                    <option value="active"><?php _e('Aktif', 'sejoli'); ?></option>
                </select>
            </div>
        </form>
    </div>
    <div class="actions">
        <button class="ui primary button filter-form"><?php _e( 'Filter', 'sejoli' ); ?></button>
    </div>
</div>