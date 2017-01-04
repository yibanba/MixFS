<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

mixfs_top('添加原材料信息', $_SESSION['acc_name']);

global $wpdb;

// 账套表名完整前缀 = fs_mixfs_xxx_ + stuff_series
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

if (isset($_POST['btn_series_add'])) {              // 添加系列 
    $stuff_series_name = preg_replace("/\s|　/", "", $_POST['stuff_series_name']); //删除所有空格和全角空格
    $stuff_series_name = wp_strip_all_tags($stuff_series_name);
    $stuff_series_detail = wp_strip_all_tags($_POST['stuff_series_detail']);
    if (empty($stuff_series_name)) {
        echo '<div id="message" class="updated"><p>原材料系列名称不能为空</p></div>';
    } else {
        $series_exists = $wpdb->get_row("SELECT ss_name FROM {$acc_prefix}stuff_series WHERE ss_name='{$stuff_series_name}'");
        if ($series_exists) {
            echo '<div id="message" class="updated"><p>产品系列名称已存在，请重新命名后再次提交</p></div>';
        } else {
            $wpdb->insert(
                    $acc_prefix . 'stuff_series', array('ss_name' => $stuff_series_name, 'ss_summary' => $stuff_series_detail)
            );
            echo "<div id='message' class='updated'><p>添加【{$stuff_series_name}】系列名称成功</p></div>";
        }
    }
} elseif (isset($_POST['btn_stuff_add']) ) {        // 添加产品
    $stuff_name = preg_replace("/\s|　/", "", $_POST['stuff_name']); //删除所有空格和全角空格
    $stuff_name = wp_strip_all_tags($stuff_name);
    $stuff_detail = wp_strip_all_tags($_POST['stuff_detail']);
    if (empty($stuff_name)) {
        echo '<div id="message" class="updated"><p>原材料名称不能为空</p></div>';
    } else {
        $stuff_exists = $wpdb->get_row("SELECT sn_name FROM {$acc_prefix}stuff_name WHERE sn_name='{$stuff_name}'", ARRAY_N);
        if ($stuff_exists) {
            echo '<div id="message" class="updated"><p>原材料名称已存在，请重新命名后再次提交</p></div>';
        } else {
            $wpdb->insert(
                    $acc_prefix . 'stuff_name', array(
                        'sn_ss_id'=>$_GET['series_id'],
                        'sn_name' => $stuff_name,
                        'sn_price' => 1,
                        'sn_summary' => $stuff_detail)
            );
            echo "<div id='message' class='updated'><p>添加【{$stuff_name}】原材料名称成功</p></div>";
        }
    }
} elseif (isset ($_POST['btn_series_show'])) {          // 显示所有产品
    show_stuff($acc_prefix);
} 

if ( $_GET['series_id'] > 0 ) {
    form_add_stuff($acc_prefix, $_GET['series_id']);    // 添加指定系列产品
    show_stuff($acc_prefix, $_GET['series_id']);        // 显示指定系列产品
} else {
    form_add_series($acc_prefix);                       // 添加系列名称
}

mixfs_bottom(); // 框架页面底部


function form_add_stuff($acc_prefix, $series_id = '') { // 添加产品系列
    global $wpdb;

    $series_name = $wpdb->get_row("SELECT ss_name FROM {$acc_prefix}stuff_series WHERE ss_id='{$series_id}'", ARRAY_A);
    $series_name = $series_name['ss_name'];
    
    echo <<<Mix_HTML
    <form action="" method="post">
        <div class="manage-menus">
            <div class="alignleft actions">
                <input type="text" id="stuff_name" name="stuff_name" value="输入【{$series_name}】系列的原材料名称..." maxlength="30" size="30" style="color: #ccc;" 
                       onblur="if (this.value == "') {
                                   this.value = '输入【{$series_name}】系列的原材料名称...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入【{$series_name}】系列的原材料名称...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="text" id="stuff_detail" name="stuff_detail" value="输入备注..." maxlength="30" size="30" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入备注...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入备注...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="submit" name="btn_stuff_add" id="btn_stuff_add" class="button" value="添加原材料名称"  />
                <input type="button" name="btn_series_return" id="btn_series_return" class="button" value="返回原材料系列" 
                    onclick="location.href=location.href.substring(0, location.href.indexOf('&series_id'))" />
            </div>
            <br class="clear" />
        </div>
    </form>
Mix_HTML;
} // 添加产品系列


