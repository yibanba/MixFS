<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

mixfs_top('添加产成品信息', $_SESSION['acc_name']);

global $wpdb;

// 账套表名完整前缀 = fs_mixfs_xxx_ + goods_series
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

if (isset($_POST['btn_series_add'])) {              // 添加系列 
    $goods_series_name = preg_replace("/\s|　/", "", $_POST['goods_series_name']); //删除所有空格和全角空格
    $goods_series_name = wp_strip_all_tags($goods_series_name);
    $goods_series_detail = wp_strip_all_tags($_POST['goods_series_detail']);
    if (empty($goods_series_name)) {
        echo '<div id="message" class="updated"><p>产品系列名称不能为空</p></div>';
    } else {
        $series_exists = $wpdb->get_row("SELECT gs_name FROM {$acc_prefix}goods_series WHERE gs_name='{$goods_series_name}'");
        if ($series_exists) {
            echo '<div id="message" class="updated"><p>产品系列名称已存在，请重新命名后再次提交</p></div>';
        } else {
            $wpdb->insert(
                    $acc_prefix . 'goods_series', array('gs_name' => $goods_series_name, 'gs_summary' => $goods_series_detail)
            );
            echo "<div id='message' class='updated'><p>添加【{$goods_series_name}】系列名称成功</p></div>";
        }
    }
} elseif (isset($_POST['btn_goods_add']) ) {        // 添加产品
    $goods_name = preg_replace("/\s|　/", "", $_POST['goods_name']); //删除所有空格和全角空格
    $goods_name = wp_strip_all_tags($goods_name);
    $goods_detail = wp_strip_all_tags($_POST['goods_detail']);
    $per_pack = trim($_POST['goods_per_pack']);
    if (empty($goods_name)) {
        echo '<div id="message" class="updated"><p>产品名称不能为空</p></div>';
    } else {
        $goods_exists = $wpdb->get_row("SELECT gn_name FROM {$acc_prefix}goods_name WHERE gn_name='{$goods_name}'", ARRAY_N);
        if ($goods_exists) {
            echo '<div id="message" class="updated"><p>产品名称已存在，请重新命名后再次提交</p></div>';
        } else {
            $wpdb->insert(
                    $acc_prefix . 'goods_name', array(
                        'gn_gs_id'=>$_GET['series_id'],
                        'gn_name' => $goods_name,
                        'gn_price' => 1,
                        'gn_summary' => $goods_detail,
                        'gn_per_pack' => $per_pack)
            );
            echo "<div id='message' class='updated'><p>添加【{$goods_name}】产品名称成功</p></div>";
        }
    }
} elseif (isset ($_POST['btn_series_show'])) {          // 显示所有产品
    show_goods($acc_prefix);
} 

if ( $_GET['series_id'] > 0 ) {
    form_add_goods($acc_prefix, $_GET['series_id']);    // 添加指定系列产品
    show_goods($acc_prefix, $_GET['series_id']);        // 显示指定系列产品
} else {
    form_add_series($acc_prefix);                       // 添加系列名称
}

mixfs_bottom(); // 框架页面底部


function form_add_goods($acc_prefix, $series_id = '') { // 添加产品系列
    global $wpdb;

    $series_name = $wpdb->get_row("SELECT gs_name FROM {$acc_prefix}goods_series WHERE gs_id='{$series_id}'", ARRAY_A);
    $series_name = $series_name['gs_name'];
    
    echo <<<Mix_HTML
    <form action="" method="post">
        <div class="manage-menus">
            <div class="alignleft actions">
                <input type="text" id="goods_name" name="goods_name" value="输入【{$series_name}】系列的产品名称..." maxlength="30" size="30" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入【{$series_name}】系列的产品名称...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入【{$series_name}】系列的产品名称...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="text" id="goods_detail" name="goods_detail" value="输入备注..." maxlength="30" size="30" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入备注...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入备注...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
               <input type="text" id="goods_per_pack" name="goods_per_pack" value="输入每件双数..." maxlength="30" size="30" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入每件双数...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入每件双数...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="submit" name="btn_goods_add" id="btn_goods_add" class="button" value="添加产品名称"  />
                <input type="button" name="btn_series_return" id="btn_series_return" class="button" value="返回添加系列" 
                    onclick="location.href=location.href.substring(0, location.href.indexOf('&series_id'))" />
            </div>
            <br class="clear" />
        </div>
    </form>
Mix_HTML;
} // 添加产品系列


