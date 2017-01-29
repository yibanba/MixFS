<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

mixfs_top('产成品业务', $_SESSION['acc_name']);

global $wpdb;

$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';
$list_total = 15;

if( ! isset($_SESSION['rate'])) { // 设置当地货币转美元的汇率
    $_SESSION['rate'] = 1.000;
}


if (!isset($_GET['goodspage'])) {
    date_from_to("goodsbiz_date");
    ?>

    <form action="" method="post" name="createuser" id="createuser" class="validate">

        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th scope="row"><label for="goodsbiz_date">选择业务日期 <span class="description">(必填)</span></label></th>
                    <td><input name="goodsbiz_date" type="text" id="goodsbiz_date" value="<?php echo $_SESSION['goodsbiz']['date']; ?>" aria-required="true"></td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row"><label for="goodsbiz_inout">选择业务类型 <span class="description">(必填)</span></label></th>
                    <td>
                        <label><input name="goodsbiz_inout" type="radio" value="入库" style="width: 25px;">入库</label> &nbsp; 
                        <label><input name="goodsbiz_inout" type="radio" value="移库" style="width: 25px;">移库</label> &nbsp; 
                        <label><input name="goodsbiz_inout" type="radio" value="销售或退回" style="width: 25px;">销售或退回</label>
                    </td>
                </tr>
    <?php
    inout('请选择入库地点', 'goodsbiz_place1_in', $acc_prefix);
    inout('请选择出库地点', 'goodsbiz_place2_out', $acc_prefix);
    inout('请选择入库地点', 'goodsbiz_place2_in', $acc_prefix);
    inout('请选择销售地点', 'goodsbiz_place3_out', $acc_prefix);
    ?>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" name="goodsbiz_1" id="goodsbiz_1" class="button button-primary" value="下 一 步" />
            <input type="reset" name="goodsbiz_reset" id="goodsbiz_reset" class="button button-primary" value="重新填写" />
        </p>
    </form>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $(".goodsbiz_place1_in").hide();
            $(".goodsbiz_place2_out").hide();
            $(".goodsbiz_place2_in").hide();
            $(".goodsbiz_place3_out").hide();
            $("input[name='goodsbiz_reset']").click(function () {
                $(".goodsbiz_place1_in").hide();
                $(".goodsbiz_place2_out").hide();
                $(".goodsbiz_place2_in").hide();
                $(".goodsbiz_place3_out").hide();
                $("#goodsbiz_date").attr("value", "");
            });
            $('#createuser :radio').click(function () {
                switch ($("input[name='goodsbiz_inout']:checked").val()) {
                    case "入库":
                        $(".goodsbiz_place1_in").show();
                        $(".goodsbiz_place2_out").hide();
                        $(".goodsbiz_place2_in").hide();
                        $(".goodsbiz_place3_out").hide();
                        break;
                    case "移库":
                        $(".goodsbiz_place1_in").hide();
                        $(".goodsbiz_place3_out").hide();
                        $(".goodsbiz_place2_out").show();
                        $(".goodsbiz_place2_in").show();
                        break;
                    case "销售或退回":
                        $(".goodsbiz_place1_in").hide();
                        $(".goodsbiz_place2_out").hide();
                        $(".goodsbiz_place2_in").hide();
                        $(".goodsbiz_place3_out").show();
                        break;
                }
            });
        });
    </script>
    <?php
} // if (!isset($_GET['goodspage']))
elseif ($_GET['goodspage'] == 2) {
    ?>
    <div class="manage-menus">
        <div class="alignleft actions">
            <span>
    <?php
    switch (TRUE) {
        case ($_SESSION['goodsbiz']['inout'] == '入库'):
            $inout_str = '， 入库地点：【' . id2name('gp_name', $acc_prefix.'goods_place', $_SESSION['goodsbiz']['in'], 'gp_id') . '】';
            break;
        case ($_SESSION['goodsbiz']['inout'] == '移库'):
            $inout_str = '， 移库地点：【' 
                . id2name('gp_name', $acc_prefix.'goods_place', $_SESSION['goodsbiz']['out'], 'gp_id') . ' >>> '
                . id2name('gp_name', $acc_prefix.'goods_place', $_SESSION['goodsbiz']['in'], 'gp_id') . '】';
            break;
        case ($_SESSION['goodsbiz']['inout'] == '销售或退回'):
            $inout_str = '， 销售或退回地点：【' . id2name('gp_name', $acc_prefix.'goods_place', $_SESSION['goodsbiz']['out'], 'gp_id') . '】';
            break;
    }
    echo '当前日期：【' . $_SESSION['goodsbiz']['date'] . '】， 业务类型：【' . $_SESSION['goodsbiz']['inout'] . '】' . $inout_str;
    ?>
            </span>
        </div>
        <div class="alignright actions">
            <input type="button" name="goodsbiz_import" id="goodsbiz_import" class="button" value="Excel 批量导入" 
                   onclick="location.href = location.href.substring(0, location.href.indexOf('&goods')) + '&goodspage=import'" />
        </div>
        <br class="clear" />
    </div>
    <form action="" method="post" name="createuser" id="createuser" class="validate">

        <table class="form-table">
            <tbody>
    <?php
    $returns = ($_SESSION['goodsbiz']['inout'] == '销售或退回') ? ',退回填负数' : '';
    ?>
                <tr class="form-field">
                    <th scope="row"><label for="goodsbiz_name">产成品名称 (必填)</label></th>
                    <td><input type="text" name="goodsbiz_name" id="goodsbiz_name" value="双击选择或输入关键字" tabindex="2"></td>
                </tr>
    <?php
    // 自动完成文本框，选择产成品名称
    $get_cols = $wpdb->get_results("SELECT gs_name, gn_name, gn_id, gn_per_pack FROM {$acc_prefix}goods_name, {$acc_prefix}goods_series "
            . " WHERE gn_gs_id=gs_id ORDER BY gn_gs_id, gn_name", ARRAY_A);

    $cols_str = '';
    foreach ($get_cols as $value) {
        $cols_str .= '{ label: "' . $value['gn_name'] . '", category: "' . $value['gs_name'] . ' 系列", per_pack:"' . $value['gn_per_pack'] . '"},';
    }
    $cols_format = rtrim($cols_str, ',');

    goodscompletejs($cols_format, 'goodsbiz_name');
    ?>
                <tr class="form-field">
                    <th scope="row"><label for="goodsbiz_num">数量 (必填<?php echo $returns; ?>)</label></th>
                    <td>
                        <input name="goodsbiz_num" type="text" id="goodsbiz_num" value="" tabindex="3"> ×
                        <input name="per_pack" type="text" id="per_pack" value="1" tabindex="7" style="width: 4em;">
                        <label for="per_pack">双/每件</label>
                    </td>
                </tr>
    <?php if ($_SESSION['goodsbiz']['inout'] == '销售或退回') { ?>
                    <tr class="form-field">
                        <th scope="row"><label for="goodsbiz_money">金额 (必填<?php echo $returns; ?>)</label></th>
                        <td>
                            <input name="goodsbiz_money" type="text" id="goodsbiz_money" value="" tabindex="4"> ÷
                            <input name="goodsbiz_rate" type="text" id="goodsbiz_rate" value="<?php echo $_SESSION['rate']; ?>" maxlength="6" tabindex="9" style="width: 4em;">
                            <label for="goodsbiz_rate">美元汇率
                        </td>
                    </tr>
    <?php } ?>
                <tr class="form-field">
                    <th scope="row"><label for="goodsbiz_sum">业务摘要</label></th>
                    <td><input name="goodsbiz_sum" type="text" id="goodsbiz_sum" value="" tabindex="5"></td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" name="goodsbiz_submit" id="goodsbiz_submit" class="button button-primary" value="提交业务" tabindex="6" />
            <input type="reset" name="goodsbiz_r" id="goodsbiz_r" class="button button-primary" value="清空内容" />
            <input type="button" name="goodsbiz_return" id="goodsbiz_return" class="button button-primary" value="返回上级" 
                   onclick="location.href = location.href.substring(0, location.href.indexOf('&goods'))" /> &nbsp; 
            <input type="submit" name="update_per_pack" id="update_per_pack" class="button" value="每件双数更新" tabindex="8" />

        </p>
    </form>
    <?php
} // elseif ($_GET['goodspage'] == 2)
elseif ($_GET['goodspage'] == 'import') {

    include_once 'goodsbiz-import.php';
} // elseif ($_GET['goodspage'] == 'import')


