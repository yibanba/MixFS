<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

include_once ( MixFS_PATH . 'core/phpexcel/PHPExcel.php');

$upload_dir = upload_dir();

$goods_names = $wpdb->get_results("SELECT gn_id, gn_name FROM {$acc_prefix}goods_name", ARRAY_A);
$gn_kv = array(); // 产成品名称键值对， 品名=>ID
foreach ($goods_names as $v) {
    $gn_kv[$v['gn_name']] = $v['gn_id'];
}
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
    <br class="clear" />
</div>

<?php
upload_form();

if ($_POST['submit_upload']) {
    if (($_FILES["file"]["type"] == "application/vnd.ms-excel") || ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")) {
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
        } else {

            if ($_FILES["file"]["type"] == "application/vnd.ms-excel") {
                move_uploaded_file($_FILES["file"]["tmp_name"], $upload_dir . "/" . '2003.xls');
                $fn = $upload_dir . '/2003.xls';
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $upload_dir . "/" . '2007.xlsx');
                $fn = $upload_dir . '/2007.xlsx';
            }
            $err_num = read_excel($fn, $gn_kv);
            if ($err_num == -1) {
                echo "<div id='message' class='updated'>"
                . "<p>上传文件名：{$_FILES["file"]["name"]} | 文件大小：" . ($_FILES["file"]["size"] / 1024) . " Kb | 文件为空，请输入正确的数据后再次提交</p>"
                . "</div>";
                $_SESSION['goodsbiz']['file'] = $fn;
            } else {
                echo "<div id='message' class='updated'>"
                . "<p>上传文件名：{$_FILES["file"]["name"]} | 文件大小：" . ($_FILES["file"]["size"] / 1024) . " Kb | 错误数：{$err_num} 行</p>"
                . "</div>";
                if ($err_num == 0) {
                    input_form();
                    $_SESSION['goodsbiz']['file'] = $fn;
                } else {
                    echo "<div id='message' class='updated'><p>请检查并更正报错的业务，包括：多余的符号、空格、大小写、未登记的新产品 ... 要和已登记的(产成品名称)完全一致！</p></div>";
                }
            }
        }
    } else {
        echo "<div id='message' class='updated'><p>请选择并上传 Excel 类型文件，不支持其它的文件格式！</p></div>";
    }
} // if ($_POST['submit_upload'])
elseif (isset($_POST['import_file'])) {

    $total = input_excel($_SESSION['goodsbiz']['file'], $_SESSION['goodsbiz']['inout'], $gn_kv, $acc_prefix);

    echo "<div id='message' class='updated'><p>共成功提交 {$total} 条记录</p></div>";
}
?>


<?php
//******************************************************************************

/**
 * 导入excel文件数据到数据库
 * @param type $ver path + filename + .xls|.xlsx
 * @param type $biz_type 业务类型：入库，移库，销售或退回
 */
function input_excel($ver, $biz_type, $gn_kv, $acc_prefix) {
    global $wpdb;
    $objPHPExcel = PHPExcel_IOFactory::load($ver);
    $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
    $sql = '';
    $row_no = 0;

    if ($biz_type == '入库') {
        $sql .= "INSERT INTO `{$acc_prefix}goods_biz` (`gb_date`, `gb_in`, `gb_gp_id`, `gb_gn_id`) VALUES ";
        foreach ($sheetData as $value) {
            if ($value['A'] == '品名' && $value['B'] == '数量') {
                continue;
            } elseif ($value['A'] == '' || $value['B'] == '') {
                break;
            } else {
                $sql .= "( '{$_SESSION['goodsbiz']['date']}', {$value['B']}, {$_SESSION['goodsbiz']['in']}, {$gn_kv[$value['A']]} ),";
            }
            $row_no++;
        }
    } elseif ($biz_type == '移库') {
        $sql .= "INSERT INTO `{$acc_prefix}goods_biz` (`gb_date`, `gb_in`, `gb_out`, `gb_gp_id`, `gb_gn_id`) VALUES ";
        foreach ($sheetData as $value) {
            if ($value['A'] == '品名' && $value['B'] == '数量') {
                continue;
            } elseif ($value['A'] == '' || $value['B'] == '') {
                break;
            } else {
                $sql .= "( '{$_SESSION['goodsbiz']['date']}', 0, {$value['B']}, {$_SESSION['goodsbiz']['out']}, {$gn_kv[$value['A']]} ),";
                $sql .= "( '{$_SESSION['goodsbiz']['date']}', {$value['B']}, 0, {$_SESSION['goodsbiz']['in']}, {$gn_kv[$value['A']]} ),";
            }
            $row_no++;
        }
    } elseif ($biz_type == '销售或退回') {
        $sql .= "INSERT INTO `{$acc_prefix}goods_biz` (`gb_date`, `gb_out`, `gb_money`, `gb_gp_id`, `gb_gn_id`) VALUES ";
        foreach ($sheetData as $value) {
            if ($value['A'] == '品名' && $value['B'] == '数量') {
                continue;
            } elseif ($value['A'] == '' || $value['B'] == '' || $value['C'] == '') {
                break;
            } else {
                $sql .= "( '{$_SESSION['goodsbiz']['date']}', {$value['B']}, {$value['C']}, {$_SESSION['goodsbiz']['out']}, {$gn_kv[$value['A']]} ),";
            }
            $row_no++;
        }
    }
    $sql_format = rtrim($sql, ",");
    $wpdb->query($sql_format);

    return $row_no;
}

