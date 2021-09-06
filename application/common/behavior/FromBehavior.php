<?php
namespace app\common\behavior;

use think\facade\Request;
use think\facade\Cookie;

class FromBehavior
{
    public function run()
    {
        //首次访问 cookie记录来源
        if (!Cookie::has('from_referee') && !Cookie::has('entrance_url')) {
            $request = Request::instance();
            $fromReferee = $request->server('HTTP_REFERER');
            Cookie::set('from_referee', $fromReferee, 0);
            $fromUrl = $request->url(true);
            Cookie::set('entrance_url', $fromUrl, 0);
        }
    }
}
