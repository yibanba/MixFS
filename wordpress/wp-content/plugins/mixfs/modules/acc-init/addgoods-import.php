<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

include_once ( MixFS_PATH . 'core/phpexcel/PHPExcel.php');

?>
<div class="manage-menus">
    <div class="alignleft actions">
        <span>
            <?php
            echo '当前日期：【' . date('Y-m-d') . '】， 业务类型：添加【' . $_SESSION['goods_series'] . '】产品信息';
            ?>
        </span>
    </div>
    <br class="clear" />
</div>

<?php

$upload_dir = upload_dir(); // 获取上传目录

$goods_names = $wpdb->get_results("SELECT gn_id, gn_name FROM {$acc_prefix}goods_name", ARRAY_A);
$gn_kv = array(); // 产成品名称键值对， 品名=>ID
foreach ($goods_names as $v) {
    $gn_kv[$v['gn_name']] = $v['gn_id'];
}
upload_form();

if ($_POST['submit_upload']) {
    if (($_FILES["file"]["type"] == "application/vnd.ms-excel") || ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")) {
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
        } else {

            if ($_FILES["file"]["type"] == "application/vnd.ms-excel") {
                move_uploaded_file($_FILES["file"]["tmp_name"], $upload_dir . "/" . '2003_addgoods.xls');
                $fn = $upload_dir . '/2003_addgoods.xls';
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $upload_dir . "/" . '2007_addgoods.xlsx');
                $fn = $upload_dir . '/2007_addgoods.xlsx';
            }
            $err_num = read_excel($fn, $gn_kv);
            if ($err_num == -1) {
                echo "<div id='message' class='updated'>"
                . "<p>上传文件名：{$_FILES["file"]["name"]} | 文件大小：" . ($_FILES["file"]["size"] / 1024) . " Kb | 文件为空，请输入正确的数据后再次提交</p>"
                . "</div>";
                $_SESSION['addgoods']['file'] = $fn;
            } else {
                echo "<div id='message' class='updated'>"
                . "<p>上传文件名：{$_FILES["file"]["name"]} | 文件大小：" . ($_FILES["file"]["size"] / 1024) . " Kb | 错误数：{$err_num} 行</p>"
                . "</div>";
                if ($err_num == 0) {
                    input_form();
                    $_SESSION['addgoods']['file'] = $fn;
                } else {
                    echo "<div id='message' class='updated'><p>请检查并更正报错的业务，包括：多余的符号、空格、大小写、货号重复 ...</p></div>";
                }
            }
        }
    } else {
        echo "<div id='message' class='updated'><p>请选择并上传 Excel 类型文件，不支持其它的文件格式！</p></div>";
    }
} // if ($_POST['submit_upload'])
elseif (isset($_POST['import_file'])) {

    $total = input_excel($_SESSION['addgoods']['file'], $_SESSION['goods_series_id'], $acc_prefix);

    echo "<div id='message' class='updated'><p>共成功提交 {$total} 条记录</p></div>";
}
?>


<?php
//******************************************************************************

/**
 * 导入excel文件数据到数据库
 * @param type $ver path + filename + .xls|.xlsx
 *  5列：系列名，A品名，价格，C摘要，B件双
 */
function input_excel($ver, $series_id, $acc_prefix) {
    global $wpdb;
    $objPHPExcel = PHPExcel_IOFactory::load($ver);
    $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
    $sql = '';
    $row_no = 0;

        $sql .= "INSERT INTO `{$acc_prefix}goods_name` (`gn_gs_id`, `gn_name`, `gn_price`,`gn_summary`, `gn_per_pack`) VALUES ";
        foreach ($sheetData as $value) {
            if ($value['A'] == '品名' && $value['B'] == '件双') {
                continue;
            } elseif ($value['A'] == '' || $value['B'] == '') {
                break;
            } else {
                $sql .= "( $series_id, '{$value['A']}', 1, '{$value['C']}', {$value['B']} ),";
            }
            $row_no++;
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
    $row_no = 1;  // 序号不含标题
    $err_num = 0;
    foreach ($sheetData as $value) {
        $err_tips = '';
        if ($value['A'] == '' || $value['B'] == '') {
            break;
        } else {
            if ($row_no == 1) { // 5列：行数，品名，件双，摘要，错误提示
                echo "<tr style='background-color: #F9F9F9'>"
                . "<th style='padding:5px'>行数</th>"
                . "<th style='padding:5px'>{$value['A']}</th>"
                . "<th style='padding:5px'>{$value['B']}</th>"
                . "<th style='padding:5px'>{$value['C']}</th>"
                . "<th style='padding:5px'>错误提示</th>"
                . "</tr>";
            } else {
                if (array_key_exists((string)$value['A'], $gn_kv)) {
                    ++$err_num;
                    $err_tips = '有此产品';
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
            <input type="button" name="addgoods_return" id="addgoods_return" class="button button-primary" value="返回上级" 
                   onclick="location.href = location.href.substring(0, location.href.indexOf('&addgoods'))" />
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
            <input type="button" name="addgoods_return" id="addgoods_return" class="button button-primary" value="取消并返回上级" 
                   onclick="location.href = location.href.substring(0, location.href.indexOf('&addgoods'))" />
        </div>
        <br class="clear" />
    </div>
</form>
HTMLForm;
}
?>
