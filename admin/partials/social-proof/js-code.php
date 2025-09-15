<?php

global $post;

$post_id = ( ! property_exists( $post, 'ID' ) ) ? 0 : $post->ID;
$post_id = ( isset( $_GET['post'] ) ) ? intval($_GET['post']) : $post_id;
?>
<script type="text/javascript">
(function( $ ){

    'use strict';

    let postID = parseInt('<?php echo $post_id; ?>');

    $(document).ready(function(){
        if( 0 < postID ) {
            let code = 	"<script type='text/javascript' src='<?php echo home_url('sejoli-social-proof/' . $post_id ); ?>'>";

            $("textarea[name='carbon_fields_compact_input[_social_proof_code]']").val(code + "<\/script>");
        }
    });
})(jQuery);
</script>
