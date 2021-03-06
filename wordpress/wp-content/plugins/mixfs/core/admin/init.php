<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

function mixfs_admin_menus() {
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

    if ($cap == 'manage_options') {
        add_menu_page('', '米克斯财会软件', $cap, 'mixfs-instructions', 'instructions', '', 110);
    
        add_submenu_page('mixfs-instructions', '进销存概况', '进销存概况', $cap, 'mixfs-instructions', 'instructions');
        add_submenu_page('mixfs-instructions', '财务软件入口', '财务软件入口', $cap, 'mixfs-entrance', 'entrance');
        add_submenu_page('mixfs-instructions', '账套列表', '账套列表', $cap, 'accounting-list', 'accounting_list');
        add_submenu_page('mixfs-instructions', '权限分配', '权限分配', $cap, 'accounting-permission', 'accounting_permission');
        add_submenu_page('mixfs-instructions', '登录日志', '登录日志', $cap, 'accounting-log', 'accounting_log');
    } elseif ($cap == 'mixfs_operator' || $cap == 'mixfs_manager' ) {
        add_menu_page('', '米克斯财会软件', $cap, 'mixfs-instructions', 'instructions');
        add_submenu_page('mixfs-instructions', '进销存概况', '进销存概况', $cap, 'mixfs-instructions', 'instructions');
        add_submenu_page('mixfs-instructions', '财务软件入口', '财务软件入口', $cap, 'mixfs-entrance', 'entrance');
    }
    
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

function accounting_log() {
    include_once( 'accounting-log.php' );
}


//******************************************************************************

/**
 * 登录后默认跳转至 财务入口页
 */
function mix_login_redirect($redirect_to, $request){
    if( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) {
        return admin_url("admin.php?page=mixfs-entrance");
    } else {
        return $redirect_to;
    }
}
add_filter("login_redirect", "mix_login_redirect", 10, 3);

/**
 * 去除升级提示
 */
function wp_hide_nag() {
    global $current_user;
    if($current_user->roles[0] != 'administrator') {
        remove_action( 'admin_notices', 'update_nag', 3 );
    }
}
add_action('admin_menu','wp_hide_nag'); 

function remove_admin_bar() {
    global $wp_admin_bar, $current_user;
    
    if($current_user->roles[0] != 'administrator') {
        $wp_admin_bar->remove_menu('wp-logo');      //移除Logo
        $wp_admin_bar->remove_menu('site-name');    //移除网站名称
        $wp_admin_bar->remove_node('dashboard');    //移除网站名称
    }
/**
 * 
    $wp_admin_bar->remove_menu('updates');      //移除升级通知
    $wp_admin_bar->remove_menu('comments');     //移除评论
    $wp_admin_bar->remove_menu('new-content');  // 移除“新建”
    $wp_admin_bar->remove_menu('my-sites');   //移除我的网站(多站点)
    $wp_admin_bar->remove_menu('search');     //移除搜索
    $wp_admin_bar->remove_menu('my-account'); //移除个人中心

    added_admin_bar();  // 添加自定义菜单
 * 
 */
}
add_action( 'wp_before_admin_bar_render', 'remove_admin_bar' );

function added_admin_bar() {
	global $wp_admin_bar;
	$wp_admin_bar->add_menu( array(
		'id'    => 'empty',
		'title' => ' ',
		'href'  => ''
	));
        $wp_admin_bar->add_menu( array(
		'id'    => 'validity',
		'title' => '服务器及数据库租用有效期',
		'href'  => ''
	));
	$wp_admin_bar->add_menu( array(
		'id'    => 'date',
		'title' => ' 2016-11-30 —— 2018-11-30  ',
		'href'  => '',
		'parent'=>'validity'
	));
}
add_action( 'wp_before_admin_bar_render', 'added_admin_bar' ); 

function remove_menu() {
    global $current_user;
    if($current_user->roles[0] != 'administrator') {
        remove_menu_page( 'index.php' );                  //Dashboard
/*        
        remove_menu_page( 'jetpack' );                    //Jetpack* 
        remove_menu_page( 'edit.php' );                   //Posts
        remove_menu_page( 'upload.php' );                 //Media
        remove_menu_page( 'edit.php?post_type=page' );    //Pages
        remove_menu_page( 'edit-comments.php' );          //Comments
        remove_menu_page( 'themes.php' );                 //Appearance
        remove_menu_page( 'plugins.php' );                //Plugins
        remove_menu_page( 'users.php' );                  //Users
        remove_menu_page( 'tools.php' );                  //Tools
        remove_menu_page( 'options-general.php' );        //Settings
 * 
 */
    }
}
add_action('admin_init','remove_menu');

function admin_footer_left_text($text) {
	$text = '<span id="footer-thankyou">米克斯财会软件 <a href="http://www.mixfs.com/">www.mixfs.com</a></span>'; 
	return $text;
}
add_filter('admin_footer_text', 'admin_footer_left_text'); 
function admin_footer_right_text($text) {
	// 右边信息
	$text = "QQ: 55517131 &nbsp; E-mail: sph999@hotmail.com";
	return $text;
}
add_filter('update_footer', 'admin_footer_right_text', 11); 
