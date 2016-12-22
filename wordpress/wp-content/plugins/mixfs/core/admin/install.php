<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

function do_install_core() {
    core_install_tables();
    core_create_pages();
    core_add_roles();
}

function core_add_roles() {
    add_role('mixfs_manager',   'MixFS - 经理',     array('read' => true, 'level_0' => true));
    add_role('mixfs_auditor',   'MixFS - 稽核',     array('read' => true, 'level_0' => true));
    add_role('mixfs_operator',  'MixFS - 操作员',   array('read' => true, 'level_0' => true));
    add_role('mixfs_assistant', 'MixFS - 营业员',   array('read' => true, 'level_0' => true));
}

function core_create_pages() {
    $core_page_content = ''; // '<h3>米克斯财会软件</h3>';
    mixfs_create_page(esc_sql(_x('core', 'page_slug', 'mixfs')), 'mixfs_homepage_id_core', 'Mix Financial Softeware', $core_page_content);
}

/**
 * CREATE TABLE IF NOT EXISTS "{$wpdb->prefix}mixfs_{$tbl_name}"
 *  + ( Table_Schema )
 *  + COLLATE
 * ma == MixFS + Accounts
 */
function core_install_tables() {
    mixfs_table_install("accounts", "(
        `ma_id` int(11) NOT NULL AUTO_INCREMENT,
        `ma_tbl_prefix` varchar(10) NOT NULL,
        `ma_tbl_name` varchar(50) NOT NULL,
        `ma_tbl_detail` varchar(100) DEFAULT NULL,
        `ma_ID_permission` varchar(100) DEFAULT NULL,
        `ma_create_md5` varchar(32) NOT NULL,
        `ma_create_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `ma_update_date` datetime DEFAULT NULL,
        PRIMARY KEY (`ma_id`)
        )"
    );
}