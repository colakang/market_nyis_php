<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------


return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    'login' => 'index/index/login',
    'logout' => 'index/index/logout',
    'register' => 'index/index/register',
    'profile' => 'index/index/profile',
    'seller' => '/seller/',
    's' => 'index/index/search',
    'view/:id' => 'index/index/view',
    '__alias__' =>  [
        'seller'  =>  'index/seller',
    ],

];
