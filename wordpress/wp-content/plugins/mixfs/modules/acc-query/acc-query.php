<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function acc_query() {
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null )
    //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' )
    add_menu_page('', '明细查询', 'manage_options', 'acc-query-instructions', 'acc_query_instructions');
    
    add_submenu_page('acc-query-instructions', '明细查询', '明细查询', 'manage_options', 'acc-query-instructions', 'acc_query_instructions');

}
add_action('admin_menu', 'acc_query');


function acc_query_instructions() {
    include_once( 'acc-query-instructions.php' );
}