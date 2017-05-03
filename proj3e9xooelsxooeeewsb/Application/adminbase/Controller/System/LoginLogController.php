<?php
namespace adminbase\Controller\System;

use Cml\Config;
use Cml\Http\Input;
use Cml\View;
use adminbase\Controller\CommonController;
use adminbase\Model\System\LoginLogModel;
use adminbase\Service\SearchService;

class LoginLogController extends CommonController
{
    public function index()
    {
        $loginLogModel = new LoginLogModel();
        SearchService::processSearch([
            'userid' => '',
            'start_time' => '>',
            'end_time' => '<'
        ], $loginLogModel, true);

        $totalCount = $loginLogModel->getTotalNums();
        View::getEngine()
            ->assign('totalPage', $this->getTotalPage($totalCount))
            ->assign('totalCount', $totalCount)
            ->displayWithLayout('System/LoginLog/list', 'regional');
    }

    /**
     * ajax请求分页
     *
     * @acljump adminbase/System/LoginLog/index
     */
    public function ajaxPage()
    {
        $loginLogModel = new LoginLogModel();
        SearchService::processSearch([
            'userid' => '',
            'start_time' => '>',
            'end_time' => '<'
        ], $loginLogModel, false);

        $list = $loginLogModel->getListByPaginate(Config::get('page_num'));
        foreach ($list as &$val) {
            $val['ctime'] = date('Y-m-d H:i:s', $val['ctime']);
        }

        $this->renderJson(0, '', $list);
    }
}