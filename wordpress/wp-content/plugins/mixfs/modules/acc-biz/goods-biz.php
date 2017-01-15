<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

mixfs_top('产成品业务', $_SESSION['acc_name']);

global $wpdb;

$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

if (isset($_POST['goodsbiz_1'])) {

    $_SESSION['goodsbiz']['date']=0;
    $_SESSION['goodsbiz']['inout']=0;
    
    $date_arr = explode('-', $_POST['goodsbiz_date']);
    if (count($date_arr) == 3 && checkdate($date_arr[1], $date_arr[2], $date_arr[0])) {
        $_SESSION['goodsbiz']['date'] = $_POST['goodsbiz_date'];
    } else {
        echo '<div id="message" class="updated"><p>日期格式不正确，请重新操作</p></div>';
    }
    if ($_POST['goodsbiz_inout'] > 0) {
        switch ($_POST['goodsbiz_inout']) {
            case 1: $_SESSION['goodsbiz']['inout'] = '入库';
                break;
            case 2: $_SESSION['goodsbiz']['inout'] = '移库';
                break;
            case 3: $_SESSION['goodsbiz']['inout'] = '销售或退回';
                break;
        }
        
        if ($_SESSION['goodsbiz']['date'] && $_SESSION['goodsbiz']['inout']) {
            echo "<script type='text/javascript'>location.href=location.href + '&goodspage=2';</script>";
        }
    } else {
        echo '<div id="message" class="updated"><p>请选择出入库再继续操作</p></div>';
    }
} // if (isset($_POST['goodsbiz_1']))
elseif (isset($_POST['goodsbiz_submit'])) {
    $goods_name = $wpdb->get_var("SELECT gn_id FROM {$acc_prefix}goods_name WHERE gn_name = '{$_POST['goodsbiz_name']}'");
    $goods_num = is_numeric($_POST['goodsbiz_num']) ? $_POST['goodsbiz_num'] : 0;

    if($_SESSION['goodsbiz']['inout'] == '入库') {
        if ($goods_name && $goods_num && $_POST['goodsbiz_place'] && $_POST['goodsbiz_place']) {
            $wpdb->insert($acc_prefix . 'goods_biz', array('gb_date' => $_SESSION['goodsbiz']['date'],
                    'gb_in_place' => $_POST['goodsbiz_place'],
                    'gb_num' => $goods_num,
                    'gb_summary' => trim($_POST['goodsbiz_sum']),
                    'gb_gn_id' => $goods_name
                    )
            );
            echo "<div id='message' class='updated'><p>提交【{$_POST['goodsbiz_name']}】产成品业务成功</p></div>";
        } else {
            echo "<div id='message' class='updated'><p>请完成(必填)选项后再提交</p></div>";
        }
    } elseif($_SESSION['goodsbiz']['inout'] == '销售或退回') {
        $money = trim($_POST['goodsbiz_money']);
        if ($goods_name && $goods_num && $_POST['goodsbiz_place'] && $_POST['goodsbiz_place'] && is_numeric($money)) {
            $wpdb->insert($acc_prefix . 'goods_biz', array('gb_date' => $_SESSION['goodsbiz']['date'],
                    'gb_out_place' => $_POST['goodsbiz_place'],
                    'gb_num' => $goods_num,
                    'gb_money' => $money,
                    'gb_summary' => trim($_POST['goodsbiz_sum']),
                    'gb_gn_id' => $goods_name
                    )
            );
            echo "<div id='message' class='updated'><p>提交【{$_POST['goodsbiz_name']}】产成品业务成功</p></div>";
        } else {
            echo "<div id='message' class='updated'><p>请完成(必填)选项后再提交</p></div>";
        }
    } elseif($_SESSION['goodsbiz']['inout'] == '移库') {
        if ($goods_name && $goods_num && $_POST['goodsbiz_place1'] && $_POST['goodsbiz_place0']) {
            $wpdb->insert($acc_prefix . 'goods_biz', array('gb_date' => $_SESSION['goodsbiz']['date'],
                    'gb_in_place' => $_POST['goodsbiz_place1'],
                    'gb_out_place' => $_POST['goodsbiz_place0'],
                    'gb_num' => $goods_num,
                    'gb_summary' => trim($_POST['goodsbiz_sum']),
                    'gb_gn_id' => $goods_name
                    )
            );
            echo "<div id='message' class='updated'><p>提交【{$_POST['goodsbiz_name']}】产成品业务成功</p></div>";
        } else {
            echo "<div id='message' class='updated'><p>请完成(必填)选项后再提交</p></div>";
        }
    }

    
} // elseif (isset($_POST['goodsbiz_submit']))


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
                        <label><input name="goodsbiz_inout" type="radio" value="1" style="width: 25px;">入库</label> &nbsp; 
                        <label><input name="goodsbiz_inout" type="radio" value="2" style="width: 25px;">移库</label> &nbsp; 
                        <label><input name="goodsbiz_inout" type="radio" value="3" style="width: 25px;">销售或退回</label>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" name="goodsbiz_1" id="goodsbiz_1" class="button button-primary" value="下 一 步" />
            <input type="reset" name="goodsbiz_reset" id="goodsbiz_reset" class="button button-primary" value="重新填写" />
        </p>
    </form>

    <?php
} // if (!isset($_GET['goodspage']))
elseif ($_GET['goodspage'] == 2) {
    ?>
    <div class="manage-menus">
        <div class="alignleft actions">
            <span><?php echo '当前日期：【' . $_SESSION['goodsbiz']['date'] . '】， 业务类型：【' . $_SESSION['goodsbiz']['inout'] . '】'; ?></span>
        </div>
        <br class="clear" />
    </div>
    <form action="" method="post" name="createuser" id="createuser" class="validate">

        <table class="form-table">
            <tbody>
                <?php
                    switch ($_SESSION['goodsbiz']['inout']) {
                        case '入库':
                            inout('请选择入库地点', 'goodsbiz_place', $acc_prefix);
                            break;
                        case '移库':
                            inout('请选择出库地点', 'goodsbiz_place0', $acc_prefix);
                            inout('请选择入库地点', 'goodsbiz_place1', $acc_prefix);
                            break;
                        case '销售或退回':
                            inout('请选择出库地点', 'goodsbiz_place', $acc_prefix);
                            break;
                    }
                ?>
                <tr class="form-field">
                    <th scope="row"><label for="goodsbiz_name">产成品名称 (必填)</label></th>
                    <td><input type="text" name="goodsbiz_name" id="goodsbiz_name" value="双击选择或输入关键字"></td>
                </tr>
                <?php
                // 自动完成文本框，选择产成品名称
                $get_cols = $wpdb->get_results("SELECT gs_name, gn_name, gn_id FROM {$acc_prefix}goods_name, {$acc_prefix}goods_series "
                        . " WHERE gn_gs_id=gs_id ORDER BY gn_gs_id, gn_name", ARRAY_A);

                $cols_str = '';
                foreach ($get_cols as $value) {
                    $cols_str .= '{ label: "' . $value['gn_name'] . '", category: "' . $value['gs_name'] . ' 系列"},';
                }
                $cols_format = rtrim($cols_str, ',');

                autocompletejs($cols_format, 'goodsbiz_name');
                ?>
                <tr class="form-field">
                    <th scope="row"><label for="goodsbiz_num">数量 (必填,退回填负数)</label></th>
                    <td><input name="goodsbiz_num" type="text" id="goodsbiz_num" value=""></td>
                </tr>
                <?php if ($_SESSION['goodsbiz']['inout'] == '销售或退回') { ?>
                    <tr class="form-field">
                        <th scope="row"><label for="goodsbiz_money">金额 (必填,退回填负数)</label></th>
                        <td><input name="goodsbiz_money" type="text" id="goodsbiz_money" value=""></td>
                    </tr>
                <?php } ?>
                <tr class="form-field">
                    <th scope="row"><label for="goodsbiz_sum">业务摘要</label></th>
                    <td><input name="goodsbiz_sum" type="text" id="goodsbiz_sum" value=""></td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" name="goodsbiz_submit" id="goodsbiz_submit" class="button button-primary" value="提交业务" />
            <input type="reset" name="goodsbiz_r" id="goodsbiz_r" class="button button-primary" value="清空内容" />
            <input type="button" name="goodsbiz_return" id="goodsbiz_return" class="button button-primary" value="返回上级" 
                   onclick="location.href = location.href.substring(0, location.href.indexOf('&goods'))" />
        </p>
    </form>
    <?php
} // elseif ($_GET['goodspage'] == 2)
//
//
// 所有页面显示的最近 10 条业务流水
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
$results_goodsbiz = $wpdb->get_results("SELECT gb_id, gb_date, gs_name, gn_name, gb_in_place, gb_out_place, gb_num, gb_money, gb_summary "
        . " FROM {$acc_prefix}goods_biz, {$acc_prefix}goods_name, {$acc_prefix}goods_series "
        . " WHERE gb_gn_id = gn_id AND gn_gs_id = gs_id "
        . " ORDER BY gb_id DESC LIMIT 10 ", ARRAY_A);

