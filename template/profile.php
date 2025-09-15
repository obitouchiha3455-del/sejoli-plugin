<?php
    sejoli_header();

    $current_user = wp_get_current_user();
    $subdistrict = $current_user->_destination_name;
    $subdistrict_id = $current_user->_destination;

    if (!empty($subdistrict)) :
        $district = sejolisa_get_district_options_by_ids($subdistrict); // pastikan ini array

        if (!empty($district['results'])) :
            // Cari yang cocok berdasarkan ID
            foreach ($district['results'] as $item) {
                if ((string)$item['id'] === (string)$subdistrict_id) {
                    $subdistrict = $item;
                    break;
                }
            }
        endif;
    endif;

    $user_group_id = intval(sejolisa_carbon_get_user_meta( $current_user->ID, 'user_group' ));

    if(0 < $user_group_id) :
        $group_detail = sejolisa_get_group_detail($user_group_id);
    endif;
    ?>
    <h2 class="ui header">Profile</h2>

    <?php do_action( 'sejoli/profile/before-form', $current_user); ?>

    <form id="profile" class="ui form" method="post">

        <input style="display:none" type="email" name="email">
        <input style="display:none" type="password" name="password">

        <div class="required field">
            <label><?php _e('Nama', 'sejoli'); ?></label>
            <input type="text" name="name" placeholder="<?php _e('Nama', 'sejoli'); ?>" value="<?php echo $current_user->first_name; ?>">
        </div>

        <div class="required field">
            <label><?php _e('Alamat Email', 'sejoli'); ?></label>
            <input type="email" name="real_email" placeholder="<?php _e('Alamat Email', 'sejoli'); ?>" value="<?php echo $current_user->user_email; ?>">
        </div>

        <div class="required field">
            <label><?php _e('No. Handphone', 'sejoli'); ?></label>
            <input type="text" name="phone" placeholder="<?php _e('No. Handphone', 'sejoli'); ?>" value="<?php echo $current_user->_phone; ?>">
        </div>

        <div class="field">
            <label><?php _e('Alamat Lengkap', 'sejoli'); ?></label>
            <textarea name="address" placeholder="<?php _e('Alamat Lengkap', 'sejoli'); ?>"><?php echo $current_user->_address; ?></textarea>
        </div>

        <div class="field">
            <label><?php _e('Kecamatan', 'sejoli'); ?></label>
            <select name="kecamatan" id="kecamatan">
                <option value=""><?php _e('Silahkan Ketik Nama Kecamatannya', 'sejoli'); ?></option>
                <?php
                if ( $subdistrict ) :
                    ?>
                    <option selected value="<?php echo $subdistrict['id']; ?>"><?php echo $subdistrict['text']; ?></option>
                    <?php
                endif;
                ?>
            </select>
        </div>

        <?php do_action( 'sejoli/profile/form', $current_user); ?>

        <div class="field">
            <label><?php _e('Informasi Rekening', 'sejoli'); ?></label>
            <textarea name="_bank_info" placeholder="<?php _e('Informasi Rekening', 'sejoli'); ?>"><?php echo $current_user->_bank_info; ?></textarea>
        </div>

        <div class="field">
            <label><?php _e('Password Baru', 'sejoli'); ?></label>
            <input type="password" name="password_baru" placeholder="<?php _e('Password Baru', 'sejoli'); ?>" value="" autocomplete="false">
        </div>

        <div class="field">
            <label><?php _e('Konfirmasi Password Baru', 'sejoli'); ?></label>
            <input type="password" name="konfirmasi_password_baru" placeholder="<?php _e('Konfirmasi Password Baru', 'sejoli'); ?>" value="" autocomplete="false">
        </div>

        <?php if(0 < $user_group_id) : ?>
        <div class="field">
            <label><?php _e('User Group: ', 'sejoli'); ?></label>
            <input type="text" name="user_group" placeholder="<?php _e('User Group', 'sejoli'); ?>" readonly value="<?php echo $group_detail['name']; ?>">
        </div>
        <?php endif; ?>

        <?php wp_nonce_field('ajax-nonce', 'security'); ?>

        <button class="ui primary button" type="submit"><?php _e('Submit', 'sejoli'); ?></button>

    </form>

    <?php do_action( 'sejoli/profile/after-form', $current_user); ?>

    <div class="alert-holder profile-alert-holder">
    </div>
    <script id="alert-template" type="text/x-js-render">
        <div class="ui {{:type}} message">
            <i class="close icon"></i>
            <div class="header">
                {{:type}}
            </div>
            {{if messages}}
                <ul class="list">
                {{props messages}}
                    <li>{{>prop}}</li>
                {{/props}}
                </ul>
            {{/if}}
        </div>
    </script>
    <script>
    (function( $ ) {
        'use strict';
        $(document).ready(function(){
            sejoli.profile.init();
        });
    })( jQuery );
    </script>
<?php sejoli_footer(); ?>
