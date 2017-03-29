<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

function acc_biz() {
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null )
    //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' )
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
        add_menu_page('', '业务处理', $cap, 'acc-biz-instructions', 'acc_biz_instructions', '', 120);

        add_submenu_page('acc-biz-instructions', '业务处理', '业务处理', $cap, 'acc-biz-instructions', 'acc_biz_instructions');
        add_submenu_page('acc-biz-instructions', '产成品业务', '产成品业务', $cap, 'goods-biz', 'goods_biz');
        add_submenu_page('acc-biz-instructions', '产成品订单业务', '产成品订单业务', $cap, 'goods-biz-order', 'goods_biz_order');
        add_submenu_page('acc-biz-instructions', '费用业务', '费用业务', $cap, 'fee-biz', 'fee_biz');
        add_submenu_page('acc-biz-instructions', '原材料业务', '原材料业务', $cap, 'stuff-biz', 'stuff_biz');
    }
}

add_action('admin_menu', 'acc_biz');

function acc_biz_instructions() {
    include_once( 'acc-biz-instructions.php' );
}

function goods_biz() {
    include_once( 'goods-biz.php' );
}

function goods_biz_order() {
    include_once( 'goods-biz-order.php' );
}

function fee_biz() {
    include_once( 'fee-biz.php' );
}

function stuff_biz() {
    include_once( 'stuff-biz.php' );
}
