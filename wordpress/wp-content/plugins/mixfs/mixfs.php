<?php

/**
 * Plugin Name: Mix Financial Softeware
 * Plugin URI: http://mixfs.com/wordpress/plugins/
 * Description: Mix Financial Softeware is a non-accounting professional software
 * Version: 0.1
 * Author: Victor
 * Author URI: http://www.yibanba.com/
 * Author E-mail: yibanba@hotmail.com
 */


if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Mix_PATH     = X:\xxx/mixfs/
 * Mix_URL      = http://xxx/mixfs/
 */
define(MixFS_PATH, plugin_dir_path(__FILE__));
define(MixFS_URL, plugins_url('/', __FILE__));

/**
 * Main MixFS Class
 */
final class MixFS {

    public static function instance() {

        static $instance = null;

        if (null === $instance) {
            $instance = new MixFS;

//            $instance->setup_globals();
//            $instance->includes();
//            $instance->setup_actions();
        }

        return $instance;
    }

    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        $this->includes();
        
        add_filter('template_include', array($this, 'template_loader'));
        add_filter('plugin_action_links', array($this, 'plugin_action_links'));
    }

    /**
     * 激活插件时的操作
     */
    public function activate() {
        update_option('mixfs_week_number', date("W", time()));     // 把"周数"作为种子，md5(table_name + week number)
        $this->install();
    }

    /**
     * 激活插件时的操作
     */
    public function deactivate() {
        $this->uninstall();
    }

    /**
     * 激活后插件页面显示链接: 软件设置 | 使用说明 | 停用 | 编辑
     */
    public function plugin_action_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=mixfs_settings') . '">软件设置</a>',
            '<a href="http://mixfs.com/wordpress/plugins/">使用说明</a>',
        );

        return array_merge($plugin_links, $links);
    }
    
    /**
     * 核心文件 core/
     */
    function includes() {
        $this->core_include();
        $this->modules_include();
    }
    
    function core_include() {
        include_once ( MixFS_PATH . 'core/functions.php');
        include_once ( MixFS_PATH . 'core/admin/init.php');
    }

    /**
     * 自动加载功能模快 modules/*
     * modules/mod_name/mod_name.php
     */
    function modules_include() {
        $mods_path = glob( MixFS_PATH . "modules/*", GLOB_ONLYDIR);
        foreach ($mods_path as $mp) {
            $fullpath = $mp . '/' . basename($mp) . '.php';
            if ( file_exists( $fullpath ) && is_readable( $fullpath ) ) {
                include_once( $fullpath );
            }
        }
    }
    
    /**
     * 安装核心功能：表、角色
     */
    function install() {
        include_once ( MixFS_PATH . 'core/admin/install.php');
        do_install_core();
    }
    
    /**
     * 安装核心功能：表、角色
     */
    function uninstall() {
        include_once ( MixFS_PATH . 'core/admin/uninstall.php');
        do_uninstall_core();
    }
    
    public function template_loader($template) {
        $find = array('mixfs.php');
        $file = '';
        if (is_page(get_option('mixfs_homepage_id_core'))) {
            $file = 'archive-core.php';
            $find[] = $file;
            $find[] = MixFS_PATH . 'core/templates/' . $file;
        }
        if ($file) {
            $template = locate_template($find);
            if (!$template)
                $template = Mix_PATH . 'core/templates/' . $file;
        }
        return $template;
    }

}

// class MixFS

function mixfs() {
    return MixFS::instance();
}

mixfs();
