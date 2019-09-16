<?php
return [
    'ak' => 'XVBkJ2CBdjQ2F-S7xE4cSzNRrtEBLf_wjHEbCYKF',
    'sk' => 'd3QDliFoPC_MDyQPDuvwRW97e1obr8NE3x1IupxS',
    'bucket' => 'photo',
    'expires' => 3600,
    'url' => 'https://up-z2.qiniup.com',
    'fsizeLimit'=> 1024*1024*40,//文件上传大小 10M
    'is_use'=>1,//开启七牛上传
    'file_prefix'=> '/qn_',
    'preview_domain'=> 'http://qiniu.chinacarechain.com/', //域名
    //是否删除数据
    'is_open_del'=>0,
];