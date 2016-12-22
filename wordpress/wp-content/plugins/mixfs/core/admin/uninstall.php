<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * 卸载插件时执行函数
 */

function do_uninstall_core() {
    core_remove_roles();
    core_remove_tables();
}

/**
 * 移除Mix角色，恢复Mix用户为订阅者角色(Subscriber)
 */
function core_remove_roles() {
    global $wpdb;

    $wpdb->query( 
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}usermeta "
            . " SET meta_value = '%s' WHERE meta_key = '%s' AND meta_value LIKE '%s'",
            serialize( ['subscriber'=>TRUE] ),              // a:1:{s:10:"subscriber";b:1;}
            "{$wpdb->prefix}capabilities",
            "%mixfs_%"
        )
    );
    
    remove_role('mixfs_manager');
    remove_role('mixfs_auditor');
    remove_role('mixfs_operator');
    remove_role('mixfs_assistant');
}

function core_remove_tables() {
    // do nothing
}