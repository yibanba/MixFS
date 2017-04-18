<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


mixfs_top('现金明细汇总表', $_SESSION['acc_name']);

global $wpdb;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

isset($_SESSION['qry_date']['date1']) ?: $_SESSION['qry_date']['date1'] = date("Y-m-d", strtotime("-1 months"));
isset($_SESSION['qry_date']['date2']) ?: $_SESSION['qry_date']['date2'] = date("Y-m-d");


if (isset($_POST['btn_qry_cash'])) {

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
}

// function form_qry_fee()

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
                ORDER BY fs_id";
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
                    <th class='manage-column' style="">本期现金增加</th>
                    <th class='manage-column'  style="">本期现金减少</th>
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
                    <th class='manage-column' style="">本期现金增加</th>
                    <th class='manage-column'  style="">本期现金减少</th>
                    <th class='manage-column'  style="">本期余额</th>
                    <th class='manage-column'  style="">项目说明</th>
                </tr>
            </tfoot>
            <tbody>
Form_HTML;

    echo "<tr class='alternate'>
                    <td class='name'>1</td>
                    <td class='name' colspan='2'>产成品销售</td>
                    <td class='name'>+</td>
                    <td class='name'>" . mix_num($goods_prior, 2) . "</td>
                    <td class='name'>" . mix_num(($goods_current - $goods_prior + (-$goods_return)), 2) . "</td>
                    <td class='name'>" . mix_num(-1 * $goods_return, 2) . "</td>
                    <td class='name'>" . mix_num($goods_current, 2) . "</td>
                    <td class='name'>现金减少为销售退回</td>
                </tr>";
    echo "<tr class='alternate'>
                    <td class='name'>2</td>
                    <td class='name' colspan='2'>原材料销售</td>
                    <td class='name'>+</td>
                    <td class='name'>" . mix_num($stuff_prior, 2) . "</td>
                    <td class='name'>" . mix_num(($stuff_current - $stuff_prior + (-$stuff_return)), 2) . "</td>
                    <td class='name'>" . mix_num(-1 * $stuff_return, 2) . "</td>
                    <td class='name'>" . mix_num($stuff_current, 2) . "</td>
                    <td class='name'>现金减少为销售退回</td>
                </tr>";

    /**
     * 产成品 和 原材料
     * $pre_balance = 前期余额，$cur_breakeven = 当期盈亏
     */
    $pre_balance = $goods_prior + $stuff_prior;
    $cur_breakeven = ($goods_current - $goods_prior) + ($stuff_current - $stuff_prior);

    $counter = 2;           // 产品和原材料占用 2 行

    if (count($r_fee) > 0) {
        $pre_fee = 0; // 判断是否和前一个费用系列id相等
        foreach ($r_fee as $fields) {
            $url_fee = "onclick=\"javascript:location.href=location.href.substring(0, location.href.indexOf('?page')) + '?page=fee-qry&fs_id={$fields[0]}'\"";
            $url_item = "onclick=\"javascript:location.href=location.href.substring(0, location.href.indexOf('?page')) + '?page=fee-qry&fi_id={$fields[2]}'\"";
            $counter++;
            if ($fields[0] == $pre_fee) {
                echo "<tr class='alternate'>
                        <td class='name'>{$counter}</td>
                        <td class='name'><span {$url_fee}> ... </span></td>
                        <td class='name'><span {$url_item}>{$fields[3]}</span></td>";
            } else {
                $pre_fee = $fields[0];
                echo "<tr class='alternate'>
                        <td class='name'>{$counter}</td>
                        <td class='name'><span {$url_fee}>{$fields[1]}</span></td>
                        <td class='name'><span {$url_item}>{$fields[3]}</span></td>";
            }

            // fb_in, fb_out不应该同时有金额，所以相加$fields[4] + $fields[5]求前期余额
            echo ($fields[5] == 1) ? "<td class='name'>+</td>" : "<td class='name'>-</td>";

            // 差 2 个字段

            echo "<td class='name'>" . mix_num(abs($fields[6] - $fields[7]), 2) . "</td>
                   <td class='name'>" . mix_num($fields[8], 2) . "</td>
                   <td class='name'>" . mix_num($fields[9], 2) . "</td>
                   <td class='name'>" . mix_num(abs($fields[6] - $fields[7] + $fields[8] - $fields[9]), 2) . "</td>
                   <td class='name'>{$fields[4]}</td></tr>";

            $pre_balance += ($fields[6] - $fields[7]);
            $cur_breakeven += ($fields[8] - $fields[9]); // 费用类增加现金合计
        }
        echo "</tbody></table>";
    }

    $pre = mix_num($pre_balance, 2);
    $cur = mix_num($cur_breakeven, 2);
    $balance = mix_num($pre_balance + $cur_breakeven, 2);

    echo <<<Form_HTML
        <br />
    <table class="wp-list-table widefat fixed users" cellspacing="1">
    <thead>
        <tr>
            <th class='manage-column'>前期现金余额: </th>
            <th class='manage-column'>✚</th>
            <th class='manage-column'>本期盈亏: </th>
            <th class='manage-column'>〓</th>
            <th class='manage-column'>本期现金余额($): </th>
            <th class='manage-column'> </th>
        </tr>
        <tr class='alternate'>
            <td class='name'>{$pre}</td>
            <td class='name'> </td>
            <td class='name'>{$cur}</td>
            <td class='name'> </td>
            <td class='name'>{$balance}</td>
            <td class='name'> </td>
        </tr>
    </thead>
Form_HTML;

    echo '</table><br />';

    // 外币余额
    $currency_sql = "SELECT fi_name, sum(cb_money) AS m"
            . " FROM {$acc_prefix}currency_biz, {$acc_prefix}fee_item "
            . " WHERE cb_date <= '{$endday}' AND cb_fi_id=fi_id GROUP BY cb_fi_id";
    $currency = $wpdb->get_results($currency_sql, ARRAY_A);
    echo '<table class="wp-list-table widefat fixed users" cellspacing="1">
    <thead>
        <tr>
            <th class="manage-column">外币账户：记账货币之外的其它货币现金余额</th>
        </tr>
    </thead>';
    foreach ($currency as $balance) {
        echo "<tr class='alternate'>
            <td><span>{$balance['fi_name']} : {$balance['m']}</span></td>
            </tr>";
    }
    echo '</table><br />';
    // 外币余额
} // function cash_total
