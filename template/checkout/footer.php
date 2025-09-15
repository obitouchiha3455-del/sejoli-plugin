    <?php wp_footer(); ?>

    <script>
        (function($){
            jQuery(document).on("ajaxComplete", function(){
                parent.postMessage({ height: parseInt(document.body.scrollHeight) + 200 }, '*');
            });
        })(jQuery);
    </script>
</body>
</html>