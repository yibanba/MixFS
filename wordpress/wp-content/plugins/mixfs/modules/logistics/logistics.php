<?php


if (!defined('ABSPATH')) exit; // Exit if accessed directly

function logistics() {
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null )
    global $current_user;
    switch ($current_user->roles[0]) {
        case 'mixfs_operator':
            $cap = 'mixfs_operator';
            break;
        case 'mixfs_assistant':
            $cap = 'mixfs_assistant';
            break;
        default:
            $cap = 'manage_options';
            break;
    }

    if ($cap == 'manage_options' || $cap == 'mixfs_operator') {
        add_menu_page('', '物流', $cap, 'logistics-init', 'logistics_init', '', 150);

        add_submenu_page('logistics-init', '物流1', '物流1', $cap, 'logistics-init', 'logistics_init');
        add_submenu_page('logistics-init', '物流2', '物流2', $cap, 'logistics-init2', 'logistics_init2');
    }
}
add_action('admin_menu', 'logistics');


function logistics_init() {
    include_once( 'logistics-init.php' );
}
function logistics_init2() {
    include_once( 'logistics-init2.php' );
}