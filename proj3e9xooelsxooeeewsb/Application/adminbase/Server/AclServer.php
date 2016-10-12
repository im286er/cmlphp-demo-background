<?php namespace adminbase\Server;

use adminbase\Model\Acl\UsersModel;
use Cml\Config;
use Cml\Server;
use Cml\Vendor\Acl;
use Cml\View;

class AclServer extends Server
{
    /**
     * 显示无权限页
     */
    public static function noPermission()
    {
        View::getEngine()
            ->assign('code', '403')
            ->assign('title', '无权限')
            ->assign('msg', '很抱歉你没有当前模块的操作权限！')
            ->displayWithLayout('Public/error', 'regional', 'adminbase', 'adminbase');
    }

    /**
     * 判断当前登录的用户是否有操作某用户的权限
     *
     * @param int $opUserId
     *
     * @return bool
     */
    public static function currentLoginUserIsHadPermisionToOpUser($opUserId = 0)
    {
        if (Acl::isSuperUser()) {
            return true;
        }

        if (Config::get('administratorid') === intval($opUserId)) {
            return false;
        }

        $currentLoginUser = Acl::getLoginInfo();

        $opUserInfo = UsersModel::getInstance()->getByColumn($opUserId, 'id');
        $opUserGroupIds = explode('|', $opUserInfo['groupid']);

        foreach($currentLoginUser['groupid'] as $cgid) {
            foreach($opUserGroupIds as $ogid) {
                if ($cgid <= $ogid) { //只要当前登录用户的管理组有一个比该用户的用户组小即他拥有更大的权限
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 判断当前登录的用户是否有操作某用户组的权限
     *
     * @param int $opGroupId
     *
     * @return bool
     */
    public static function currentLoginUserIsHadPermisionToOpGroup($opGroupId = 0)
    {
        if (Acl::isSuperUser()) {
            return true;
        }
        $currentLoginUser = Acl::getLoginInfo();

        foreach($currentLoginUser['groupid'] as $cgid) {
            if ($cgid <= $opGroupId) { //只要当前登录用户的管理组有一个比该用户组小即他拥有更大的权限
                return true;
            }
        }
        return false;
    }

}