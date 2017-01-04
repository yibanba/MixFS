<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

mixfs_top('添加费用项目', $_SESSION['acc_name']);

global $wpdb;

// 账套表名完整前缀 = fs_mixfs_xxx_ + fee_series
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

if (isset($_POST['btn_series_add'])) {              // 添加系列 
    $fee_series_name = preg_replace("/\s|　/", "", $_POST['fee_series_name']); //删除所有空格和全角空格
    $fee_series_name = wp_strip_all_tags($fee_series_name);
    $fee_series_detail = wp_strip_all_tags($_POST['fee_series_detail']);
    if (empty($fee_series_name)) {
        echo '<div id="message" class="updated"><p>费用分类名称不能为空</p></div>';
    } else {
        $series_exists = $wpdb->get_row("SELECT fs_name FROM {$acc_prefix}fee_series WHERE fs_name='{$fee_series_name}'");
        if ($series_exists) {
            echo '<div id="message" class="updated"><p>费用分类名称已存在，请重新命名后再次提交</p></div>';
        } else {
            $wpdb->insert(
                    $acc_prefix . 'fee_series', array('fs_name' => $fee_series_name, 'fs_summary' => $fee_series_detail)
            );
            echo "<div id='message' class='updated'><p>添加【{$fee_series_name}】费用分类名称成功</p></div>";
        }
    }
} elseif (isset($_POST['btn_item_add']) ) {        // 添加产品
    $item_name = preg_replace("/\s|　/", "", $_POST['item_name']); //删除所有空格和全角空格
    $item_name = wp_strip_all_tags($item_name);
    $item_in_out = intval($_POST['item_in_out']);
    $item_detail = wp_strip_all_tags($_POST['item_detail']);
    if (empty($item_name)) {
        echo '<div id="message" class="updated"><p>费用项目不能为空</p></div>';
    } else {
        $item_exists = $wpdb->get_row("SELECT fi_name FROM {$acc_prefix}fee_item WHERE fi_name='{$item_name}'", ARRAY_N);
        if ($item_exists) {
            echo '<div id="message" class="updated"><p>费用项目已存在，请重新命名后再次提交</p></div>';
        } else {
            $wpdb->insert(
                    $acc_prefix . 'fee_item', array(
                        'fi_fs_id'=>$_GET['series_id'],
                        'fi_name' => $item_name,
                        'fi_in_out' => $item_in_out,
                        'fi_summary' => $item_detail
                    )
            );
            echo "<div id='message' class='updated'><p>添加【{$item_name}】费用项目成功</p></div>";
        }
    }
} elseif (isset ($_POST['btn_series_show'])) {          // 显示所有产品
    show_item($acc_prefix);
} 

if ( $_GET['series_id'] > 0 ) {
    form_add_item($acc_prefix, $_GET['series_id']);    // 添加指定系列产品
    show_item($acc_prefix, $_GET['series_id']);        // 显示指定系列产品
} else {
    form_add_series($acc_prefix);                       // 添加系列名称
}

mixfs_bottom(); // 框架页面底部


function form_add_item($acc_prefix, $series_id = '') { // 添加产品系列
    global $wpdb;

    $series_name = $wpdb->get_row("SELECT fs_name FROM {$acc_prefix}fee_series WHERE fs_id='{$series_id}'", ARRAY_A);
    $series_name = $series_name['fs_name'];
    
    echo <<<Mix_HTML
    <form action="" method="post">
        <div class="manage-menus">
            <div class="alignleft actions">
                    <select name="item_in_out" id="item_in_out">
                    <option selected="selected" value="-1">减少</option>
                    <option value="1">增加</option>
                </select>
                <label for="item_in_out">账面现金 </label>
                <input type="text" id="item_name" name="item_name" value="输入【{$series_name}】费用分类名称..." maxlength="30" size="30" style="color: #ccc;" 
                       onblur="if (this.value == "') {
                                   this.value = '输入【{$series_name}】费用分类名称...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入【{$series_name}】费用分类名称...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="text" id="item_detail" name="item_detail" value="输入备注..." maxlength="30" size="30" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入备注...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入备注...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="submit" name="btn_item_add" id="btn_item_add" class="button" value="添加费用项目"  />
                <input type="button" name="btn_series_return" id="btn_series_return" class="button" value="返回费用分类" 
                    onclick="location.href=location.href.substring(0, location.href.indexOf('&series_id'))" />
            </div>
            <br class="clear" />
        </div>
    </form>
Mix_HTML;
} // 添加产品系列


