<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;


Route::get('/startStudentsCron', 'StudentsCron/startStudentsCron');


//Route::get('/getCanada', 'StudentsCron/getCanada');
//Route::get('/getUnitedKingdom', 'StudentsCron/getUnitedKingdom');
//Route::get('/getUnitedStates', 'StudentsCron/getUnitedStates');
//Route::get('/getJapan', 'StudentsCron/getJapan');
//
//
//
//Route::get('/encrypt', 'StudentsCron/encrypt');
//Route::get('/decrypt', 'StudentsCron/decrypt');


Route::get('/testPalpay', 'Index/testPalpay');