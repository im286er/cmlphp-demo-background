<?php
//应用公共配置文件
return array(
        'auth_key' => 'olfeoo3-8wlxoew3-zwpebx',
        'userauthid' => 'pokmuel',
        'url_default_action' => 'adminbase/System/Index/index',
        'system_name' => '管理后台',
        'cmlframework_system_route' => array(
            'cmlframeworkstaticparse' => '\\Cml\\Tools\\StaticResource::parseResourceFile',
            'cml_calc_veryfy_code' => '\\Cml\\Vendor\\VerifyCode::calocVerify'
        ),
        'log_unset_field' => [//记录操作日志时不记录敏感数据的日志，要unset掉post中相关的参数
            'pwd',
            'oldpwd',
            'repwd',
            'password',
            'checkpassword'
        ],
        'html_theme' => 'adminlte',
        'page_num' => 15,
        'administratorid' => 1,//后台超级管理员id
);