function show_stuff($acc_prefix, $series_id = '') { // 显示产品名称列表，指定系列 or 全部系列
    global $wpdb;
    echo <<<Mix_HTML
    <br />
    <table class = "wp-list-table widefat fixed users" cellspacing = "1">
    <thead>
        <tr>
            <th class = 'manage-column' style = "">原材料系列名称</th>
            <th class = 'manage-column' style = "width: 100px;">原材料代码</th>
            <th class = 'manage-column' style = "">原材料名称</th>
            <th class = 'manage-column' style = "">原材料售价</th>
            <th class = 'manage-column' style = "">原材料说明</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th class = 'manage-column' style = "">系列名称</th>
            <th class = 'manage-column' style = "width: 100px;">产品代码</th>
            <th class = 'manage-column' style = "">产品名称</th>
            <th class = 'manage-column' style = "">产品售价</th>
            <th class = 'manage-column' style = "">产品说明</th>
        </tr>
    </tfoot>
Mix_HTML;
    
    if( $series_id > 0 ) {
        $where = " sn_ss_id={$series_id} ";
        $orderby =  " ORDER BY sn_id DESC ";
    } else {
        $where = " 1=1 ";
        $orderby = " ORDER BY sn_ss_id, sn_id ";
    }
    $results_stuff = $wpdb->get_results("SELECT ss_name, sn_id, sn_name, sn_price, sn_summary "
            . " FROM {$acc_prefix}stuff_name, {$acc_prefix}stuff_series "
            . " WHERE {$where} AND sn_ss_id=ss_id {$orderby} ", ARRAY_A);

    echo '<tbody >';
    foreach ($results_stuff as $ss_name) {
        echo "<tr class='alternate'>
                <td class='name'>{$ss_name['ss_name']}</td>
                <td class='name'>{$ss_name['sn_id']}</td>
                <td class='name'>{$ss_name['sn_name']}</td>
                <td class='name'>{$ss_name['sn_price']}</td>
                <td class='name'>{$ss_name['sn_summary']}</td>
            </tr>";
    }
    echo '</tbody>'
    . '</table>';
} // 显示产品


function form_add_series($acc_prefix) { // 添加系列表单
    global $wpdb;
    echo <<<Form_HTML
    <form action="" method="post">
        <div class="manage-menus">

            <div class="alignleft actions">
                <input type="text" id="stuff_series_name" name="stuff_series_name" value="输入原材料系列(大类)名称..." maxlength="20" size="25" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入原材料系列(大类)名称...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入原材料系列(大类)名称...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="text" id="stuff_series_detail" name="stuff_series_detail" value="输入备注..." maxlength="20" size="25" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入备注...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入备注...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="submit" name="btn_series_add" id="btn_series_add" class="button" value="添加原材料系列名称"  />
                <input type="submit" name="btn_series_show" id="btn_series_show" class="button" value="显示所有原材料名称"  />
            </div>
            <br class="clear" />
        </div>
        <br />
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column'  style="">点击指定系列名称添加原材料</th>
                    <th class='manage-column' style="width: 100px;">原材料系列代码</th>
                    <th class='manage-column' style="">原材料系列名称</th>
                    <th class='manage-column'  style="">原材料系列说明</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th class='manage-column'  style="">点击指定系列名称添加原材料</th>
                    <th class='manage-column' style="width: 100px;">原材料系列代码</th>
                    <th class='manage-column' style="">原材料系列名称</th>
                    <th class='manage-column'  style="">原材料系列说明</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

    $results_series = $wpdb->get_results("SELECT * FROM {$acc_prefix}stuff_series", ARRAY_A);

    foreach ($results_series as $s_name) {
        echo "<tr class='alternate'>
                <td class='name'>
                    <input type='button' name='add_item_btn' id='add_item_btn' class='button button-primary' style='width:200px;'  
                    onclick=\"javascript:location.href=location.href + '&series_id={$s_name['ss_id']}'\" value='添加 【{$s_name['ss_name']}】 系列原材料'>
                </td>
                <td class='name'>{$s_name['ss_id']}</td>
                <td class='name'>{$s_name['ss_name']}</td>
                <td class='name'>{$s_name['ss_summary']}</td>
            </tr>";
    }
    echo '</tbody></table></form>';
} // 添加系列表单
