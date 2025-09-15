<?php sejoli_header(); ?>
    <h2 class="ui header">Affiliasi Facebook Pixel</h2>
    <table id="affiliasi-facebook-pixel" class="ui celled table" style="width:100%">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Facebook Pixel</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <th>Produk</th>
                <th>Facebook Pixel</th>
            </tr>
        </tfoot>
    </table>
    <div id="facebook-pixel-modal" class="ui modal">
        <i class="close icon"></i>
        <div class="header">
            Facebook Pixel
        </div>
        <div class="content">
            <h3 id="product_title"></h3>
            <form id="facebook-pixel-form" class="ui form">
                <div class="required field">
                    <label>ID Pixel</label>
                    <input type="text" id="id_pixel" name="id_pixel" value="" placeholder="ID Pixel">
                    <input type="hidden" id="product_id" name="product_id" value="">
                </div>
                <button class="ui primary button" type="submit">Submit</button>
            </form>
            <div class="alert-holder">
            </div>
            <div class="fb-pixel-links-holder">
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
        <button data-title="{{:title}}" data-id="{{:id}}" class="ui primary button edit-bonus-toggle">Edit Facebook Pixel</button>
    </script>
    <script id="fb-pixel-links-template" type="text/x-js-render">
        {{if links}}
            <ol>
            {{props links}}
                <li>
                    <p><b>{{:prop.title}}</b></p>
                    <hr>
                    <p>{{:prop.detail}}</p>
                    <div class="ui fluid action input">
                        <input id="fb-pixel-link-{{:key}}" name="fb-pixel-link-{{:key}}" type="text" value="{{:prop.link}}" readonly>
                        <button class="ui teal right labeled icon button copy-btn" data-clipboard-target="#fb-pixel-link-{{:key}}"><i class="copy icon"></i> <?php _e( 'Copy', 'sejoli' ); ?></button>
                    </div>
                </li>
            {{/props}}
            </ol>
        {{/if}}
    </script>
    <script>
        jQuery(document).ready(function($){
            sejoli.affiliate.facebook_pixel.init();
        });
    </script>
<?php sejoli_footer(); ?>