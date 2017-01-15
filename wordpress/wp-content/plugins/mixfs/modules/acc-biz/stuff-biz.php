<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

mixfs_top('原材料业务', $_SESSION['acc_name']);

global $wpdb;

$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

if (isset($_POST['stuffbiz_1'])) {

    $_SESSION['stuffbiz']['date']=0;
    $_SESSION['stuffbiz']['inout']=0;
    
    $date_arr = explode('-', $_POST['stuffbiz_date']);
    if (count($date_arr) == 3 && checkdate($date_arr[1], $date_arr[2], $date_arr[0])) {
        $_SESSION['stuffbiz']['date'] = $_POST['stuffbiz_date'];
    } else {
        echo '<div id="message" class="updated"><p>日期格式不正确，请重新操作</p></div>';
    }
    if ($_POST['stuffbiz_inout'] > 0) {
        $_SESSION['stuffbiz']['inout'] = ( $_POST['stuffbiz_inout'] == 1) ? '入库' : '出库';
    } else {
        echo '<div id="message" class="updated"><p>请选择出入库再继续操作</p></div>';
    }

    if ($_SESSION['stuffbiz']['date'] && $_SESSION['stuffbiz']['inout']) {
        echo "<script type='text/javascript'>location.href=location.href + '&stuffpage=2';</script>";
    }
} // if (isset($_POST['stuffbiz_1']))
elseif (isset($_POST['stuffbiz_2'])) {
    $stuff_name = $wpdb->get_var("SELECT sn_id FROM {$acc_prefix}stuff_name WHERE sn_name = '{$_POST['stuffbiz_name']}'");
    $stuff_num = is_numeric($_POST['stuffbiz_num']) ? $_POST['stuffbiz_num'] : 0;
    $in_out = ($_SESSION['stuffbiz']['inout'] == '入库') ? 'sb_in' : ( ($_SESSION['stuffbiz']['inout'] == '出库') ? 'sb_out' : 0);
    if ($stuff_name && $stuff_num && $in_out) {
        $wpdb->insert($acc_prefix . 'stuff_biz', array('sb_date' => $_SESSION['stuffbiz']['date'],
            $in_out => $stuff_num,
            'sb_money' => trim($_POST['stuffbiz_money']),
            'sb_p_id' => $_POST['stuffbiz_provider'],
            'sb_c_id' => $_POST['stuffbiz_container'],
            'sb_summary' => trim($_POST['stuffbiz_sum']),
            'sb_sn_id' => $stuff_name
                )
        );
        echo "<div id='message' class='updated'><p>提交【{$_POST['stuffbiz_name']}】原材料业务成功</p></div>";
    } else {
        echo "<div id='message' class='updated'><p>请完成(必填)选项后再提交</p></div>";
    }
} // elseif (isset($_POST['stuffbiz_2']))


