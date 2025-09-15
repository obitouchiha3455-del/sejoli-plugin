<?php sejoli_header(); ?>
    <h2 class="ui header"><?php _e('Affiliasi Bonus Editor', 'sejoli'); ?></h2>
    <table id="affiliasi-bonus-editor" class="ui celled table" style="width:100%">
        <thead>
            <tr>
                <th><?php _e('Produk', 'sejoli'); ?></th>
                <th><?php _e('Bonus', 'sejoli'); ?></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <th><?php _e('Produk', 'sejoli'); ?></th>
                <th><?php _e('Bonus', 'sejoli'); ?></th>
            </tr>
        </tfoot>
    </table>
    <div id="bonus-editor-modal" class="ui modal">
        <i class="close icon"></i>
        <div class="header">
            <?php _e('Bonus Editor', 'sejoli'); ?>
        </div>
        <div class="content">
            <h3 id="product_title"></h3>
            <form id="bonus-editor-form" class="ui form">
                <div class="required field">
                    <label><?php _e('Konten', 'sejoli'); ?></label>
                    <textarea id="bonus" name="content" placeholder="Bonus"></textarea>
                    <input type="hidden" id="product_id" name="product_id" value="">
                </div>
                <button class="ui primary button" type="submit"><?php _e('Submit', 'sejoli'); ?></button>
            </form>
            <div class="alert-holder">
            </div>
        </div>
    </div>
    <script id="alert-template" type="text/x-jsrender">
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
    <script id="button-template" type="text/x-js-render">
        <button data-title="{{:title}}" data-id="{{:id}}" class="ui primary button edit-bonus-toggle"><?php _e('Edit Bonus', 'sejoli'); ?></button>
    </script>
    <script>
        jQuery(document).ready(function($){
            sejoli.affiliate.bonus_editor.init();
        });
    </script>
<?php sejoli_footer(); ?>
