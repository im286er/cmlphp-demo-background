<?php namespace adminbase\Server\System;

use Cml\Http\Request;
use Cml\Vendor\Acl;
use Cml\Cml;
use Cml\Server;
use adminbase\Model\System\ActionLogModel;

/**
 * Class LoginlogServer log服务类
 *
 * @package Server\System
 */
class LogServer extends Server
{

    /**
     * 新增一条操作日志
     *
     * @param $action
     */
    public static function addActionLog($action)
    {
        $user = Acl::getLoginInfo();
        $actionLog = array(
            'action' => $action,
            'userid' => $user['id'],
            'username' => $user['username'],
            'ctime' => Cml::$nowTime
        );
        ActionLogModel::getInstance()->set($actionLog);
    }
}