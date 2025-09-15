<?php sejoli_header(); ?>
    <h2 class="ui header"><?php _e('Lisensi', 'sejoli'); ?></h2>
    <button class="ui primary button show-filter-form"><i class="filter icon"></i> <?php _e( 'Filter Data', 'sejoli' ); ?></button>
    <table id="sejoli-license" class="ui striped single line table" style="width:100%;word-break: break-word;white-space: normal;">
        <thead>
            <tr>
                <th><?php _e('Lisensi',       'sejoli'); ?></th>
                <th><?php _e('Pemilik',     'sejoli'); ?></th>
                <th><?php _e('Penanda',    'sejoli'); ?></th>
                <th><?php _e('Status',      'sejoli'); ?></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <th><?php _e('Lisensi',       'sejoli'); ?></th>
                <th><?php _e('Pemilik',     'sejoli'); ?></th>
                <th><?php _e('Penanda',    'sejoli'); ?></th>
                <th><?php _e('Status',      'sejoli'); ?></th>
            </tr>
        </tfoot>
    </table>
    <?php
    include('license-filter.php');
    ?>
    <script id="sejoli-edit-license-tmpl" type="text/x-jsrender">
        <strong>
            {{:code}}
        </strong>
        <div style='line-height:220%'>
            <span class="ui olive label">INV {{:order}}</span>
            <span class="ui violet label"><i class="box icon"></i>{{:product}}</span>
        </div>
    </script>
    <script id='license-status' type="text/x-jsrender">
        <div class="ui horizontal label boxed" style="background-color:{{:color}};">{{:label}}</div>
    </script>
    <script>
        jQuery(document).ready(function($){
            sejoli.license.init();
        });
    </script>
<?php sejoli_footer(); ?>
