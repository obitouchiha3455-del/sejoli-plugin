<div class="two wide column center aligned">
    <div class="ui fluid card">
        <a class="image" href="#">
            <div class="floating ui red circular label">{{:rank}}</div>
            <img src="{{:image}}">
        </a>
        <div class="content">
            <a class="header" href="#">{{:name}}</a>
            <?php if(current_user_can('manage_options') && is_admin()) : ?>
            <div class="meta">
                <a>{{:total}} Sales</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
