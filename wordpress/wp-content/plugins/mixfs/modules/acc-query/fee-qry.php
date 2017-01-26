<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

mixfs_top('费用明细查询', $_SESSION['acc_name']);

global $wpdb;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

isset($_SESSION['qry_date']['date1']) ?: $_SESSION['qry_date']['date1'] = date("Y-m-d", strtotime("-1 months"));
isset($_SESSION['qry_date']['date2']) ?: $_SESSION['qry_date']['date2'] = date("Y-m-d");

if(isset($_GET['fs_id'])) {
    $fs_name = $wpdb->get_var("SELECT fs_name FROM {$acc_prefix}fee_series WHERE fs_id={$_GET['fs_id']}");
    echo '<div class="manage-menus">
            <div class="alignleft actions">
            【 ' . $fs_name . ' 】 '
    . $_SESSION['qry_date']['date1'] . ' —— ' . $_SESSION['qry_date']['date2']
    . ' 期间业务明细
            </div>
            <div class="alignright actions">
    <input type="button" name="btn_return" id="btn_return" class="button" value="返回上级查询" onclick="history.back();" />
            </div>
        </div>
        <br />';
    qry_fee_detail($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2'], $_GET['fs_id'], 0);
    
} elseif(isset($_GET['fi_id'])) {
    
    $fi_name = $wpdb->get_var("SELECT fi_name FROM {$acc_prefix}fee_item WHERE fi_id={$_GET['fi_id']}");
    echo '<div class="manage-menus">
            <div class="alignleft actions">
            【 ' . $fi_name . ' 】 '
    . $_SESSION['qry_date']['date1'] . ' —— ' . $_SESSION['qry_date']['date2']
    . ' 期间业务明细
            </div>
            <div class="alignright actions">
    <input type="button" name="btn_return" id="btn_return" class="button" value="返回上级查询" onclick="history.back();" />
            </div>
        </div>
        <br />';
        qry_fee_detail($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2'], 0, $_GET['fi_id']);

} elseif(isset ($_POST['btn_qry_item'])) {
    
    $_SESSION['qry_date']['date1'] = $_POST['qry_date1'];
    $_SESSION['qry_date']['date2'] = $_POST['qry_date2'];
    
    form_qry_fee();
    echo "<div id='message' class='updated'><p>下表为 {$_SESSION['qry_date']['date1']} —— {$_SESSION['qry_date']['date2']} 期间所有费用项目汇总, 前期余额为 {$_SESSION['qry_date']['date1']} 日之前的该项目余额</p></div>";
    qry_fee_total($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2']);
    
} else {
    
    form_qry_fee();
    
} // $_REQUES Processing is complete


mixfs_bottom(); // 框架页面底部
//******************************************************************************

function form_qry_fee() {
    ?>
    <form action="" method="post">
        <div class="manage-menus">
            <div class="alignleft actions" id="sale_inventory">
                <label for="qry_date1">指定起始日期
                    <input name="qry_date1" type="text" id="qry_date1" value="<?php echo $_SESSION['qry_date']['date1']; ?>">
                </label>
                <label for="qry_date2">指定截止日期
                    <input name="qry_date2" type="text" id="qry_date2" value="<?php echo $_SESSION['qry_date']['date2']; ?>">
                </label>
    <?php
    date_from_to("qry_date1", "qry_date2");
    ?>
                <input type="submit" name="btn_qry_item" id="btn_qry_item" class="button button-primary" value="费用项目汇总查询"  />
                <input type="submit" name="btn_qry_container" id="btn_qry_container" class="button button-primary" value="货柜相关费用查询"  />
            </div>

            <br class="clear" />
        </div>
        <br />
    </form>
    <?php
} // function form_qry_fee()


function qry_fee_detail($acc_prefix, $startday, $endday, $fs_id=0, $fi_id=0) {
    global $wpdb;
    
    if($fs_id > 0 && $fi_id == 0) {
        $where = " fi_fs_id = {$fs_id} ";
        $name = id2name('fs_name', "{$acc_prefix}fee_series", $fs_id, "fs_id");
    } elseif ($fs_id == 0 && $fi_id > 0) {
        $where = " fb_fi_id = {$fi_id} ";
        $name = id2name('fi_name', "{$acc_prefix}fee_item", $fi_id, "fi_id");
    }
    

    $sql = "SELECT fb_id, fb_date, fs_name, fi_name, fb_in, fb_out, fb_summary
                FROM {$acc_prefix}fee_biz, {$acc_prefix}fee_item,  {$acc_prefix}fee_series
                WHERE fb_date BETWEEN  '{$startday}' AND  '{$endday}' 
                       AND {$where} AND fb_fi_id = fi_id AND fi_fs_id = fs_id
                ORDER BY fs_order, fi_order";
    $fee_items = $wpdb->get_results($sql, ARRAY_N);

    if (count($fee_items) > 0) {
        echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column'  style="">日期</th>
                    <th class='manage-column'  style="">费用系列</th>
                    <th class='manage-column' style="">费用项目</th>
                    <th class='manage-column' style="">现金增加</th>
                    <th class='manage-column'  style="">现金减少</th>
                    <th class='manage-column'  style="">项目说明</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column'  style="">日期</th>
                    <th class='manage-column'  style="">费用系列</th>
                    <th class='manage-column' style="">费用项目</th>
                    <th class='manage-column' style="">现金增加</th>
                    <th class='manage-column'  style="">现金减少</th>
                    <th class='manage-column'  style="">项目说明</th>
                </tr>
            </tfoot>
            <tbody>
Form_HTML;

        $fb_in = 0;
        $fb_out = 0;
        foreach ($fee_items as $fi) {
            $fb_in += $fi[4];
            $fb_out += $fi[5];
            echo "<tr class='alternate'>
                    <td class='name'>{$fi[0]}</td>
                    <td class='name'>{$fi[1]}</td>
                    <td class='name'>{$fi[2]}</td>
                    <td class='name'>{$fi[3]}</td>
                    <td class='name'>" . mix_num($fi[4], 2) . "</td>
                    <td class='name'>" . mix_num($fi[5], 2) . "</td>
                    <td class='name'>{$fi[6]}</td>
                </tr>";
        } // foreach
        echo '</tbody></table>';
        
        $fbt_in = mix_num($fb_in, TRUE);
        $fbt_out = mix_num($fb_out, TRUE);
        $balance = mix_num(($fb_in - $fb_out), TRUE);
        echo <<<Form_HTML
        <br />
    <table class="wp-list-table widefat fixed users" cellspacing="1">
    <thead>
        <tr>
            <th class='manage-column' style="width:150px;">【 {$name} 】</th>
            <th class='manage-column' style="width:150px;">本期现金增加总额: </th>
            <th class='manage-column' style="">{$fbt_in}</th>
            <th class='manage-column'  style="width:150px;">本期现金减少总额: </th>
            <th class='manage-column'  style="">{$fbt_out}</th>
            <th class='manage-column'  style="width:150px;">本期现金收支净额($): </th>
            <th class='manage-column'  style="">{$balance}</th>
        </tr>
    </thead>
Form_HTML;
    }
    echo '</table><br />';
        
}
/**
 * 费用汇总查询
 */

function qry_fee_total($acc_prefix, $startday, $endday) {

    global $wpdb;
    $sql = "SELECT fs_id, fs_name, fi_id, fi_name, fi_summary,
                SUM( if(fb_date < '{$startday}', fb_in, 0) ),
                SUM( if(fb_date < '{$startday}', fb_out, 0) ),
                SUM( if(fb_date >= '{$startday}' && fb_date <= '{$endday}', fb_in, 0) ),
                SUM( if(fb_date >= '{$startday}' && fb_date <= '{$endday}', fb_out, 0) )
            FROM {$acc_prefix}fee_biz, {$acc_prefix}fee_item,  {$acc_prefix}fee_series
            WHERE fb_fi_id = fi_id AND fi_fs_id = fs_id
            GROUP BY fi_id
            ORDER BY fs_name, fi_id";

    $items = $wpdb->get_results($sql, ARRAY_N);

//*******************************

    if (count($items)) {
        echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="">总分类</th>
                    <th class='manage-column'  style="">明细项目</th>
                    <th class='manage-column'  style="">前期余额</th>
                    <th class='manage-column' style="">本期增加</th>
                    <th class='manage-column' style="">本期减少</th>
                    <th class='manage-column'  style="">本期余额</th>
                    <th class='manage-column'  style="">项目说明</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class='manage-column' style="">总分类</th>
                    <th class='manage-column'  style="">明细项目</th>
                    <th class='manage-column'  style="">前期余额</th>
                    <th class='manage-column' style="">本期增加</th>
                    <th class='manage-column' style="">本期减少</th>
                    <th class='manage-column'  style="">本期余额</th>
                    <th class='manage-column'  style="">项目说明</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

        $curent_in = 0;     // 本期增加累计变量
        $curent_out = 0;    // 本期减少累计变量
        $pre_fee = 0;
        foreach ($items as $item) {
            $url_fee = "onclick=\"javascript:location.href=location.href + '&fs_id={$item[0]}'\"";
            $url_item = "onclick=\"javascript:location.href=location.href + '&fi_id={$item[2]}'\"";
            if ($item[0] == $pre_fee) {
                echo "<tr class='alternate'><td class='name'> ... </td>";
            } else {
                $pre_fee = $item[0];
                echo "<tr class='alternate'><td class='name'><span {$url_fee}>{$item[1]}</span></td>";
            }
            
            // fb_in, fb_out不应该同时有金额，所以相加$row[4] + $row[5]求前期余额
            echo "<td class='name'><span {$url_item}>{$item[3]}</span></td>
                <td class='name'>" . mix_num(($item[5] + $item[6]), 2) . "</td>
                <td class='name'>" . mix_num($item[7], 2) . "</td>
                <td class='name'>" . mix_num($item[8], 2) . "</td>";

            $curent_in += $item[7];
            $curent_out += $item[8];
            echo "<td>" . mix_num(($item[5] + $item[6] + $item[7] + $item[8]), 2) . "</td>
                <td class='name'>{$item[4]}</td></tr>";
        } // foreach
        echo '</tbody></table>';

        $cur_in = mix_num($curent_in, TRUE);
        $cur_out = mix_num($curent_out, TRUE);
        $balance = mix_num(($curent_in - $curent_out), TRUE);
        echo <<<Form_HTML
        <br />
    <table class="wp-list-table widefat fixed users" cellspacing="1">
    <thead>
        <tr>
            <th class='manage-column' style="width:150px;">本期现金增加总额: </th>
            <th class='manage-column' style="">{$cur_in}</th>
            <th class='manage-column'  style="width:150px;">本期现金减少总额: </th>
            <th class='manage-column'  style="">{$cur_out}</th>
            <th class='manage-column'  style="width:150px;">本期现金收支净额($): </th>
            <th class='manage-column'  style="">{$balance}</th>
        </tr>
    </thead>
Form_HTML;
    }
        echo '</table><br />';

}

