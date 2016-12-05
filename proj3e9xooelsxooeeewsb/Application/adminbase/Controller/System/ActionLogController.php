<?php
namespace adminbase\Controller\System;

use Cml\Config;
use Cml\View;
use adminbase\Controller\CommonController;
use adminbase\Model\System\ActionLogModel;
use adminbase\Server\SearchServer;

class ActionLogController extends CommonController
{
    public function index()
    {
        $actionLogModel = new ActionLogModel();
        SearchServer::processSearch([
            'userid' => '',
            'start_time' => '>',
            'end_time' => '<'
        ], $actionLogModel, true);

        $totalCount = $actionLogModel->getTotalNums();
        View::getEngine()
            ->assign('totalPage', $this->getTotalPage($totalCount))
            ->assign('totalCount', $totalCount)
            ->displayWithLayout('System/ActionLog/list', 'regional');
    }


    /**
     * ajax请求分页
     *
     * @acljump adminbase/System/ActionLog/index
     */
    public function ajaxPage()
    {
        $actionLogModel = new ActionLogModel();
        SearchServer::processSearch([
            'userid' => '',
            'start_time' => '>',
            'end_time' => '<'
        ], $actionLogModel, false);

        $list = $actionLogModel->getListByPaginate(Config::get('page_num'));
        foreach ($list as &$val) {
            $val['ctime'] = date('Y-m-d H:i:s', $val['ctime']);
        }

        $this->renderJson(0, '', $list);
    }
}