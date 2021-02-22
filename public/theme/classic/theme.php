<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-08-17
 * Time: 18:17
 */

return [
    'name' => '经典主题', //主题名称
    'package_name' => 'classic', //配置主题报名，与配置的主题文件夹一致
    'responsive' => false, //是否响应式界面：值为true,false; 可自动响应至pc,mobile,tablet

    'version' => 'v1.0.0',
    'update_time' => '2020-08-17 10:20:00',

    //网站配置
    'logo_image' => ["width" => 80, "height" => 52, "thumb_width" => 80, "thumb_height" => 52],
    'favicon' => ["width" => 32, "height" => 32, "thumb_width" => 16, "thumb_height" => 16],

    //文章配置
    'article_thumb_image' => ["width" => 420, "height" => 280, "thumb_width" => 210, "thumb_height" => 140],

    //广告配置
    'ad_images' => [
       'banner_headline' => ["width" => 980, "height" => 335, "thumb_width" => 490, "thumb_height" => 167.5],
    ],
];