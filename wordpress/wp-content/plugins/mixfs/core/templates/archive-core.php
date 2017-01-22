<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if ( ! is_user_logged_in()) {
    auth_redirect();
} else {
    echo '<script type="text/javascript">'
        . 'setTimeout(function(){location.href="' . admin_url('admin.php?page=mixfs-entrance') . '"},10)'
        . '</script>';
}