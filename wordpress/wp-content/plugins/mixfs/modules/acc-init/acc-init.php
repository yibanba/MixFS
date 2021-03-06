<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

function acc_init() {
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
        add_menu_page('', '添加初始信息', $cap, 'acc-init-instructions', 'acc_init_instructions', '', 140);

        add_submenu_page('acc-init-instructions', '添加初始信息', '添加信息说明', $cap, 'acc-init-instructions', 'acc_init_instructions');
        add_submenu_page('acc-init-instructions', '添加产成品名称', '添加产成品名称', $cap, 'acc-add-goods', 'acc_add_goods');
        add_submenu_page('acc-init-instructions', '添加原材料名称', '添加原材料名称', $cap, 'acc-add-stuff', 'acc_add_stuff');
        add_submenu_page('acc-init-instructions', '添加仓库店面', '添加仓库店面', $cap, 'acc-add-place', 'acc_add_place');
        add_submenu_page('acc-init-instructions', '添加费用项目', '添加费用项目', $cap, 'acc-add-fee', 'acc_add_fee');
        add_submenu_page('acc-init-instructions', '添加货柜号', '添加货柜号', $cap, 'acc-add-container', 'acc_add_container');
        add_submenu_page('acc-init-instructions', '添加供应商', '添加供应商', $cap, 'acc-add-provider', 'acc_add_provider');
        add_submenu_page('acc-init-instructions', '临时更新件双数据', '临时更新件双数据', 'manage_options', 'temp-update-per-pack', 'temp_update_per_pack');
    }
}
add_action('admin_menu', 'acc_init');


function temp_update_per_pack() {
    include_once( 'temp-update-per-pack.php' );
}

function acc_init_instructions() {
    include_once( 'acc-init-instructions.php' );
}

function acc_add_goods() {
    include_once( 'add-goods.php' );
}

function acc_add_stuff() {
    include_once( 'add-stuff.php' );
}

function acc_add_fee() {
    include_once( 'add-fee.php' );
}

function acc_add_place() {
    include_once( 'add-place.php' );
}

function acc_add_container() {
    include_once( 'add-container.php' );
}

function acc_add_provider() {
    include_once( 'add-provider.php' );
}