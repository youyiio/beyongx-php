<?php


namespace app\cms\controller;


class Sign extends \app\common\controller\Sign
{
    public function initialize()
    {
        parent::initialize();

        $config = [
            'login_success_view' => url('cms/Member/overview'),
            'logout_success_view' => url('Cms/Index/index'),
            'register_enable' => true,
            'register_code_type' => 'mail',
            'reset_enable' => true,
            'reset_code_type' => 'mail',
        ];

        $this->defaultConfig = array_merge($this->defaultConfig, $config);

        $this->view->engine->layout(false);
    }
}