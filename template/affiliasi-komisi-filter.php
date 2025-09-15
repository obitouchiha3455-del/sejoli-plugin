<div id="filter-form-wrap" class="ui small modal">
    <i class="close icon"></i>
    <div class="header">
        <?php _e( 'Filter Data', 'sejoli' ); ?>
    </div>
    <div class="content">
        <form id="commission-filter" class="ui form">
            <div class="field">
                <label><?php _e( 'Tanggal', 'sejoli' ); ?></label>
                <input type="text" id="date-range" name="date-range" class="filter-data">
            </div>
            <div class="field">
                <label><?php _e( 'Produk', 'sejoli' ); ?></label>
                <select id="product_id" name="product_id" class="filter-data">
                    <option value=""><?php _e( '--Pilih Produk--', 'sejoli' ); ?></option>
                </select>
            </div>
            <div class="field">
                <label><?php _e( 'Status Order', 'sejoli' ); ?></label>
                <select id="status" name="status" class="filter-data select2-filled">
                    <option value=""><?php _e( '--Pilih Status Order--', 'sejoli' ); ?></option>
                    <option value="pending"><?php _e('Order belum selesai', 'sejoli'); ?></option>
                    <option value="added"><?php _e('Order sudah selesai', 'sejoli'); ?></option>
                    <option value="cancelled"><?php _e('Order dibatalkan', 'sejoli'); ?></option>
                </select>
            </div>
            <div class="field">
                <label><?php _e( 'Status Pembayaran', 'sejoli' ); ?></label>
                <select id="paid_status" name="paid_status" class="filter-data select2-filled">
                    <option value=""><?php _e( '--Pilih Status Pembayaran--', 'sejoli' ); ?></option>
                    <option value=1><?php _e('Sudah Dibayar', 'sejoli'); ?></option>
                    <option value=0><?php _e('Belum Dibayar', 'sejoli'); ?></option>
                </select>
            </div>
        </form>
    </div>
    <div class="actions">
        <button class="ui primary button filter-form"><?php _e( 'Filter', 'sejoli' ); ?></button>
    </div>
</div>