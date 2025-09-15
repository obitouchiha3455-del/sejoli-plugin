{{props products}}
<div class="column">
    <div class="ui fluid card">
        <div class="content">
            <div class="header">{{:prop.product_name}}</div>
        </div>
        <div class="content">
            <h4 class="ui sub header"><?php _e('Akses produk anda disini','sejoli'); ?></h4>
            {{props prop.content}}
                <p><a href="{{:prop.link}}" class='click-access' data-accessid='{{:prop.id}}' data-access='{{:prop.link}}' data-product='{{:prop.access_product}}'>{{:prop.title}}</a></p>
            {{else}}
                <p>-</p>
            {{/props}}
        </div>
        <div class="content">
            <h4 class="ui sub header"><?php _e('Download File','sejoli'); ?></h4>
            {{props prop.attachments}}
                <p><a href="{{:prop.link}}">{{:prop.name}}</a></p>
            {{else}}
                <p>-</p>
            {{/props}}
        </div>
        {{if prop.bonus}}
        <div class="content">
            <h4 class='ui sub header'><?php _e('Bonus Affiliasi', 'sejoli'); ?></h4>
            {{props prop.bonus}}
                <p><a href='#' class='open-bonus' data-affiliate='{{:prop.affiliate_id}}' data-product='{{:prop.product_id}}'>Bonus dari {{:prop.name}}</a></p>
            {{/props}}
        </div>
        {{/if}}

        <!-- <div class="content">
            <h4 class="ui sub header">Atur Lisensi</h4>
            <p><a>Link Here</a></p>
        </div> -->
    </div>
</div>
{{else}}
<div class="column">
    <div class="ui fluid card">
        <div class="content">
            <p><?php _e('Tidak ada data yang bisa ditampilkan','sejoli'); ?></p>
        </div>
    </div>
</div>
{{/props}}
