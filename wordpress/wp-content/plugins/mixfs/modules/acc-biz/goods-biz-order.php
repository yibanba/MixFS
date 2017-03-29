<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

mixfs_top('录入初始信息', $_SESSION['acc_name']);


echo '<div id="message" class="updated"><p>录入初始信息</p></div>';

if ($_POST['js_submit']) {
    var_dump($_POST['atext']);
    var_dump($_POST['btext']);
}
?>
<form action="" method="post">
    <a href="#" id="AddMoreFileBox" class="btn btn-info">添加更多的input输入框</a>
    <div id="InputsWrapper">  
        <div>
            <input type="text" name="atext[]" id="field_a_1" value="Text a"/>
            <input type="text" name="btext[]" id="field_b_1" value="Text b"/>
            <a href="#" class="removeclass">×</a>
        </div>  
    </div>
    <input type="submit" value="提交" name="js_submit">
</form>
<script type="text/javascript">
    jQuery(document).ready(function ($) {

        var MaxInputs = 8; //maximum input boxes allowed  
        var InputsWrapper = $("#InputsWrapper"); //Input boxes wrapper ID  
        var AddButton = $("#AddMoreFileBox"); //Add button ID  

        var x = InputsWrapper.length; //initlal text box count  
        var FieldCount = 1; //to keep track of text box added  

        $(AddButton).click(function (e) {  //on add input button click  

            if (x <= MaxInputs) { //max input box allowed  

                FieldCount++; //text box added increment  
                //add input box  
                $(InputsWrapper).append('<div><input type="text" name="atext[]" id="field_a_' + FieldCount + '" value="Text ' + FieldCount + '"/><input type="text" name="btext[]" id="field_b_' + FieldCount + '" value="Text ' + FieldCount + '"/><a href="#" class="removeclass">×</a></div>');
                x++; //text box increment  
            }
            return false;
        });

        $("body").on("click", ".removeclass", function (e) { //user click on remove text  
            if (x > 1) {
                $(this).parent('div').remove(); //remove text box  
                x--; //decrement textbox  
            }
            return false;
        })

    });
</script>  

<div class="ui-widget">
  <label for="tags">标签：</label><br />
  <input class="tag" name="price[]" value="单价">
	<input class="tag" name="qty[]" value="数量">
	<input class="tag" name="sums[]" value="小计"  disabled="disabled"><br />
	<input class="tag" name="price[]">
	<input class="tag" name="qty[]">
	<input class="tag" name="sums[]" disabled="disabled"><br />
	
	<label for="tags">数量合计：</label>
	<input class="ti" id="amount"><br />
	<label for="tags">金额合计：</label>
	<input class="ti" id="total"><br />
</div>
 
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var total_qty = 0;
        var total_sum = 0;

        $(".tag").keyup(function () {
            var arr_price = $("input[name='price[]']").toArray();
            var arr_qty = $("input[name='qty[]']").toArray();
            var arr_sums = $("input[name='sums[]']").toArray();

            var i = 0;
            var amount = 0;
            var total = 0;
            $("input[name='sums[]']").each(function () {
                var p = (arr_price[i].value > 0) ? arr_price[i].value : 0;
                var q = (arr_qty[i].value > 0) ? arr_qty[i].value : 0;
                $(this).val(parseInt(p * q));
                i++;
                amount += q * 1;
                total += p * q;
            });
            $("#amount").val(amount);
            $("#total").val(total);
        });
    });
</script>
<?php
mixfs_bottom();
