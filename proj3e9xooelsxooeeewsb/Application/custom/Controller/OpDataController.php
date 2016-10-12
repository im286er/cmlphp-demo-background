<?php namespace custom\Controller;
/* * *********************************************************
* 演示
* @Author  linhecheng<linhechengbush@live.com>
* @Date: 2016/5/5 15:28
* *********************************************************** */
use adminbase\Controller\CommonController;
use Cml\View;

class OpDataController extends CommonController
{
    /**
     * 数据展示
     *
     */
    public function index()
    {
        View::getEngine()
            ->assign('totalPage', 1)
            ->assign('totalCount', 10)
            ->assign('title', '测试一下')
            ->displayWithLayout('OpData/list', 'regional', 'adminbase');//使用adminbase模块下的 regional布局文件渲染页面
    }

}