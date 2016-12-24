<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

function acc_init() {
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null )
    //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' )
    add_menu_page('', '添加初始信息', 'manage_options', 'acc-init-instructions', 'acc_init_instructions');
    
    add_submenu_page('acc-init-instructions', '添加初始信息', '添加信息说明', 'manage_options', 'acc-init-instructions', 'acc_init_instructions');
    add_submenu_page('acc-init-instructions', '产成品', '产成品', 'manage_options', 'mixfs-entrance', 'entrance');
    add_submenu_page('acc-init-instructions', '原材料', '原材料', 'manage_options', 'accounting-list', 'accounting_list');
    add_submenu_page('acc-init-instructions', '仓库店面', '仓库店面', 'manage_options', 'accounting-list', 'accounting_list');
    add_submenu_page('acc-init-instructions', '费用', '费用', 'manage_options', 'accounting-permission', 'accounting_permission');
    add_submenu_page('acc-init-instructions', '货柜', '货柜', 'manage_options', 'accounting-permission', 'accounting_permission');
    add_submenu_page('acc-init-instructions', '供应商', '供应商', 'manage_options', 'accounting-permission', 'accounting_permission');
}
add_action('admin_menu', 'acc_init');


function acc_init_instructions() {
    include_once( 'acc-init-instructions.php' );
}