foreach ($results_goodsbiz as $gb) {
    $in_place = id2name("gp_name", "{$acc_prefix}goods_place", $gb['gb_in_place'], "gp_id");
    $out_place = id2name("gp_name", "{$acc_prefix}goods_place", $gb['gb_out_place'], "gp_id");
    $number = number_format($gb['gb_num'], 0);
    $money = ($gb['gb_money'] == 0) ? '' : number_format($gb['gb_money'], 2);
    if ($gb['gb_in_place'] > 0 && $gb['gb_out_place'] > 0) {
        echo "<tr class='alternate'>
                    <td class='name'>{$gb['gb_id']}</td>
                    <td class='name'>{$gb['gb_date']}</td>
                    <td class='name'>{$gb['gs_name']}</td>
                    <td class='name'>{$gb['gn_name']}</td>
                    <td class='name'>{$out_place}</td>
                    <td class='name'></td>
                    <td class='name'>{$number}</td>
                    <td class='name'>{$money}</td>
                    <td class='name'>{$gb['gb_summary']}</td>
                </tr><tr class='alternate'>
                    <td class='name'>{$gb['gb_id']}</td>
                    <td class='name'>{$gb['gb_date']}</td>
                    <td class='name'>{$gb['gs_name']}</td>
                    <td class='name'>{$gb['gn_name']}</td>
                    <td class='name'>{$in_place}</td>
                    <td class='name'>{$number}</td>
                    <td class='name'></td>
                    <td class='name'>{$money}</td>
                    <td class='name'>{$gb['gb_summary']}</td>
                </tr>";
    } else {
        if ($gb['gb_in_place'] > 0) {
            $in = $number;
            $out = '';
        } elseif ($gb['gb_out_place'] > 0) {
            $in = '';
            $out = $number;
        }
        $place = $in_place . $out_place; // 其中一个为空
        echo "<tr class='alternate'>
                    <td class='name'>{$gb['gb_id']}</td>
                    <td class='name'>{$gb['gb_date']}</td>
                    <td class='name'>{$gb['gs_name']}</td>
                    <td class='name'>{$gb['gn_name']}</td>
                    <td class='name'>{$place}</td>
                    <td class='name'>{$in}</td>
                    <td class='name'>{$out}</td>
                    <td class='name'>{$money}</td>
                    <td class='name'>{$gb['gb_summary']}</td>
                </tr>";
    }
}
echo '</tbody></table>';

mixfs_bottom(); // 框架页面底部


/**
 * 生成仓库、店铺 下拉框
 * @param type $title
 * @param type $tag
 */
function inout($title, $tag, $acc_prefix) {
    global $wpdb;
    $places = $wpdb->get_results("SELECT gp_id, gp_name FROM {$acc_prefix}goods_place ORDER BY gp_id", ARRAY_A);

    echo "<tr class='form-field'>
                    <th scope='row'><label for='{$tag}'>{$title} (必填)</label></th>
                    <td>
                        <select name='{$tag}' id='{$tag}' style='width: 25em;'>
                            <option selected='selected' value='0'>{$title}</option>";

    foreach ($places as $p) {
        printf('<option value="%d">%s</option>', $p['gp_id'], $p['gp_name']);
    }
    echo '</select></td></tr>';
}
