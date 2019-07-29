<?php
/**
 * 验证手机号码
 * */
function validPhone($phone){
    return preg_match('/^1\d{10}$/',$phone);
}