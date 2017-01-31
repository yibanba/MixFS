<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function acc_query() {
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null )
    //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' )
    global $current_user;
    switch ($current_user->roles[0]) {
        case 'mixfs_operator':
            $cap = 'mixfs_operator';
            break;
        case 'mixfs_manager':
            $cap = 'mixfs_manager';
            break;
        case 'mixfs_assistant':
            $cap = 'mixfs_assistant';
            break;
        default:
            $cap = 'manage_options';
            break;
    }

    if ($cap == 'manage_options' || $cap == 'mixfs_operator') {
        add_menu_page('', '明细查询', $cap, 'acc-query-instructions', 'acc_query_instructions', '', 130);

        add_submenu_page('acc-query-instructions', '现金明细汇总', '现金明细汇总', $cap, 'acc-query-instructions', 'acc_query_instructions');
        add_submenu_page('acc-query-instructions', '产成品查询', '产成品查询', $cap, 'goods-qry', 'goods_qry');
        add_submenu_page('acc-query-instructions', '费用明细查询', '费用明细查询', $cap, 'fee-qry', 'fee_qry');
        add_submenu_page('acc-query-instructions', '原材料查询', '原材料查询', $cap, 'stuff-qry', 'stuff_qry');
        add_submenu_page('acc-query-instructions', '产成品业务流水', '产成品业务流水', $cap, 'goods-serial-no', 'goods_serial_no');
        add_submenu_page('acc-query-instructions', '费用业务流水', '费用业务流水', $cap, 'fee-serial-no', 'fee_serial_no');
    } elseif ($cap == 'mixfs_manager') {
        add_menu_page('', '明细查询', $cap, 'acc-query-instructions', 'acc_query_instructions', '', 130);

        add_submenu_page('acc-query-instructions', '现金明细汇总', '现金明细汇总', $cap, 'acc-query-instructions', 'acc_query_instructions');
        add_submenu_page('acc-query-instructions', '产成品查询', '产成品查询', $cap, 'goods-qry', 'goods_qry');
        add_submenu_page('acc-query-instructions', '费用明细查询', '费用明细查询', $cap, 'fee-qry', 'fee_qry');
        add_submenu_page('acc-query-instructions', '原材料查询', '原材料查询', $cap, 'stuff-qry', 'stuff_qry');
        add_submenu_page('acc-query-instructions', '产成品业务流水', '产成品业务流水', $cap, 'goods-serial-no', 'goods_serial_no');
        add_submenu_page('acc-query-instructions', '费用业务流水', '费用业务流水', $cap, 'fee-serial-no', 'fee_serial_no');
    }
}

add_action('admin_menu', 'acc_query');

function acc_query_instructions() {
    include_once( 'acc-query-instructions.php' );
}

function goods_qry() {
    include_once( 'goods-qry.php' );
}

function fee_qry() {
    include_once( 'fee-qry.php' );
}

function stuff_qry() {
    include_once( 'stuff-qry.php' );
}

function goods_serial_no() {
    include_once( 'goods-serial-no.php' );
}

function fee_serial_no() {
    include_once( 'fee-serial-no.php' );
}
