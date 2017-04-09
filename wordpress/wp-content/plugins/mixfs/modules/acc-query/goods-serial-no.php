<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

mixfs_top('产成品业务流水', $_SESSION['acc_name']);

global $wpdb;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

isset($_SESSION['goods_qry_date']) ?: $_SESSION['goods_qry_date'] = date("Y-m-d");


if(isset($_POST['btn_goods_biz'])) {
    $_SESSION['goods_qry_date'] = $_POST['goods_qry_date'];
    form_qry_goods($_SESSION['goods_qry_date']);
    goodsbiz_list($acc_prefix, $_SESSION['goods_qry_date']);
} elseif (isset($_POST['btn_goods_biz_prev'])) {
    $_SESSION['goods_qry_date'] = date("Y-m-d", strtotime("{$_POST['goods_qry_date']}, -1 day"));
    form_qry_goods($_SESSION['goods_qry_date']);
    goodsbiz_list($acc_prefix, $_SESSION['goods_qry_date']);
} elseif (isset($_POST['btn_goods_biz_last'])) {
    $_SESSION['goods_qry_date'] = date("Y-m-d", strtotime("{$_POST['goods_qry_date']}, +1 day"));
    form_qry_goods($_SESSION['goods_qry_date']);
    goodsbiz_list($acc_prefix, $_SESSION['goods_qry_date']);
} else {
    form_qry_goods($_SESSION['goods_qry_date']);
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
function form_qry_goods($day) {
    ?>
    <form action="" method="post">
        <div class="manage-menus">
            <!--# 汇总查询库存和销售 -->
            <div class="alignleft actions" id="sale_inventory">
                <label for="goods_qry_date">指定业务发生日期
                    <input name="goods_qry_date" type="text" id="goods_qry_date" value="<?php echo $day; ?>">
                </label>
    <?php
    date_from_to("goods_qry_date");
    ?>
                <input type="submit" name="btn_goods_biz" id="btn_goods_biz" class="button button-primary" value="产成品业务流水查询"  />
                &nbsp; 
                <input type="submit" name="btn_goods_biz_prev" id="btn_goods_biz_prev" class="button" value="<<< 前一天"  />
                <input type="submit" name="btn_goods_biz_last" id="btn_goods_biz_last" class="button" value="后一天 >>>"  />
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
function goodsbiz_list($acc_prefix, $day) {
    global $wpdb;
    echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column' style="width:50px;"></th>
                    <th class='manage-column' style="">日期</th>
                    <th class='manage-column'  style="">系列</th>
                    <th class='manage-column'  style="">型号</th>
                    <th class='manage-column'  style="">仓库</th>
                    <th class='manage-column'  style="">件数</th>
                    <th class='manage-column'  style="">入库</th>
                    <th class='manage-column'  style="">出库</th>
                    <th class='manage-column'  style="">金额</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column' style="width:50px;"></th>
                    <th class='manage-column' style="">日期</th>
                    <th class='manage-column'  style="">系列</th>
                    <th class='manage-column'  style="">型号</th>
                    <th class='manage-column'  style="">仓库</th>
                    <th class='manage-column'  style="">件数</th>
                    <th class='manage-column'  style="">入库</th>
                    <th class='manage-column'  style="">出库</th>
                    <th class='manage-column'  style="">金额</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

// 产成品业务列表
    $results_goodsbiz = $wpdb->get_results("SELECT gb_id, gb_date, gs_name, gn_name, gb_in, gb_out, gb_money, gb_gp_id, gb_summary, gn_per_pack "
            . " FROM {$acc_prefix}goods_biz, {$acc_prefix}goods_name, {$acc_prefix}goods_series "
            . " WHERE gb_date = '{$day}' AND gb_gn_id = gn_id AND gn_gs_id = gs_id "
            . " ORDER BY gb_date,gb_id  ", ARRAY_A);

    $gp_total = $wpdb->get_results("SELECT gp_id FROM {$acc_prefix}goods_place", ARRAY_A);
    $gp[] = array();

    foreach ($gp_total as $value) {
        $gp[$value['gp_id']]['in'] = 0;      // 指定仓库入库初始化 0
        $gp[$value['gp_id']]['out'] = 0;     // 指定仓库出库初始化 0
    }
    foreach ($results_goodsbiz as $gb) {
        if($gb['gb_in'] != 0) {      // 累计入库件数
            $gp[$gb['gb_gp_id']]['in'] += $gb['gb_in'] / $gb['gn_per_pack'];
        } 
        if ($gb['gb_out'] != 0) {    // 累计出库件数
            $gp[$gb['gb_gp_id']]['out'] += $gb['gb_out'] / $gb['gn_per_pack'];
        }
        $place = id2name("gp_name", "{$acc_prefix}goods_place", $gb['gb_gp_id'], "gp_id");
        $piece = ($gb['gb_in'] + $gb['gb_out']) / $gb['gn_per_pack'];
        $in_number =  ( $gb['gb_in'] == 0 ) ?  '' : number_format($gb['gb_in'], 0);
        $out_number = ( $gb['gb_out'] == 0 ) ?  '' : number_format($gb['gb_out'], 0);
        $money = ($gb['gb_money'] == 0) ? '' : number_format($gb['gb_money'], 2);
            echo "<tr class='alternate'>
                    <td class='name'>{$gb['gb_id']}</td>
                    <td class='name' style='width:50px;'><input type='checkbox'></td>
                    <td class='name'>{$gb['gb_date']}</td>
                    <td class='name'>{$gb['gs_name']}</td>
                    <td class='name'>{$gb['gn_name']}</td>
                    <td class='name'>{$place}</td>
                    <td class='name'><span style='color:#AAA;'>{$piece}</span></td>
                    <td class='name'>{$in_number}</td>
                    <td class='name'>{$out_number}</td>
                    <td class='name'>{$money}</td>
                    <td class='name'>{$gb['gb_summary']}</td>
                </tr>";
    } // foreach ($results_goodsbiz as $gb)

    echo '</tbody></table>';
    
    echo <<<Form_HTML
    <br />
    <table class="wp-list-table widefat fixed users" cellspacing="1">
    <thead>
        <tr>
            <th class='manage-column' style="">仓库代码</th>
            <th class='manage-column' style="">仓库名称</th>
            <th class='manage-column'  style="">入库件数合计</th>
            <th class='manage-column'  style="">出库件数合计</th>
        </tr>
    </thead>
    <tbody>
Form_HTML;
    
    foreach ($gp_total as $value) {
        $place = id2name("gp_name", "{$acc_prefix}goods_place", $value['gp_id'], "gp_id");
        echo "<tr class='alternate'>
                    <td class='name'>{$value['gp_id']}</td>
                    <td class='name'>{$place}</td>
                    <td class='name'>{$gp[$value['gp_id']]['in']}</td>
                    <td class='name'>{$gp[$value['gp_id']]['out']}</td>
                </tr>";
    }
    echo '</tbody></table>';
    
} // function goodsbiz_list($acc_prefix, $day)