function show_goods($acc_prefix, $series_id = '') { // 显示产品名称列表，指定系列 or 全部系列
    global $wpdb;
    echo <<<Mix_HTML
    <br />
    <table class = "wp-list-table widefat fixed users" cellspacing = "1">
    <thead>
        <tr>
            <th class = 'manage-column' style = "">系列名称</th>
            <th class = 'manage-column' style = "width: 100px;">产品代码</th>
            <th class = 'manage-column' style = "">产品名称</th>
            <th class = 'manage-column' style = "">产品售价</th>
            <th class = 'manage-column' style = "">产品说明</th>
            <th class = 'manage-column' style = "">每件双数</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th class = 'manage-column' style = "">系列名称</th>
            <th class = 'manage-column' style = "width: 100px;">产品代码</th>
            <th class = 'manage-column' style = "">产品名称</th>
            <th class = 'manage-column' style = "">产品售价</th>
            <th class = 'manage-column' style = "">产品说明</th>
            <th class = 'manage-column' style = "">每件双数</th>
        </tr>
    </tfoot>
Mix_HTML;
    
    if( $series_id > 0 ) {
        $where = " gn_gs_id={$series_id} ";
        $orderby =  " ORDER BY gn_id DESC ";
    } else {
        $where = " 1=1 ";
        $orderby = " ORDER BY gn_gs_id, gn_id ";
    }
    $results_goods = $wpdb->get_results("SELECT gs_name, gn_id, gn_name, gn_price, gn_summary, gn_per_pack "
            . " FROM {$acc_prefix}goods_name, {$acc_prefix}goods_series "
            . " WHERE {$where} AND gn_gs_id=gs_id {$orderby} ", ARRAY_A);

    echo '<tbody >';
    foreach ($results_goods as $g_name) {
        echo "<tr class='alternate'>
                <td class='name'>{$g_name['gs_name']}</td>
                <td class='name'>{$g_name['gn_id']}</td>
                <td class='name'>{$g_name['gn_name']}</td>
                <td class='name'>{$g_name['gn_price']}</td>
                <td class='name'>{$g_name['gn_summary']}</td>
                <td class='name'>{$g_name['gn_per_pack']}</td>
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
                <input type="text" id="goods_series_name" name="goods_series_name" value="输入产成品系列(大类)名称..." maxlength="20" size="25" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入产成品系列(大类)名称...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入产成品系列(大类)名称...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="text" id="goods_series_detail" name="goods_series_detail" value="输入备注..." maxlength="20" size="25" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入备注...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入备注...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="submit" name="btn_series_add" id="btn_series_add" class="button" value="添加系列名称"  />
                <input type="submit" name="btn_series_show" id="btn_series_show" class="button" value="显示所有产品"  />
            </div>
            <br class="clear" />
        </div>
        <br />
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column'  style="">点击指定系列名称添加产品</th>
                    <th class='manage-column' style="width: 100px;">系列代码</th>
                    <th class='manage-column' style="">系列名称</th>
                    <th class='manage-column'  style="">系列说明</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th class='manage-column'  style="">点击指定系列名称添加产品</th>
                    <th class='manage-column' style="width: 100px;">系列代码</th>
                    <th class='manage-column' style="">系列名称</th>
                    <th class='manage-column'  style="">系列说明</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

    $results_series = $wpdb->get_results("SELECT * FROM {$acc_prefix}goods_series", ARRAY_A);

    foreach ($results_series as $s_name) {
        echo "<tr class='alternate'>
                <td class='name'>
                    <input type='button' name='add_item_btn' id='add_item_btn' class='button button-primary' style='width:200px;'  
                    onclick=\"javascript:location.href=location.href + '&series_id={$s_name['gs_id']}'\" value='添加 【{$s_name['gs_name']}】 系列产品'>
                </td>
                <td class='name'>{$s_name['gs_id']}</td>
                <td class='name'>{$s_name['gs_name']}</td>
                <td class='name'>{$s_name['gs_summary']}</td>
            </tr>";
    }
    echo '</tbody></table></form>';
} // 添加系列表单