if (!isset($_GET['stuffpage'])) {
    date_from_to("stuffbiz_date");
    ?>

    <form action="" method="post" name="createuser" id="createuser" class="validate">

        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th scope="row"><label for="stuffbiz_date">选择业务日期 <span class="description">(必填)</span></label></th>
                    <td><input name="stuffbiz_date" type="text" id="stuffbiz_date" value="<?php echo $_SESSION['stuffbiz']['date']; ?>" aria-required="true"></td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row"><label for="email">选择入库出库 <span class="description">(必填)</span></label></th>
                    <td>
                        <label><input name="stuffbiz_inout" type="radio" value="1" style="width: 25px;">入库</label> &nbsp; 
                        <label><input name="stuffbiz_inout" type="radio" value="2" style="width: 25px;">出库</label>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" name="stuffbiz_1" id="stuffbiz_1" class="button button-primary" value="下 一 步" />
            <input type="reset" name="stuffbiz_reset" id="stuffbiz_reset" class="button button-primary" value="重新填写" />
        </p>
    </form>

    <?php
} // if (!isset($_GET['stuffpage']))
elseif ($_GET['stuffpage'] == 2) {
    ?>
    <div class="manage-menus">
        <div class="alignleft actions">
            <span><?php echo '当前日期：【' . $_SESSION['stuffbiz']['date'] . '】， 业务类型：【' . $_SESSION['stuffbiz']['inout'] . '】'; ?></span>
        </div>
        <br class="clear" />
    </div>
    <form action="" method="post" name="createuser" id="createuser" class="validate">

        <table class="form-table">
            <tbody>
                <tr class="form-field">
                    <th scope="row"><label for="stuffbiz_provider">供应商</label></th>
                    <td>
                        <select name="stuffbiz_provider" id="stuffbiz_provider" style="width: 25em;">
                            <option selected="selected" value="0">请选择供应商</option>
    <?php
    $providers = $wpdb->get_results("SELECT p_id, p_name FROM {$acc_prefix}provider ORDER BY p_name", ARRAY_A);
    foreach ($providers as $p) {
        printf('<option value="%d">%s</option>', $p['p_id'], $p['p_name']);
    }
    ?>
                        </select>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="stuffbiz_container">货柜号</label></th>
                    <td>
                        <select name="stuffbiz_container" id="stuffbiz_container" style="width: 25em;">
                            <option selected="selected" value="0">请选择货柜号</option>
    <?php
    $containers = $wpdb->get_results("SELECT c_id, c_no FROM {$acc_prefix}container ORDER BY c_no", ARRAY_A);
    foreach ($containers as $c) {
        printf('<option value="%d">%s</option>', $c['c_id'], $c['c_no']);
    }
    ?>
                        </select>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="stuffbiz_name">原材料名称 (必填)</label></th>
                    <td><input type="text" name="stuffbiz_name" id="stuffbiz_name" value="双击选择或输入关键字"></td>
                </tr>
    <?php
    // 自动完成文本框，选择原材料名称
    $get_cols = $wpdb->get_results("SELECT ss_name, sn_name, sn_id FROM {$acc_prefix}stuff_name, {$acc_prefix}stuff_series "
            . " WHERE sn_ss_id=ss_id ORDER BY sn_ss_id, sn_name", ARRAY_A);

    $cols_str = '';
    foreach ($get_cols as $value) {
        $cols_str .= '{ label: "' . $value['sn_name'] . '", category: "' . $value['ss_name'] . ' 系列"},';
    }
    $cols_format = rtrim($cols_str, ',');

    autocompletejs($cols_format, 'stuffbiz_name');
    ?>
                <tr class="form-field">
                    <th scope="row"><label for="stuffbiz_num">数量 (必填)</label></th>
                    <td><input name="stuffbiz_num" type="text" id="stuffbiz_num" value=""></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="stuffbiz_money">金额小计</label></th>
                    <td><input name="stuffbiz_money" type="text" id="stuffbiz_money" value=""></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="stuffbiz_sum">业务摘要</label></th>
                    <td><input name="stuffbiz_sum" type="text" id="stuffbiz_sum" value=""></td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" name="stuffbiz_2" id="stuffbiz_2" class="button button-primary" value="提交业务" />
            <input type="reset" name="stuffbiz_r" id="stuffbiz_r" class="button button-primary" value="清空内容" />
            <input type="button" name="stuffbiz_return" id="stuffbiz_return" class="button button-primary" value="返回上级" 
                   onclick="location.href = location.href.substring(0, location.href.indexOf('&stuff'))" />
        </p>
    </form>

    <?php
} // elseif ($_GET['stuffpage'] == 2)


echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column' style="">日期</th>
                    <th class='manage-column'  style="">系列</th>
                    <th class='manage-column'  style="">型号</th>
                    <th class='manage-column'  style="">入库</th>
                    <th class='manage-column'  style="">出库</th>
                    <th class='manage-column'  style="">金额</th>
                    <th class='manage-column'  style="">供货商</th>
                    <th class='manage-column'  style="">货柜号</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column' style="">日期</th>
                    <th class='manage-column'  style="">系列</th>
                    <th class='manage-column'  style="">型号</th>
                    <th class='manage-column'  style="">入库</th>
                    <th class='manage-column'  style="">出库</th>
                    <th class='manage-column'  style="">金额</th>
                    <th class='manage-column'  style="">供货商</th>
                    <th class='manage-column'  style="">货柜号</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

// 原材料业务列表
$results_stuffbiz = $wpdb->get_results("SELECT sb_id, sb_date, ss_name, sn_name, sb_in, sb_out, sb_money, sb_p_id, sb_c_id, sb_summary "
        . " FROM {$acc_prefix}stuff_biz, {$acc_prefix}stuff_name, {$acc_prefix}stuff_series "
        . " WHERE sn_ss_id = ss_id AND sb_sn_id = sn_id ORDER BY sb_id DESC LIMIT 10 ", ARRAY_A);

foreach ($results_stuffbiz as $sb) {
    $provider = id2name("p_name", "{$acc_prefix}provider", $sb['sb_p_id'], "p_id");
    $container = id2name("c_no", "{$acc_prefix}container", $sb['sb_c_id'], "c_id");
    $in = ($sb['sb_in'] == 0) ? '' : number_format($sb['sb_in'], 2);
    $out = ($sb['sb_out'] == 0) ? '' : number_format($sb['sb_out'], 2);
    $money = ($sb['sb_money'] == 0) ? '' : number_format($sb['sb_money'], 2);
    echo "<tr class='alternate'>
                <td class='name'>{$sb['sb_id']}</td>
                <td class='name'>{$sb['sb_date']}</td>
                <td class='name'>{$sb['ss_name']}</td>
                <td class='name'>{$sb['sn_name']}</td>
                <td class='name'>{$in}</td>
                <td class='name'>{$out}</td>
                <td class='name'>{$money}</td>
                <td class='name'>{$provider}</td>
                <td class='name'>{$container}</td>
                <td class='name'>{$sb['sb_summary']}</td>
            </tr>";
}
echo '</tbody></table>';

mixfs_bottom(); // 框架页面底部