/**
 * 读取上传的 Excel 文件 并列表显示
 * @param type $ver  path + filename + .xls|.xlsx
 * @param type $goods_names 产品名称数组
 * @return int 错误数量
 */
function read_excel($ver, $gn_kv) {
    $objPHPExcel = PHPExcel_IOFactory::load($ver);
    $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
    //var_dump($sheetData);
    echo '<table border="1" width="500px" style="margin:20px 0px;">';
    $row_no = 1;
    $err_num = 0;
    foreach ($sheetData as $value) {
        $err_tips = '';
        if ($value['A'] == '' || $value['B'] == '') {
            break;
        } else {
            if ($row_no == 1) {
                echo "<tr style='background-color: #F9F9F9'>"
                . "<th style='padding:5px'>1</th>"
                . "<th style='padding:5px'>{$value['A']}</th>"
                . "<th style='padding:5px'>{$value['B']}</th>"
                . "<th style='padding:5px'>{$value['C']}</th>"
                . "<th style='padding:5px'>错误提示</th>"
                . "</tr>";
            } else {
                if (!array_key_exists($value['A'], $gn_kv)) {
                    ++$err_num;
                    $err_tips = '没有此产品';
                }
                echo "<tr>"
                . "<th style='padding:5px'>{$row_no}</th>"
                . "<td style='padding:5px'>{$value['A']}</td>"
                . "<td style='padding:5px'>{$value['B']}</td>"
                . "<td style='padding:5px'>{$value['C']}</td>"
                . "<td style='padding:5px'>{$err_tips}</td>"
                . "</tr>";
            }
            $row_no++;
        }
    }
    echo '</table>';
    return ($row_no >= 2) ? $err_num : (-1);
}

/**
 * 上传 Excel 文件 ，如目的文件夹不存在创建
 * @return string ，创建失败返回空
 */
function upload_dir() {
    $uploads = wp_upload_dir(); // Array
    $upload_dir = $uploads['basedir'] . '/mixfs-specific';

    if (!is_dir($upload_dir)) {
        wp_mkdir_p($upload_dir);
        return $upload_dir;
    }
    return $upload_dir;
}

/**
 * 显示上传表单 html
 */
function upload_form() {

    echo <<<HTMLForm
    
    <form action="" method="post" enctype="multipart/form-data">
    <div class="manage-menus">
        <div class="alignleft actions">
            <input type="file" name="file" id="file" /> 
            <input type="submit" name="submit_upload" class="button button-primary" value="上传文件" />
            <input type="button" name="goodsbiz_return" id="goodsbiz_return" class="button button-primary" value="返回上级" 
                   onclick="location.href = location.href.substring(0, location.href.indexOf('&goods'))" />
        </div>
        <br class="clear" />
    </div>
</form>
HTMLForm;
}

/**
 * 显示 提交已导入文件 表单 + 按钮
 */
function input_form() {
    echo <<<HTMLForm
    <form action="" method="post">
    <div class="manage-menus">
        <div class="alignleft actions">
            <input type="submit" name="import_file" class="button button-primary" value="提交已导入文件" />
            <input type="button" name="goodsbiz_return" id="goodsbiz_return" class="button button-primary" value="取消并返回上级" 
                   onclick="location.href = location.href.substring(0, location.href.indexOf('&goods'))" />
        </div>
        <br class="clear" />
    </div>
</form>
HTMLForm;
}
?>
