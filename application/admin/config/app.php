<?php

return [
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '\\app\\common\\exception\\AdminHandle',

    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => 'public/success', //Env::get('think_path') . 'tpl/dispatch_jump.tpl',
    'dispatch_error_tmpl'    => 'public/error', //Env::get('think_path') . 'tpl/dispatch_jump.tpl',
    //'exception_tmpl'         => 'public/500.html', //Env::get('think_path') . 'tpl/think_exception.tpl',
];
