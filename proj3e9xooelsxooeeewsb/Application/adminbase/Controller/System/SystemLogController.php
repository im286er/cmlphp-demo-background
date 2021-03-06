<?php
namespace adminbase\Controller\System;

use Cml\Config;
use Cml\View;
use adminbase\Controller\CommonController;
use adminbase\Model\System\SystemLogModel;
use adminbase\Service\SearchService;

class SystemLogController extends CommonController
{
    public function index()
    {
        $systemLogModel = new SystemLogModel();
        SearchService::processSearch([
            'userid' => '',
            'url' => 'like',
            'start_time' => '>',
            'end_time' => '<'
        ], $systemLogModel, true);

        $totalCount = $systemLogModel->getTotalNums();
        View::getEngine()
            ->assign('totalPage', $this->getTotalPage($totalCount))
            ->assign('totalCount', $totalCount)
            ->displayWithLayout('System/SystemLog/list', 'regional');
    }

    /**
     * ajax请求分页
     *
     * @acljump adminbase/System/SystemLog/index
     */
    public function ajaxPage()
    {
        $systemLogModel = new SystemLogModel();
        SearchService::processSearch([
            'userid' => '',
            'url' => 'like',
            'start_time' => '>',
            'end_time' => '<'
        ], $systemLogModel, false);

        $list = $systemLogModel->getListByPaginate(Config::get('page_num'));
        foreach ($list as &$val) {
            $val['ctime'] = date('Y-m-d H:i:s', $val['ctime']);
        }
        $this->renderJson(0, '', $list);
    }
}