if (isset($_POST['goodsbiz_1'])) { // 处理表单提交

    $_SESSION['goodsbiz']['date'] = '';
    $_SESSION['goodsbiz']['inout'] = '';
    $_SESSION['goodsbiz']['in'] = '';
    $_SESSION['goodsbiz']['out'] = '';
    $date_arr = explode('-', $_POST['goodsbiz_date']);
    $err = '';

    if (count($date_arr) == 3 && checkdate($date_arr[1], $date_arr[2], $date_arr[0]) && $_POST['goodsbiz_inout'] != '') {
        $_SESSION['goodsbiz']['date'] = $_POST['goodsbiz_date'];
        $_SESSION['goodsbiz']['inout'] = $_POST['goodsbiz_inout'];
        switch (TRUE) {
            case ($_POST['goodsbiz_inout'] == '入库' && $_POST['goodsbiz_place1_in'] > 0):
                $_SESSION['goodsbiz']['in'] = $_POST['goodsbiz_place1_in'];
                break;
            case ($_POST['goodsbiz_inout'] == '移库' 
                    && $_POST['goodsbiz_place2_out'] > 0 
                    && $_POST['goodsbiz_place2_in'] > 0 
                    && $_POST['goodsbiz_place2_out'] != $_POST['goodsbiz_place2_in'] ):
                $_SESSION['goodsbiz']['out'] = $_POST['goodsbiz_place2_out'];
                $_SESSION['goodsbiz']['in'] = $_POST['goodsbiz_place2_in'];
                break;
            case ($_POST['goodsbiz_inout'] == '销售或退回' && $_POST['goodsbiz_place3_out'] > 0):
                $_SESSION['goodsbiz']['out'] = $_POST['goodsbiz_place3_out'];
                break;
            default:
                echo $err = '<div id="message" class="updated"><p>请重新选择业务类型并完成出入库再继续下一步操作，出入库地点不能相同</p></div>';
        }
        if ($err == '') {
            echo "<script type='text/javascript'>location.href=location.href + '&goodspage=2';</script>";
        }
    } else {
        echo '<div id="message" class="updated"><p>请填写业务日期和业务类型</p></div>';
    }
} // if (isset($_POST['goodsbiz_1']))
elseif (isset($_POST['goodsbiz_submit'])) {
    $goods_name = $wpdb->get_var("SELECT gn_id FROM {$acc_prefix}goods_name WHERE gn_name = '{$_POST['goodsbiz_name']}'");
    $goods_num = is_numeric($_POST['goodsbiz_num']) ? $_POST['goodsbiz_num']*$_POST['per_pack'] : 0;

    if ($_SESSION['goodsbiz']['inout'] == '入库') {
        if ($goods_name && $goods_num) {
            $wpdb->insert($acc_prefix . 'goods_biz', array('gb_date' => $_SESSION['goodsbiz']['date'],
                'gb_gp_id' => $_SESSION['goodsbiz']['in'],
                'gb_in' => $goods_num,
                'gb_summary' => trim($_POST['goodsbiz_sum']),
                'gb_gn_id' => $goods_name
                    )
            );
            echo "<div id='message' class='updated'><p>提交【{$_POST['goodsbiz_name']}】产成品业务成功</p></div>";
        } else {
            echo "<div id='message' class='updated'><p>请完成(必填)选项后再提交</p></div>";
        }
    } elseif ($_SESSION['goodsbiz']['inout'] == '销售或退回') {
        $money = trim($_POST['goodsbiz_money']);
        $_SESSION['rate'] = trim($_POST['goodsbiz_rate']);
        if ($goods_name && ($goods_num * $money > 0) && $_SESSION['rate']) {
            $wpdb->insert($acc_prefix . 'goods_biz', array('gb_date' => $_SESSION['goodsbiz']['date'],
                'gb_gp_id' => $_SESSION['goodsbiz']['out'],
                'gb_out' => $goods_num,
                'gb_money' => ($money / $_SESSION['rate']),
                'gb_summary' => trim($_POST['goodsbiz_sum']),
                'gb_gn_id' => $goods_name
                    )
            );
            if($money > 0) { // 正常销售，不含退货
                $wpdb->update( $acc_prefix . 'goods_name', 
                    array( 'gn_price' => number_format(($money/$goods_num) / $_SESSION['rate'],2) ), 
                    array( 'gn_id' => $goods_name ), 
                    array( '%.2f' ), array( '%d' ) );
            }
            echo "<div id='message' class='updated'><p>提交【{$_POST['goodsbiz_name']}】产成品业务成功</p></div>";
        } else {
            echo "<div id='message' class='updated'><p>请完成(必填)选项后再提交</p></div>";
        }
    } elseif ($_SESSION['goodsbiz']['inout'] == '移库') {
        if ($goods_name && $goods_num) {
            $wpdb->insert($acc_prefix . 'goods_biz', 
                array('gb_date' => $_SESSION['goodsbiz']['date'],
                    'gb_gp_id' => $_SESSION['goodsbiz']['out'],
                    'gb_out' => $goods_num,
                    'gb_summary' => trim($_POST['goodsbiz_sum']),
                    'gb_gn_id' => $goods_name )
            );
            $wpdb->insert($acc_prefix . 'goods_biz', 
                array('gb_date' => $_SESSION['goodsbiz']['date'],
                    'gb_gp_id' => $_SESSION['goodsbiz']['in'],
                    'gb_in' => $goods_num,
                    'gb_summary' => trim($_POST['goodsbiz_sum']),
                    'gb_gn_id' => $goods_name)
            );
            echo "<div id='message' class='updated'><p>提交【{$_POST['goodsbiz_name']}】产成品业务成功</p></div>";
        } else {
            echo "<div id='message' class='updated'><p>请完成(必填)选项后再提交</p></div>";
        }
    }
} // elseif (isset($_POST['goodsbiz_submit']))
elseif (isset ($_POST['update_per_pack'])) {
    if( $_POST['goodsbiz_name'] && $_POST['per_pack']) {
        $updated = $wpdb->update( "{$acc_prefix}goods_name", 
                array('gn_per_pack'=> $_POST['per_pack']), 
                array('gn_name'=>$_POST['goodsbiz_name']),
                array( '%d' ));
        if($updated == 0) {
            echo "<div id='message' class='updated'><p>选择产品名称后再提交</p></div>";
        } else {
            echo "<div id='message' class='updated'><p>更新【{$_POST['goodsbiz_name']}】产品件双数成功</p></div>";            
        }
    }
}


