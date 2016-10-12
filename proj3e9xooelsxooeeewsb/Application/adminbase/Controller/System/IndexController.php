<?php
namespace adminbase\Controller\System;

use Cml\View;
use adminbase\Controller\CommonController;

class IndexController extends CommonController
{
    /**
     * 首页
     *
     */
    public function index()
    {
        View::getEngine('Html')
            ->displayWithLayout('System/Index/index', 'master');
    }
}