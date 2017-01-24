<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


mixfs_top('现金明细汇总表', $_SESSION['acc_name']);

global $wpdb;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

isset($_SESSION['qry_date']['date1']) ?: $_SESSION['qry_date']['date1'] = date("Y-m-d", strtotime("-1 months"));
isset($_SESSION['qry_date']['date2']) ?: $_SESSION['qry_date']['date2'] = date("Y-m-d");


if(isset($_POST['btn_qry_cash'])) {
    
    $_SESSION['qry_date']['date1'] = $_POST['qry_date1'];
    $_SESSION['qry_date']['date2'] = $_POST['qry_date2'];
    
    form_qry_cash();
    echo "<div id='message' class='updated'><p>下表为 {$_SESSION['qry_date']['date1']} —— {$_SESSION['qry_date']['date2']} 期间现金汇总, 前期余额为 {$_SESSION['qry_date']['date1']} 日之前的该项目余额</p></div>";

    cash_total($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2']);
            
} else {
    
    form_qry_cash();
    
} // $_REQUES Processing is complete

mixfs_bottom(); // 框架页面底部
//******************************************************************************



function form_qry_cash() {
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
                <input type="submit" name="btn_qry_cash" id="btn_qry_cash" class="button button-primary" value="现金明细汇总查询"  />
            </div>

            <br class="clear" />
        </div>
        <br />
    </form>
    <?php
} // function form_qry_fee()


/**
 *
 * 计算现金余额：资金来源（产成品、原材料、借款、赊销返款） - 资金运用（费用、还款）
 * @return type  计算 (销售总额，借贷净值，费用总额)
 */

function cash_total($acc_prefix, $startday, $endday) {
    global $wpdb;
    // 1、产品销售
    $sql_goods = "SELECT SUM( if(gb_date < '{$startday}' && gb_money <> 0, gb_money, 0) ) ,
                    SUM( if(gb_date <= '{$endday}' && gb_money <> 0, gb_money, 0) ),
                    SUM( if(gb_date >= '{$startday}' && gb_date <= '{$endday}' && gb_money < 0, gb_money, 0) )
                FROM {$acc_prefix}goods_biz";
    $r_goods = $wpdb->get_row($sql_goods, ARRAY_N);
    if (count($r_goods) > 0) {
        list($goods_prior, $goods_current, $goods_return) = $r_goods;
    }
    // 2、原料销售
    $sql_stuff = "SELECT SUM( if(sb_date < '{$startday}' && sb_out <> 0 && sb_money <> 0, sb_money, 0) ) ,
                      SUM( if(sb_date <= '{$endday}' && sb_out <> 0 && sb_money <> 0, sb_money, 0) ),
                      SUM( if(sb_date >= '{$startday}' && sb_date <= '{$endday}'  && sb_out < 0 && sb_money < 0, sb_money, 0) )
                FROM {$acc_prefix}stuff_biz";
    $r_stuff = $wpdb->get_row($sql_stuff, ARRAY_N);
    if (count($r_stuff) > 0) {
        list($stuff_prior, $stuff_current, $stuff_return) = $r_stuff;
    }

    // 3、资金来源 - 资金运用
    $sql_fee = "SELECT fs_id, fs_name, fi_id, fi_name, fi_summary, fi_in_out, 
                    SUM( if(fb_date < '{$startday}', fb_in, 0) ),
                    SUM( if(fb_date < '{$startday}', fb_out, 0) ),
                    SUM( if(fb_date >= '{$startday}' && fb_date <= '{$endday}', fb_in, 0) ),
                    SUM( if(fb_date >= '{$startday}' && fb_date <= '{$endday}', fb_out, 0) )                    
                FROM {$acc_prefix}fee_biz, {$acc_prefix}fee_item, {$acc_prefix}fee_series
                WHERE fb_fi_id = fi_id AND fi_fs_id = fs_id
                GROUP BY fi_id
                ORDER BY fs_name, fi_id";
    $r_fee = $wpdb->get_results($sql_fee, ARRAY_N);

    echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="">序号</th>
                    <th class='manage-column'  style="">总分类</th>
                    <th class='manage-column'  style="">子项目</th>
                    <th class='manage-column' style="">现金增减</th>
                    <th class='manage-column' style="">前期余额</th>
                    <th class='manage-column' style="">现金增加</th>
                    <th class='manage-column'  style="">现金减少</th>
                    <th class='manage-column'  style="">本期余额</th>
                    <th class='manage-column'  style="">项目说明</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class='manage-column' style="">序号</th>
                    <th class='manage-column'  style="">总分类</th>
                    <th class='manage-column'  style="">子项目</th>
                    <th class='manage-column' style="">现金增减</th>
                    <th class='manage-column' style="">前期余额</th>
                    <th class='manage-column' style="">现金增加</th>
                    <th class='manage-column'  style="">现金减少</th>
                    <th class='manage-column'  style="">本期余额</th>
                    <th class='manage-column'  style="">项目说明</th>
                </tr>
            </tfoot>
            <tbody>
Form_HTML;

    echo "<tr class='alternate'>
                    <td class='name'>1</td>
                    <td class='name' colspan='2'>产成品销售</td>
                    <td class='name'>十</td>
                    <td class='name'>" . mix_num($goods_prior, 2) . "</td>
                    <td class='name'>" . mix_num(($goods_current - $goods_prior - $goods_return), 2) . "</td>
                    <td class='name'>" . mix_num(-1.00 * $goods_return, 2) . "</td>
                    <td class='name'>" . mix_num($goods_current, 2) . "</td>
                    <td class='name'>现金减少为销售退回</td>
                </tr>";
    echo "<tr class='alternate'>
                    <td class='name'>2</td>
                    <td class='name' colspan='2'>原材料销售</td>
                    <td class='name'>十</td>
                    <td class='name'>" . mix_num($stuff_prior, 2) . "</td>
                    <td class='name'>" . mix_num(($stuff_current - $stuff_prior - $stuff_return), 2) . "</td>
                    <td class='name'>" . mix_num(-1.00 * $stuff_return , 2) . "</td>
                    <td class='name'>" . mix_num($stuff_current, 2) . "</td>
                    <td class='name'>现金减少为销售退回</td>
                </tr>";

    $counter = 2; // 产品和原材料占用 2 行
    $fee_current = 0;
    if (count($r_fee) > 0) {
        $pre_fee = 0;
        foreach ($r_fee as $fields) {
            $counter++;
            if ($fields[0] == $pre_fee) {
                echo "<tr class='alternate'>
                        <td class='name'>{$counter}</td>
                        <td class='name'> ... </td>
                        <td class='name'>{$fields[3]}</td>";
            } else {
                $pre_fee = $fields[0];
                echo "<tr class='alternate'>
                        <td class='name'>{$counter}</td>
                        <td class='name'>{$fields[1]}</td>
                        <td class='name'>{$fields[3]}</td>";
            }

            // fb_in, fb_out不应该同时有金额，所以相加$fields[4] + $fields[5]求前期余额
            echo ($fields[5] == 1) ? "<td class='name'>十</td>" : "<td class='name'> &nbsp; —</td>";
            
            // 差 2 个字段

            echo  "<td class='name'>" .mix_num(abs($fields[6] - $fields[7]), 2) . "</td>
                   <td class='name'>" . mix_num($fields[8], 2) . "</td>
                   <td class='name'>" . mix_num($fields[9], 2) . "</td>
                   <td class='name'>" . mix_num(abs($fields[6] - $fields[7] + $fields[8] - $fields[9]), 2) . "</td>
                   <td class='name'>{$fields[10]}</td></tr>";

            $fee_current += ($fields[4] - $fields[5] + $fields[6] - $fields[7]);
        }
        echo "</tbody></table>";
    }


    $sales_total = mix_num($goods_current + $stuff_current, 2);
    $fee_total = mix_num(abs($fee_current), 2);
    $balance = mix_num($goods_current + $stuff_current + $fee_current, 2);
    
    echo <<<Form_HTML
        <br />
    <table class="wp-list-table widefat fixed users" cellspacing="1">
    <thead>
        <tr>
            <th class='manage-column' style="width:150px;">销售收入总额: </th>
            <th class='manage-column' style="">{$sales_total}</th>
            <th class='manage-column'  style="width:150px;">本期现金减少总额: </th>
            <th class='manage-column'  style="">{$fee_total}</th>
            <th class='manage-column'  style="width:150px;">本期现金收支净额($): </th>
            <th class='manage-column'  style="">{$balance}</th>
        </tr>
    </thead>
Form_HTML;
            
    echo '</table><br />';
    echo "<div id='message' class='updated'><p>汇总表公式： ( 产品销售 + 原料销售 + 赊销返款 ) — ( 投资总额 + 周转借款 - 费用总额 ) = 当前现金余额</p></div>";

} // function cash_total