function show_item($acc_prefix, $series_id = '') { // 显示产品名称列表，指定系列 or 全部系列
    global $wpdb;
    echo <<<Mix_HTML
    <br />
    <table class = "wp-list-table widefat fixed users" cellspacing = "1">
    <thead>
        <tr>
            <th class = 'manage-column' style = "">费用分类名称</th>
            <th class = 'manage-column' style = "width: 100px;">费用项目代码</th>
            <th class = 'manage-column' style = "">费用项目名称</th>
            <th class = 'manage-column' style = "">现金增减</th>
            <th class = 'manage-column' style = "">费用项目说明</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th class = 'manage-column' style = "">费用分类名称</th>
            <th class = 'manage-column' style = "width: 100px;">费用项目代码</th>
            <th class = 'manage-column' style = "">费用项目名称</th>
            <th class = 'manage-column' style = "">现金增减</th>
            <th class = 'manage-column' style = "">费用项目说明</th>
        </tr>
    </tfoot>
Mix_HTML;
    
    if( $series_id > 0 ) {
        $where = " fi_fs_id={$series_id} ";
        $orderby =  " ORDER BY fi_id DESC ";
    } else {
        $where = " 1=1 ";
        $orderby = " ORDER BY fi_fs_id, fi_id ";
    }
    $results_item = $wpdb->get_results("SELECT fs_name, fi_id, fi_name, fi_in_out, fi_summary "
            . " FROM {$acc_prefix}fee_item, {$acc_prefix}fee_series "
            . " WHERE {$where} AND fi_fs_id=fs_id {$orderby} ", ARRAY_A);

    echo '<tbody >';
    foreach ($results_item as $i_name) {
        $in_out = $i_name['fi_in_out'] > 0 ? '增加现金' : '减少现金';
        echo "<tr class='alternate'>
                <td class='name'>{$i_name['fs_name']}</td>
                <td class='name'>{$i_name['fi_id']}</td>
                <td class='name'>{$i_name['fi_name']}</td>
                <td class='name'>{$in_out}</td>
                <td class='name'>{$i_name['fi_summary']}</td>
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
                <input type="text" id="fee_series_name" name="fee_series_name" value="输入费用分类名称..." maxlength="20" size="25" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入费用分类名称...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入费用分类名称...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="text" id="fee_series_detail" name="fee_series_detail" value="输入备注..." maxlength="20" size="25" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入备注...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入备注...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="submit" name="btn_series_add" id="btn_series_add" class="button" value="添加费用分类名称"  />
                <input type="submit" name="btn_series_show" id="btn_series_show" class="button" value="显示所有费用项目"  />
            </div>
            <br class="clear" />
        </div>
        <br />
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column'  style="">点击指定费用分类添加项目</th>
                    <th class='manage-column' style="width: 100px;">费用分类代码</th>
                    <th class='manage-column' style="">费用分类</th>
                    <th class='manage-column'  style="">费用分类说明</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th class='manage-column'  style="">点击指定费用分类添加项目</th>
                    <th class='manage-column' style="width: 100px;">费用分类代码</th>
                    <th class='manage-column' style="">费用分类</th>
                    <th class='manage-column'  style="">费用分类说明</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

    $results_series = $wpdb->get_results("SELECT * FROM {$acc_prefix}fee_series", ARRAY_A);

    foreach ($results_series as $s_name) {
        echo "<tr class='alternate'>
                <td class='name'>
                    <input type='button' name='add_item_btn' id='add_item_btn' class='button button-primary' style='width:220px;'  
                    onclick=\"javascript:location.href=location.href + '&series_id={$s_name['fs_id']}'\" value='添加 【{$s_name['fs_name']}】 费用项目'>
                </td>
                <td class='name'>{$s_name['fs_id']}</td>
                <td class='name'>{$s_name['fs_name']}</td>
                <td class='name'>{$s_name['fs_summary']}</td>
            </tr>";
    }
    echo '</tbody></table></form>';
} // 添加系列表单
