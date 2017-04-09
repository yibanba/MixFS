<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

mixfs_top('产成品订单业务', $_SESSION['acc_name']);

global $wpdb;

$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';
$list_total = 15;

if (!isset($_SESSION['rate'])) { // 设置当地货币转美元的汇率
    $_SESSION['rate'] = 1.000;
}


if (isset($_POST['goodsbiz_1'])) { // 处理表单提交
    $_SESSION['goodsbiz']['date'] = '';
    $_SESSION['goodsbiz']['inout'] = '';    // 入库、移库、销售或退回
    $_SESSION['goodsbiz']['in'] = '';       // 入库地点
    $_SESSION['goodsbiz']['out'] = '';      // 出库地点
    $date_arr = explode('-', $_POST['goodsbiz_date']);
    $err = '';

    if (count($date_arr) == 3 && checkdate($date_arr[1], $date_arr[2], $date_arr[0]) && $_POST['goodsbiz_inout'] != '') {
        $_SESSION['goodsbiz']['date'] = $_POST['goodsbiz_date'];
        $_SESSION['goodsbiz']['inout'] = $_POST['goodsbiz_inout'];
        switch (TRUE) {
            case ($_POST['goodsbiz_inout'] == '入库' && $_POST['goodsbiz_place1_in'] > 0):
                $_SESSION['goodsbiz']['in'] = $_POST['goodsbiz_place1_in'];
                break;
            case ($_POST['goodsbiz_inout'] == '移库' && $_POST['goodsbiz_place2_out'] > 0 && $_POST['goodsbiz_place2_in'] > 0 && $_POST['goodsbiz_place2_out'] != $_POST['goodsbiz_place2_in'] ):
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
elseif (isset($_POST['btn_order'])) {
    //**********************************************************************************************
    $goods_names = $wpdb->get_results("SELECT gn_id, gn_name FROM {$acc_prefix}goods_name", ARRAY_A);
    $gn_kv = array(); // 产成品名称键值对， 品名=>ID
    foreach ($goods_names as $v) {
        $gn_kv[$v['gn_name']] = $v['gn_id'];
    }

    $sql = '';
    $flag = 0;  // 数量标识

    if ($_SESSION['goodsbiz']['inout'] == '入库') {
        $sql .= "INSERT INTO `{$acc_prefix}goods_biz` (`gb_date`, `gb_in`, `gb_gp_id`, `gb_gn_id`) VALUES ";
        $order_num = count($_POST['goodsbiz_name']);
        for ($i = 0; $i < $order_num; $i++) {
            $gb_name = $_POST["goodsbiz_name"][$i];
            $gb_num = $_POST["qty"][$i] * $_POST["per_pack"][$i];
            if ($gb_name != "" && $gb_num != 0) {
                $sql .= "( '{$_SESSION['goodsbiz']['date']}', {$gb_num}, {$_SESSION['goodsbiz']['in']}, {$gn_kv[$gb_name]} ),";
                $flag++;
            }
        }
        if($flag > 0) {
            $sql_format = rtrim($sql, ",");
            $wpdb->query($sql_format);
            echo "<div id='message' class='updated'><p>提交【{$flag}】条产成品业务成功</p></div>";
        } else {
            echo "<div id='message' class='updated'><p>请完成(必填)选项后再提交</p></div>";
        }
    } elseif ($_SESSION['goodsbiz']['inout'] == '销售或退回') {
        $sql .= "INSERT INTO `{$acc_prefix}goods_biz` (`gb_date`, `gb_out`, `gb_money`, `gb_gp_id`, `gb_gn_id`) VALUES ";
        $order_num = count($_POST['goodsbiz_name']);
        for ($i = 0; $i < $order_num; $i++) {
            $gb_name = $_POST["goodsbiz_name"][$i];
            $gb_num = $_POST["qty"][$i] * $_POST["per_pack"][$i];
            $money = trim($_POST['price'][$i]) * $gb_num;
            if ($gb_name != "" && $gb_num != 0 && $money != 0) {
                $sql .= "( '{$_SESSION['goodsbiz']['date']}', {$gb_num}, {$money}, {$_SESSION['goodsbiz']['out']}, {$gn_kv[$gb_name]} ),";
                $flag++;
            }
        }
        if($flag > 0) {
            $sql_format = rtrim($sql, ",");
            $wpdb->query($sql_format);
            echo "<div id='message' class='updated'><p>提交【{$flag}】条产成品业务成功</p></div>";
        } else {
            echo "<div id='message' class='updated'><p>请完成(必填)选项后再提交</p></div>";
        }
    } elseif ($_SESSION['goodsbiz']['inout'] == '移库') {
        $sql .= "INSERT INTO `{$acc_prefix}goods_biz` (`gb_date`, `gb_in`, `gb_out`, `gb_gp_id`, `gb_gn_id`) VALUES ";
        $order_num = count($_POST['goodsbiz_name']);
        for ($i = 0; $i < $order_num; $i++) {
            $gb_name = $_POST["goodsbiz_name"][$i];
            $gb_num = $_POST["qty"][$i] * $_POST["per_pack"][$i];
            if ($gb_name != "" && $gb_num != 0) {
                $sql .= "( '{$_SESSION['goodsbiz']['date']}', 0, {$gb_num}, {$_SESSION['goodsbiz']['out']}, {$gn_kv[$gb_name]} ),";
                $sql .= "( '{$_SESSION['goodsbiz']['date']}', {$gb_num}, 0, {$_SESSION['goodsbiz']['in']}, {$gn_kv[$gb_name]} ),";
                $flag++;
            }
        }
        if($flag > 0) {
            $sql_format = rtrim($sql, ",");
            $wpdb->query($sql_format);
            echo "<div id='message' class='updated'><p>提交【{$flag}】条产成品业务成功</p></div>";
        } else {
            echo "<div id='message' class='updated'><p>请完成(必填)选项后再提交</p></div>";
        }
    }
    
    //**********************************************************************************************

    //**********************************************************************************************
} // elseif (isset($_POST['goodsbiz_submit']))
elseif (isset($_POST['update_per_pack'])) {
    if ($_POST['goodsbiz_name'] && $_POST['per_pack']) {
        $updated = $wpdb->update("{$acc_prefix}goods_name", array('gn_per_pack' => $_POST['per_pack']), array('gn_name' => $_POST['goodsbiz_name']), array('%d'));
        if ($updated == 0) {
            echo "<div id='message' class='updated'><p>选择产品名称后再提交</p></div>";
        } else {
            echo "<div id='message' class='updated'><p>更新【{$_POST['goodsbiz_name']}】产品件双数成功</p></div>";
        }
    }
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
    goodsbiz_list($acc_prefix, $list_total);
} // if (!isset($_GET['goodspage']))
elseif ($_GET['goodspage'] == 2) {
    ?>
    <div class="manage-menus">
        <div class="alignleft actions">
            <span>
                <?php
                switch (TRUE) {
                    case ($_SESSION['goodsbiz']['inout'] == '入库'):
                        $inout_str = '， 入库地点：【' . id2name('gp_name', $acc_prefix . 'goods_place', $_SESSION['goodsbiz']['in'], 'gp_id') . '】';
                        break;
                    case ($_SESSION['goodsbiz']['inout'] == '移库'):
                        $inout_str = '， 移库地点：【'
                                . id2name('gp_name', $acc_prefix . 'goods_place', $_SESSION['goodsbiz']['out'], 'gp_id') . ' >>> '
                                . id2name('gp_name', $acc_prefix . 'goods_place', $_SESSION['goodsbiz']['in'], 'gp_id') . '】';
                        break;
                    case ($_SESSION['goodsbiz']['inout'] == '销售或退回'):
                        $inout_str = '， 销售或退回地点：【' . id2name('gp_name', $acc_prefix . 'goods_place', $_SESSION['goodsbiz']['out'], 'gp_id') . '】';
                        break;
                }
                echo '当前日期：【' . $_SESSION['goodsbiz']['date'] . '】， 业务类型：【' . $_SESSION['goodsbiz']['inout'] . '】' . $inout_str;
                ?>
            </span>
        </div>
        <div class="alignright actions">
            <input type="button" name="goodsbiz_import" id="goodsbiz_import" class="button" value="Excel 批量导入" 
                   onclick="location.href = location.href.substring(0, location.href.indexOf('&goodspage')) + '&goodspage=import'" />
        </div>
        <br class="clear" />
    </div>
    <form action="" method="post" name="createuser" id="createuser" class="validate">

        <table class="form-table">
            <thead>
                <tr><th>行号</th><th>品名</th><th style="width: 50px;">双/件</th><th>件数</th><th>单价</th><th>小计</th></tr>
            </thead>
            <tbody>

                <?php for ($i = 1; $i <= 5; $i++) : ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><input type="text" name="goodsbiz_name[]" class="goodsbiz_order" value="" /></td>
                        <td style="width: 50px;"><input type="text" name="per_pack[]" class="per_pack" value="" style="width: 50px;background-color:#EEE;" /></td>
                        <td><input type="text" name="qty[]" value="" onfocus="this.select()" /></td>
                        <td><input type="text" name="price[]" value="" /></td>
                        <td><input type="text" name="sum[]" value="" disabled="disabled" /></td>
                        <!-- <td class="removeclass"> &nbsp; </td> -->
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" value="提交订单" name="btn_order" class="button button-primary">
            <input type="reset" value="清空内容" name="btn_reset" class="button button-primary">
            <input type="button" name="goodsbiz_return" id="goodsbiz_return" class="button button-primary" value="返回上级" 
                   onclick="location.href = location.href.substring(0, location.href.indexOf('&goods'))" />&nbsp; 
            <input type="button" value="添加表格" id="btn_add" class="button">
            <span style="margin-left: 30px">
                <label for="tags">件数合计:</label><input class="ti" id="amount">  &nbsp; 
                <label for="tags">金额合计:</label><input class="ti" id="total">
            </span>
    </p>

    </form>


    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var total_qty = 0;
            var total_sum = 0;

            $("#createuser").keyup(function () {
                var arr_pack = $("input[name='per_pack[]']").toArray();
                var arr_qty = $("input[name='qty[]']").toArray();
                var arr_price = $("input[name='price[]']").toArray();
                var arr_sums = $("input[name='sum[]']").toArray();

                var i = 0;
                var amount = 0;
                var total = 0;
                var sign=1;
                var t=0; // temp of sum
                $("input[name='sum[]']").each(function () {
                    var pack = (arr_pack[i].value != 0) ? arr_pack[i].value : 0;
                    var q = (arr_qty[i].value != 0) ? arr_qty[i].value : 0;
                    var p = (arr_price[i].value != 0) ? arr_price[i].value : 0;
                    if(q<0 && p<0) {
                        sign=-1;
                    }
                    t = pack * q * p * sign;
                    t = parseFloat(t.toFixed(2));
                    $(this).val( t );
                    i++;
                    amount += q * 1;
                    
                    total += t;
                });
                $("#amount").val(amount);
                $("#total").val(parseFloat(total.toFixed(2)));
            });
        });
    </script>
    <?php
// 自动完成文本框，选择产成品名称
    $get_cols = $wpdb->get_results("SELECT gs_name, gn_name, gn_id, gn_per_pack FROM {$acc_prefix}goods_name, {$acc_prefix}goods_series "
            . " WHERE gn_gs_id=gs_id ORDER BY gn_gs_id, gn_name", ARRAY_A);

    $cols_str = '';
    foreach ($get_cols as $value) {
        $cols_str .= '{ label: "' . $value['gn_name'] . '", category: "' . $value['gs_name'] . ' 系列", per_pack:"' . $value['gn_per_pack'] . '"},';
    }
    $cols_format = rtrim($cols_str, ',');

    ordercompletejs($cols_format, 'goodsbiz_order');


    goodsbiz_list($acc_prefix, $list_total);
} // elseif ($_GET['goodspage'] == 2)
elseif ($_GET['goodspage'] == 'import') {

    include_once 'goodsbiz-import.php';
} // elseif ($_GET['goodspage'] == 'import')


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
                    <th class='manage-column'  style="">入库 <span style="color:#AAA;">[件数]</span></th>
                    <th class='manage-column'  style="">出库 <span style="color:#AAA;">[件数]</span></th>
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
                    <th class='manage-column'  style="">入库 <span style="color:#AAA;">[件数]</span></th>
                    <th class='manage-column'  style="">出库 <span style="color:#AAA;">[件数]</span></th>
                    <th class='manage-column'  style="">金额</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

// 产成品业务列表
    $limit = ($total == '') ? "" : " LIMIT {$total}";
    $results_goodsbiz = $wpdb->get_results("SELECT gb_id, gb_date, gs_name, gn_name, gb_in, gb_out, gb_money, gb_gp_id, gb_summary, gn_per_pack "
            . " FROM {$acc_prefix}goods_biz, {$acc_prefix}goods_name, {$acc_prefix}goods_series "
            . " WHERE gb_gn_id = gn_id AND gn_gs_id = gs_id "
            . " ORDER BY gb_id DESC $limit", ARRAY_A);

    foreach ($results_goodsbiz as $gb) {
        $place = id2name("gp_name", "{$acc_prefix}goods_place", $gb['gb_gp_id'], "gp_id");
        $in_number = ( $gb['gb_in'] == 0 ) ? '' : number_format($gb['gb_in'], 0) . ' <span style="color:#AAA;">[' . ($gb['gb_in'] / $gb['gn_per_pack']) . ']</span>';
        $out_number = ( $gb['gb_out'] == 0 ) ? '' : number_format($gb['gb_out'], 0) . ' <span style="color:#AAA;">[' . ($gb['gb_out'] / $gb['gn_per_pack']) . ']</span>';
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
}

// function goodsbiz_list($total = 10)

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
