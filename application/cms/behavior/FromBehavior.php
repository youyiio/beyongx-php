<?php
namespace app\cms\behavior;

use think\facade\Request;
use think\facade\Cookie;

class FromBehavior
{
    public function run()
    {
        //首次访问 cookie记录来源
        if (!Cookie::has('from_referee') && !Cookie::has('entrance_url')) {
            $request = Request::instance();
            $from_referee = $request->server('HTTP_REFERER');
            Cookie::set('from_referee', $from_referee,0);
            $from_url = $request->url(true);
            Cookie::set('entrance_url', $from_url, 0);
        }
    }
}
