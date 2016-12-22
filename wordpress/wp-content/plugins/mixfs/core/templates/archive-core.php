<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if ( ! is_user_logged_in()) {
    auth_redirect();
} else {
    get_header();
    echo '<div id="content"><div style="margin:100px;">'
        . '<a href="' . $admin_url . '">请登陆后访问，1秒钟后自动跳转至登陆页面 >>> </a>'
        . '</div></div>';
    echo '<script type="text/javascript">'
        . 'setTimeout(function(){location.href="' . $admin_url . '"},10)'
        . '</script>';
    get_footer();
}