/**
 * Created by Administrator on 2017/5/24.
 */

$(function () {
    var del_ids = '';
    var idsArr = new Array();
    //全局的checkbox选中和未选中的样式
    var $allCheckbox = $('input[type="checkbox"]'),     //全局的全部checkbox
        $wholeChexbox = $('.whole_check'),
        $cartBox = $('.cartBox'),                       //每个商铺盒子
        $shopCheckbox = $('.shopChoice'),               //每个商铺的checkbox
        $sonCheckBox = $('.son_check');                 //每个商铺下的商品的checkbox
    $allCheckbox.click(function () {
        if ($(this).is(':checked')) {
            $(this).next('label').addClass('mark');
        } else {
            $(this).next('label').removeClass('mark');
        }
    });

    //===============================================全局全选与单个商品的关系================================
    $wholeChexbox.click(function () {
        var $checkboxs = $cartBox.find('input[type="checkbox"]');
        if ($(this).is(':checked')) {
            $checkboxs.prop("checked", true);
            $checkboxs.next('label').addClass('mark');
            var child_length = $('.cartBox').find('.son_check').length;
            for(var i=0;i<child_length;i++){
                changeIds($('.cartBox').find('.son_check:eq('+i+')').val(),1);
            }
        } else {
            del_ids = '';
            idsArr = new Array();
            $checkboxs.prop("checked", false);
            $checkboxs.next('label').removeClass('mark');
        }
        totalMoney();
    });


    $sonCheckBox.each(function () {
        $(this).click(function () {
            if ($(this).is(':checked')) {
                //判断：所有单个商品是否勾选
                var len = $sonCheckBox.length;
                var num = 0;
                $sonCheckBox.each(function () {
                    if ($(this).is(':checked')) {
                        num++;
                    }
                });
                if (num == len) {
                    $wholeChexbox.prop("checked", true);
                    $wholeChexbox.next('label').addClass('mark');
                }
                changeIds($(this).val(),1);
            } else {
                //单个商品取消勾选，全局全选取消勾选
                $wholeChexbox.prop("checked", false);
                $wholeChexbox.next('label').removeClass('mark');
                changeIds($(this).val(),2);
            }
        })
    })

    //=======================================每个店铺checkbox与全选checkbox的关系/每个店铺与其下商品样式的变化===================================================

    //店铺有一个未选中，全局全选按钮取消对勾，若店铺全选中，则全局全选按钮打对勾。
    $shopCheckbox.each(function () {
        $(this).click(function () {
            if ($(this).is(':checked')) {
                //判断：店铺全选中，则全局全选按钮打对勾。
                var len = $shopCheckbox.length;
                var num = 0;
                $shopCheckbox.each(function () {
                    if ($(this).is(':checked')) {
                        num++;
                    }
                });
                if (num == len) {
                    $wholeChexbox.prop("checked", true);
                    $wholeChexbox.next('label').addClass('mark');
                }

                //店铺下的checkbox选中状态
                $(this).parents('.cartBox').find('.son_check').prop("checked", true);
                var child_length = $(this).parents('.cartBox').find('.son_check').length;
                for(var i=0;i<child_length;i++){
                    changeIds($(this).parents('.cartBox').find('.son_check:eq('+i+')').val(),1);
                }
                $(this).parents('.cartBox').find('.son_check').next('label').addClass('mark');
            } else {
                del_ids = '';
                idsArr = new Array();
                //否则，全局全选按钮取消对勾
                $wholeChexbox.prop("checked", false);
                $wholeChexbox.next('label').removeClass('mark');

                //店铺下的checkbox选中状态
                $(this).parents('.cartBox').find('.son_check').prop("checked", false);
                $(this).parents('.cartBox').find('.son_check').next('label').removeClass('mark');
            }
            totalMoney();
        });
    });


    //========================================每个店铺checkbox与其下商品的checkbox的关系======================================================

    //店铺$sonChecks有一个未选中，店铺全选按钮取消选中，若全都选中，则全选打对勾
    $cartBox.each(function () {
        var $this = $(this);
        var $sonChecks = $this.find('.son_check');
        $sonChecks.each(function () {
            $(this).click(function () {
                if ($(this).is(':checked')) {
                    //判断：如果所有的$sonChecks都选中则店铺全选打对勾！
                    var len = $sonChecks.length;
                    var num = 0;
                    $sonChecks.each(function () {
                        if ($(this).is(':checked')) {
                            num++;
                        }
                    });
                    if (num == len) {
                        $(this).parents('.cartBox').find('.shopChoice').prop("checked", true);
                        $(this).parents('.cartBox').find('.shopChoice').next('label').addClass('mark');
                    }
                } else {
                    //否则，店铺全选取消
                    $(this).parents('.cartBox').find('.shopChoice').prop("checked", false);
                    $(this).parents('.cartBox').find('.shopChoice').next('label').removeClass('mark');
                }
                totalMoney();
            });
        });
    });


    //=================================================商品数量==============================================
    var $plus = $('.plus'),
        $reduce = $('.reduce'),
        $all_sum = $('.sum');
    $plus.click(function () {
        var $inputVal = $(this).prev('input'),
            $count = parseInt($inputVal.val())+1,
            $obj = $(this).parents('.amount_box').find('.reduce'),
            $priceTotalObj = $(this).parents('.order_lists').find('.sum_price'),
            $price = $(this).parents('.order_lists').find('.price').html(),  //单价
            $cart_id = $(this).parents('.amount_box').attr('data-id'),  //cartid
            $priceTotal = $count*Math.round(($price.substring(1))*100)/100;
        $inputVal.val($count);
        $priceTotalObj.html('￥'+$priceTotal);
        if($inputVal.val()>1 && $obj.hasClass('reSty')){
            $obj.removeClass('reSty');
        }
        editCartNum($cart_id,2);
        totalMoney();
    });

    $reduce.click(function () {
        var $inputVal = $(this).next('input'),
            $count = parseInt($inputVal.val())-1,
            $priceTotalObj = $(this).parents('.order_lists').find('.sum_price'),
            $price = $(this).parents('.order_lists').find('.price').html(),  //单价
            $cart_id = $(this).parents('.amount_box').attr('data-id'),  //cartid
            $priceTotal = $count*Math.round(($price.substring(1))*100)/100;
        if($inputVal.val()>1){
            $inputVal.val($count);
            $priceTotalObj.html('￥'+$priceTotal);
        }
        if($inputVal.val()==1 && !$(this).hasClass('reSty')){
            $(this).addClass('reSty');
        }
        editCartNum($cart_id,1);
        totalMoney();
    });

    $all_sum.keyup(function () {
        var $count = 0,
            $priceTotalObj = $(this).parents('.order_lists').find('.sum_price'),
            $price = $(this).parents('.order_lists').find('.price').html(),  //单价
            $cart_id = $(this).parents('.amount_box').attr('data-id'),  //cartid
            $priceTotal = 0;
        if($(this).val()==''){
            $(this).val('1');
        }
        $(this).val($(this).val().replace(/\D|^0/g,''));
        $count = $(this).val();
        $priceTotal = $count*Math.round(($price.substring(1))*100)/100;
        $(this).attr('value',$count);
        $priceTotalObj.html('￥'+$priceTotal);
        editCartNum($cart_id,3,$count);
        totalMoney();
    })

    //======================================移除商品========================================

    var $order_lists = null;
    var $order_content = '';
    $('.delBtn').click(function () {
        $order_lists = $(this).parents('.order_lists');
        $order_content = $order_lists.parents('.order_content');
        $('.model_bg').fadeIn(300);
        $('.my_model').fadeIn(300);
        del_ids = $(this).attr('data-id');
    });

    $('.movein').click(function () {
        $order_lists = $(this).parents('.order_lists');
        $order_content = $order_lists.parents('.order_content');
        $('.model_bg').fadeIn(300);
        $('.my_tips').fadeIn(300);
    });

    $('.xuanzhong').click(function(){
        if(del_ids){
            if(confirm('是否删除选中的商品')){
                cartChange('del');
            }
        }
    });
    $('.yiru').click(function(){
        if(del_ids){
            if(confirm('是否移入选中的商品到收藏')){
                cartChange('col_del');
            }
        }
    });
    $('.calBtn').click(function(){
        if($(this).find('a').attr('class')){
            cartChange('order');
        }
    });
    //关闭模态框
    $('.closeModel').click(function () {
        closeM();
    });
    $('.dialog-close').click(function () {
        closeM();
    });
    function closeM() {
        $('.model_bg').fadeOut(300);
        $('.my_model').fadeOut(300);
        $('.my_tips').fadeOut(300);
    }
    function closey() {
        $('.model_bg').fadeOut(300);
        $('.my_tips').fadeOut(300);
    }
    //确定按钮，移除商品
    $('.dialog-sure').click(function () {
        $order_lists.remove();
        if($order_content.html().trim() == null || $order_content.html().trim().length == 0){
            $order_content.parents('.cartBox').remove();
        }
        closeM();
        closey();
        $sonCheckBox = $('.son_check');
        totalMoney();
        var tags = $(this).attr('tag');
        if(tags == 'del'){
            cartChange('del');
        }else{
            cartChange('col_del');
        }
    })

    //======================================总计==========================================

    function totalMoney() {
        var total_money = 0;
        var total_count = 0;
        var calBtn = $('.calBtn a');
        $sonCheckBox.each(function () {
            if ($(this).is(':checked')) {
                var goods = Math.round(($(this).parents('.order_lists').find('.sum_price').html().substring(1))*100)/100;
                var num =  Math.round(($(this).parents('.order_lists').find('.sum').val())*100)/100;
                total_money += goods;
                total_count += num;
            }
        });
        $('.total_text').html('￥'+(Math.round(total_money * 100) / 100));
        $('.piece_num').html(total_count);

        // console.log(total_money,total_count);

        if(total_money!=0 && total_count!=0){
            if(!calBtn.hasClass('btn_sty')){
                calBtn.addClass('btn_sty');
            }
        }else{
            if(calBtn.hasClass('btn_sty')){
                calBtn.removeClass('btn_sty');
            }
        }
    }

    function cartChange(type){
        console.log(del_ids);
        $.ajax({
            url:"/Order/changecart", 
            data:{"ids":del_ids,'type':type}, 
            dataType:'json',
            type:'post', 
            success:function (re){
                if(re.code){
                    if(type == 'order'){
                        window.location.href='/Order/detail';
                    }else{
                        history.go(0);
                    }
                }else{
                    alert(re.msg);
                }
            }
        });
    }
    function changeIds(val,k){
        if(k == 2){
            if(idsArr.length == 0)
                return;
            for(var i in idsArr){
                if(idsArr[i] == val){
                   idsArr.splice(i,1);
                }
            }
        }else if(k==1){
            idsArr[idsArr.length] = val;
        }
        del_ids = idsArr.join(',');
    }

    function editCartNum(cart_id,type,num){
        $.ajax({
            url:"/Order/cart", 
            data:{"id":cart_id,'type':type,'num':num}, 
            dataType:'json',
            type:'post', 
            success:function (re){
                if(re.code){
                }else{
                    alert(re.msg);
                }
            }
        });
    }
});

