<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * 判断账套表是否存在
 */

function table_name_exists($acc_prefix) {
    global $wpdb;

    $acc_names = $wpdb->get_results("SELECT ma_tbl_prefix FROM {$wpdb->prefix}mixfs_accounts", ARRAY_N);

    foreach ($acc_names as $name) {
        if ($acc_prefix == $name[0]) {
            return TRUE;    // 表名存在
        }
    }

    return FALSE;
}

/**
 * 
 * 用户ID转用户名
 */
function ID2user_login($str_user_ID) {
    $ID_arr = explode(",", $str_user_ID);
    $str_user_login = "";
    foreach ($ID_arr as $user_ID) {
        $t = get_userdata($user_ID);
        $str_user_login .= $t->user_login . " | ";
    }
    return rtrim(trim($str_user_login), " | ");
}

/**
 * id转name
 * 比如 产品ID=>品名，费用ID=>费用名称
 */
function id2name($field_name, $table, $source_id, $tbl_id) {
    global $wpdb;
    $name = $wpdb->get_var("SELECT {$field_name} FROM {$table} WHERE {$tbl_id}='{$source_id}'");

    return $name;
}

/**
 * 
 * 创建前台页面: /?page_id=xxx
 */
function mixfs_create_page($slug, $option, $page_title = '', $page_content = '', $post_parent = 0) {
    global $wpdb;
    $option_value = get_option($option);
    if ($option_value > 0 && get_post($option_value))
        return;
    $page_found = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", $slug));
    if ($page_found) {
        if (!$option_value)
            update_option($option, $page_found);
        return;
    }
    $page_data = array(
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 1,
        'post_name' => $slug,
        'post_title' => $page_title,
        'post_content' => $page_content,
        'post_parent' => $post_parent,
        'comment_status' => 'closed'
    );
    $page_id = wp_insert_post($page_data);
    update_option($option, $page_id);
}

/**
 * 
 * 创建数据库表: wp_mixfs_xxx
 */
function mixfs_table_install($tbl_name, $tbl_schema) {
    global $wpdb;
    $wpdb->hide_errors();
    $collate = '';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset))
            $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $collate .= " COLLATE $wpdb->collate";
    }
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $mixfs_table_schema = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mixfs_{$tbl_name} {$tbl_schema} {$collate}";
    dbDelta($mixfs_table_schema);
}

/**
 * 日期文本框
 * 2 个参数设置起止时间 2 个文本框
 * 默认 1 个参数设置
 * 参数为 <input id="tag_from" name="tag_from">
 */
function date_from_to($tag_from, $tag_to = '') {

    if ('' == $tag_to) {

        echo <<<DateJS
<script type="text/javascript">
jQuery(document).ready(function($) {
    $( "#{$tag_from}" ).datepicker({
        defaultDate: "-1M",
        numberOfMonths: 2,
        minDate: new Date(2015, 1 - 1, 1),
        maxDate: "+1d",
        monthNames: [ "一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月" ],
        dayNamesMin: [ "日","一","二","三","四","五","六" ],
        onClose: function( selectedDate ) {
            $( "#{$tag_from}" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        }
    });
});
</script>
DateJS;
    } else {

        echo <<<DateJS
<script type="text/javascript">
jQuery(document).ready(function($) {
    $( "#{$tag_from}" ).datepicker({
      defaultDate: "-1M",
      numberOfMonths: 2,
        monthNames: [ "一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月" ],
        dayNamesMin: [ "日","一","二","三","四","五","六" ],
      onClose: function( selectedDate ) {
        $( "#{$tag_to}" ).datepicker( "option", "minDate", selectedDate );
        $( "#{$tag_from}" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
      }
    });
    $( "#{$tag_to}" ).datepicker({
        defaultDate: "-1M",
        numberOfMonths: 2,
        monthNames: [ "一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月" ],
        dayNamesMin: [ "日","一","二","三","四","五","六" ],
        onClose: function( selectedDate ) {
            $( "#{$tag_from}" ).datepicker( "option", "maxDate", selectedDate );
            $( "#{$tag_to}" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        }
    });
});
</script>
DateJS;
    }
}

// date_from_to

/**
 * 自动提示补全文本框
 * 双击显示全部
 * 用来提示 产品名称或费用项目 ...
 */
function autocompletejs($cols_format, $tag) {
    echo <<<autoJS
<script type="text/javascript">
jQuery(document).ready(function($) {

    $.widget( "custom.catcomplete", $.ui.autocomplete, {
        _create: function() {
            this._super();
            this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
        },
        _renderMenu: function( ul, items ) {
            var that = this,
            currentCategory = "";
            $.each( items, function( index, item ) {
                var li;
                if ( item.category != currentCategory ) {
                    ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
                    currentCategory = item.category;
                }
                li = that._renderItemData( ul, item );
                if ( item.category ) {
                li.attr( "aria-label", item.category + " : " + item.label );
                }
            });
        }
    });

    $( "#{$tag}" ).catcomplete({
      delay: 0,
      source: [$cols_format]
    });
    $( "#{$tag}" ).catcomplete({
        minLength: 0
    }).dblclick(function () {
        $(this).catcomplete('search', '');
    });
    $( "#{$tag}" ).click(function(){  
     $(this).val("");
    });
});
</script>
autoJS;
}

// autocompletejs

/**
 * 所有页面输出的框架
 * 顶部：mixfs_top()
 * 底部：mixfs_bottom()
 */
function mixfs_top($title, $acc_name = '') {
    $url_entrance = admin_url('admin.php?page=mixfs-entrance'); // 所有页面共用的返回入口链接URL == mixfs-entrance

    $html = '<div class="wrap">'
            . '<div id="icon-themes" class="icon32"><br></div>'
            . '<h2 class="nav-tab-wrapper">';

    if ($_GET['page'] == 'mixfs-entrance') {
        $html .= '<a href="' . $url_entrance . '" class="nav-tab nav-tab-active">财务软件入口</a>';
    } else {
        if (!isset($_SESSION['acc_tbl']) and ! isset($_SESSION['acc_name'])) {
            echo "<script type='text/javascript'>location.href='$url_entrance'</script>";
            exit();
        }
        $html .= '<a href="' . $url_entrance . '" class="nav-tab">财务软件入口 &#187 ' . $acc_name . '</a>';
        $html .= '<a href="" class="nav-tab nav-tab-active">' . $title . '</a>';
    }

    echo $html . '<a href="' . wp_logout_url() . '" class="nav-tab">退出软件</a></h2>'
    . '<br />';
}

/**
 * MixPage 底部代码 + JS(选中行高亮)
 * 
 */
function mixfs_bottom() {
    ?>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            if ($(".alternate").length > 0) {
                var bg = '';
                $(".alternate:odd").css("background-color", "#F7F7F7");

                $(".alternate").mouseover(function () {
                    bg = $(this).css("background-color");
                    $(this).css("background-color", "lightblue");
                });
                $(".alternate").mouseout(function () {
                    $(this).css("background-color", bg);
                });
            }
        });
    </script>

<?php } ?>