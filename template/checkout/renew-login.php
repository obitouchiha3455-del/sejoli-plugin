<?php
wp_login_form(array(
    'redirect'	=> add_query_arg(array(
                        'order_id'	=> $_GET['order_id']
                   ),home_url('checkout/renew/'))
));
?>
<style type="text/css">
#loginform {
    margin: 1rem 0;
    padding: 1em 1em;
}
#loginform label {
    font-size: 1em;
    display: block;
}
#loginform input[type=text],
#loginform input[type=email],
#loginform input[type=password] {
    font-size: 1em;
    line-height: 1.21428571em;
    padding: .67857143em 1em;
    width: calc( 100% - 2rem );
    border: 1px solid #666;
}

#loginform p.login-submit {
    text-align: center;
}

 #loginform p.login-submit input#wp-submit {
    width: 180px;
    background-color: #00b5ad;
    color: white;
    line-height: 1.21428571em;
    border: none;
}
 #loginform p#nav,
 #loginform p#backtoblog {
    text-align: center;
}

</style>
