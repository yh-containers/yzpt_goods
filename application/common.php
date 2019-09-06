<?php
/**
 * 验证手机号码
 * */
function validPhone($phone){
    return preg_match('/^1\d{10}$/',$phone);
}


function isMobile()
{
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $is_pc = (strpos($agent, 'windows nt')) ? true : false;
    $is_mac = (strpos($agent, 'mac os')) ? true : false;
    $is_iphone = (strpos($agent, 'iphone')) ? true : false;
    $is_android = (strpos($agent, 'android')) ? true : false;
    $is_ipad = (strpos($agent, 'ipad')) ? true : false;
    if($is_pc){
        return  false;
    }
    if($is_mac){
        return  true;
    }
    if($is_iphone){
        return  true;
    }
    if($is_android){
        return  true;
    }
    if($is_ipad){
        return  true;
    }
}

function create_invite_code() {
    $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $rand = $code[rand(0,25)]
        .strtoupper(dechex(date('m')))
        .date('d')
        .substr(time(),-5)
        .substr(microtime(),2,5)
        .sprintf('%02d',rand(0,99));
    for(
        $a = md5( $rand, true ),
        $s = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        $d = '',
        $f = 0;
        $f < 6;
        $g = ord( $a[ $f ] ),
        $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
        $f++
    );
    return $d;
}

/**
 * [将Base64图片转换为本地图片并保存]
 * @E-mial wuliqiang_aa@163.com
 * @TIME   2017-04-07
 * @WEB    http://blog.iinu.com.cn
 * @param  [Base64] $base64_image_content [要保存的Base64]
 * @param  [目录] $path [要保存的路径]
 * @return bool
 */
function base64_image_content($base64_image_content,$type='video_cover')
{
    //匹配出图片的格式
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
        $new_file = md5($type.time().create_invite_code());
        $root_path = \think\facade\Env::get('root_path');
        $dir = '/uploads/'.$type.'/';
//        is_dir($dir) OR mkdir($dir, 0777, true);

        //$new_file = $new_file . time() . ".{$type}";
        $ext = '.jpg';
        if (file_put_contents($root_path.$dir.$new_file.$ext, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
            return  $dir.$new_file.$ext;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

