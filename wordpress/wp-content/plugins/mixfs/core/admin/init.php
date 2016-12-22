<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

function mixfs_admin_menus() {
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null )
    //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' )
    add_menu_page('', '米克斯财会软件', 'manage_options', 'mixfs-instructions', 'instructions');
    
    add_submenu_page('mixfs-instructions', '使用说明', 'MixFS 使用说明', 'manage_options', 'mixfs-instructions', 'instructions');
    add_submenu_page('mixfs-instructions', '财务软件入口', '财务软件入口', 'manage_options', 'mixfs-entrance', 'entrance');
    add_submenu_page('mixfs-instructions', '账套列表', '账套列表', 'manage_options', 'accounting-list', 'accounting_list');
    add_submenu_page('mixfs-instructions', '权限分配', '权限分配', 'manage_options', 'accounting-permission', 'accounting_permission');
}
add_action('admin_menu', 'mixfs_admin_menus');

function instructions() {
    include_once( 'instructions.php' );
}

function entrance() {
    include_once( 'entrance.php' );
}

function accounting_list() {
    include_once( 'accounting-list.php' );
}
function accounting_permission() {
    include_once( 'accounting-permission.php' );
}

/**
 * 去除升级提示
 *
remove_action( 'load-update-core.php', 'wp_update_core' );// 移除核心更新的加载项
add_filter( 'pre_site_transient_update_core', create_function( '$a',"return null;" ) );
 * 
 */
/**
 * 后台右下角仍有升级提示
 * 
add_action('admin_menu','wp_hide_nag'); 
function wp_hide_nag() { 
     remove_action( 'admin_notices', 'update_nag', 3 ); 
}
 * 
 */
/**
 * 
 * 
add_filter('pre_site_transient_update_core', create_function('$a', "return null;")); // 关闭核心提示  
add_filter('pre_site_transient_update_plugins', create_function('$a', "return null;")); // 关闭插件提示  
add_filter('pre_site_transient_update_themes', create_function('$a', "return null;")); // 关闭主题提示  
remove_action('admin_init', '_maybe_update_core'); // 禁止 WordPress 检查更新  
remove_action('admin_init', '_maybe_update_plugins'); // 禁止 WordPress 更新插件  
remove_action('admin_init', '_maybe_update_themes'); // 禁止 WordPress 更新主题 
 * 
 */
