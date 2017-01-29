<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

mixfs_top('产成品业务流水', $_SESSION['acc_name']);

global $wpdb;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

isset($_SESSION['goods_qry_date']['date1']) ?: $_SESSION['goods_qry_date']['date1'] = date("Y-m-d", strtotime("-1 months"));
isset($_SESSION['goods_qry_date']['date2']) ?: $_SESSION['goods_qry_date']['date2'] = date("Y-m-d");


if(isset($_POST['btn_goods_biz'])) {
    $_SESSION['goods_qry_date']['date1'] = $_POST['goods_qry_date1'];
    $_SESSION['goods_qry_date']['date2'] = $_POST['goods_qry_date2'];
    form_qry_goods($_SESSION['goods_qry_date']['date1'], $_SESSION['goods_qry_date']['date2']);
    goodsbiz_list($acc_prefix, $_SESSION['goods_qry_date']['date1'], $_SESSION['goods_qry_date']['date2']);
} else {
    form_qry_goods($_SESSION['goods_qry_date']['date1'], $_SESSION['goods_qry_date']['date2']);
}

?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $(".alternate").click(function (e) {
            if ($(this).find(":checkbox").is(":checked")) {
                $(this).find(":checkbox").attr("checked", false);
            } else {
                $(this).find(":checkbox").attr("checked", true);
            }
        })
    });
</script>
<?php

mixfs_bottom(); // 框架页面底部

//******************************************************************************

/**
 * 默认查询表单
 */
function form_qry_goods($startday, $endday) {
    ?>
    <form action="" method="post">
        <div class="manage-menus">
            <!--# 汇总查询库存和销售 -->
            <div class="alignleft actions" id="sale_inventory">
                <label for="goods_qry_date1">指定起始日期
                    <input name="goods_qry_date1" type="text" id="goods_qry_date1" value="<?php echo $startday; ?>">
                </label>
                <label for="goods_qry_date2">指定截止日期
                    <input name="goods_qry_date2" type="text" id="goods_qry_date2" value="<?php echo $endday; ?>">
                </label>
    <?php
    date_from_to("goods_qry_date1", "goods_qry_date2");
    ?>
                <input type="submit" name="btn_goods_biz" id="btn_goods_biz" class="button button-primary" value="产成品业务流水查询"  />
            </div>

            <br class="clear" />
        </div>
        <br />
    </form>
    <?php
} // function form_qry_goods()



/**
 * 显示最近提交业务流水
 * 所有页面显示的最近 10 条业务流水
 */
function goodsbiz_list($acc_prefix, $startday, $endday) {
    global $wpdb;
    echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="width:50px;"></th>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column' style="">日期</th>
                    <th class='manage-column'  style="">系列</th>
                    <th class='manage-column'  style="">型号</th>
                    <th class='manage-column'  style="">仓库</th>
                    <th class='manage-column'  style="">入库</th>
                    <th class='manage-column'  style="">出库</th>
                    <th class='manage-column'  style="">金额</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th class='manage-column' style="width:50px;"></th>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column' style="">日期</th>
                    <th class='manage-column'  style="">系列</th>
                    <th class='manage-column'  style="">型号</th>
                    <th class='manage-column'  style="">仓库</th>
                    <th class='manage-column'  style="">入库</th>
                    <th class='manage-column'  style="">出库</th>
                    <th class='manage-column'  style="">金额</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

// 产成品业务列表
    $results_goodsbiz = $wpdb->get_results("SELECT gb_id, gb_date, gs_name, gn_name, gb_in, gb_out, gb_money, gb_gp_id, gb_summary "
            . " FROM {$acc_prefix}goods_biz, {$acc_prefix}goods_name, {$acc_prefix}goods_series "
            . " WHERE gb_date BETWEEN '{$startday}' AND '{$endday}' AND gb_gn_id = gn_id AND gn_gs_id = gs_id "
            . " ORDER BY gb_date,gb_id  ", ARRAY_A);

    foreach ($results_goodsbiz as $gb) {
        $place = id2name("gp_name", "{$acc_prefix}goods_place", $gb['gb_gp_id'], "gp_id");
        $in_number =  ( $gb['gb_in'] == 0 ) ?  '' : number_format($gb['gb_in'], 0);
        $out_number = ( $gb['gb_out'] == 0 ) ?  '' : number_format($gb['gb_out'], 0);
        $money = ($gb['gb_money'] == 0) ? '' : number_format($gb['gb_money'], 2);
            echo "<tr class='alternate'>
                    <td class='name' style='width:50px;'><input type='checkbox'></td>
                    <td class='name'>{$gb['gb_id']}</td>
                    <td class='name'>{$gb['gb_date']}</td>
                    <td class='name'>{$gb['gs_name']}</td>
                    <td class='name'>{$gb['gn_name']}</td>
                    <td class='name'>{$place}</td>
                    <td class='name'>{$in_number}</td>
                    <td class='name'>{$out_number}</td>
                    <td class='name'>{$money}</td>
                    <td class='name'>{$gb['gb_summary']}</td>
                </tr>";
    } // foreach ($results_goodsbiz as $gb)

    echo '</tbody></table>';

    
} // function goodsbiz_list($acc_prefix, $startday, $endday)