?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#message').show().delay(5000).hide(0);
        });
    </script>
<?php

    goodsbiz_list($acc_prefix, $list_total);

mixfs_bottom(); // 框架页面底部
//******************************************************************************

/**
 * 显示最近提交业务流水
 * 所有页面显示的最近 10 条业务流水
 */
function goodsbiz_list($acc_prefix, $total = '') {
    global $wpdb;
    echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
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
    $limit = ($total == '') ? "" : " LIMIT {$total}";
    $results_goodsbiz = $wpdb->get_results("SELECT gb_id, gb_date, gs_name, gn_name, gb_in, gb_out, gb_money, gb_gp_id, gb_summary "
            . " FROM {$acc_prefix}goods_biz, {$acc_prefix}goods_name, {$acc_prefix}goods_series "
            . " WHERE gb_gn_id = gn_id AND gn_gs_id = gs_id "
            . " ORDER BY gb_id DESC $limit", ARRAY_A);

    foreach ($results_goodsbiz as $gb) {
        $place = id2name("gp_name", "{$acc_prefix}goods_place", $gb['gb_gp_id'], "gp_id");
        $in_number =  ( $gb['gb_in'] == 0 ) ?  '' : number_format($gb['gb_in'], 0);
        $out_number = ( $gb['gb_out'] == 0 ) ?  '' : number_format($gb['gb_out'], 0);
        $money = ($gb['gb_money'] == 0) ? '' : number_format($gb['gb_money'], 2);
            echo "<tr class='alternate'>
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

    
} // function goodsbiz_list($total = 10)

/**
 * 生成仓库、店铺 下拉框
 * @param type $title
 * @param type $tag
 */
function inout($title, $tag, $acc_prefix) {
    global $wpdb;
    $places = $wpdb->get_results("SELECT gp_id, gp_name FROM {$acc_prefix}goods_place ORDER BY gp_id", ARRAY_A);

    echo "<tr class='{$tag}'>
                    <th scope='row'><label for='{$tag}'>{$title} (必填)</label></th>
                    <td>
                        <select name='{$tag}' id='{$tag}' style='width: 25em;'>
                            <option selected='selected' value='0'>{$title}</option>";

    foreach ($places as $p) {
        printf('<option value="%d">%s</option>', $p['gp_id'], $p['gp_name']);
    }
    echo '</select></td></tr>';
}
