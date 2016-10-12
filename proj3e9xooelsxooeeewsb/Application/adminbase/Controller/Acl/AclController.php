<?php
/**
 * 权限管理
 *
 */
namespace adminbase\Controller\Acl;

use adminbase\Model\Acl\AccessModel;
use adminbase\Server\AclServer;
use adminbase\Server\System\LogServer;
use Cml\Http\Input;
use Cml\Vendor\Acl;
use Cml\View;
use adminbase\Controller\CommonController;
use adminbase\Model\Acl\GroupsModel;
use adminbase\Model\Acl\MenusModel;
use adminbase\Model\Acl\UsersModel;

class AclController extends CommonController
{
    /**
     * 用户授权
     *
     */
    public function user()
    {
        $this->showAclPage(1);
    }

    /**
     * 用户组授权
     *
     */
    public function group()
    {
        $this->showAclPage(2);
    }

    /**
     * 显示授权页面
     *
     * @param int $type 1 用户授权 2用户组授权
     */
    private function showAclPage($type = 1)
    {
        $id = Input::getInt('id');
        $id < 1 && exit('非法请求');

        $dataModel = $type === 1 ? new UsersModel() : new GroupsModel();

        $isHadPermission = $type == 1 ?
            AclServer::currentLoginUserIsHadPermisionToOpUser($id)
            :
            AclServer::currentLoginUserIsHadPermisionToOpGroup($id);
        $isHadPermission || exit('您所有的用户组没有操作该用户[组]的权限!');

        $menusModel = new MenusModel();

        //获取所有菜单
        $menus = $menusModel->getList(0, 5000);
        //获取已有权限
        $accessModel = new AccessModel();
        $hadAccessMenusId = $accessModel->getAccessArrByField($id, $type === 1 ? 'userid' : 'groupid');
        $hadAccessMenus = array();
        foreach ($hadAccessMenusId as $val) {
            $hadAccessMenus[] = $val['menuid'];
        }

        //授权的时候该管理员只能看到自己有的权限列表
        $currentLoginUsersHadAccessList = Acl::isSuperUser() ? array() : $this->getCurrentLoginUsersAcl();

        foreach ($menus as $key => &$val) {
            if (false === Acl::isSuperUser() && !in_array($val['id'], $currentLoginUsersHadAccessList)) {
                unset($menus[$key]);
                continue;
            }
            unset($val['url']);
            $val['open'] = $val['checked'] = in_array($val['id'], $hadAccessMenus);
        }

        View::getEngine()
            ->assignByRef('item', $dataModel->getByColumn($id))
            ->assignByRef('menus', array_values($menus))
            ->display('Acl/Acl/'.$type = 1 ? 'user' : 'group');

    }

    /**
     * 保存授权信息
     *
     */
    public function save()
    {
        $menuIds = trim(Input::postString('ids'), ',');
        empty($menuIds) || $menuIds = explode(',', $menuIds);
        $id = Input::postInt('id');
        $type = Input::getInt('type', 0);

        $isHadPermission = $type == 1 ?
            AclServer::currentLoginUserIsHadPermisionToOpUser($id)
            :
            AclServer::currentLoginUserIsHadPermisionToOpGroup($id);
        $isHadPermission || $this->renderJson(-2, '您所有的用户组没有操作该用户[组]的权限!');

        if (!in_array($type, array(1, 2)) || $id < 1) {
            $this->renderJson(-2, '没修改!');
        }
        $field = $type === 1 ? 'userid' :'groupid';

        $aclModel = new AccessModel();
        $aclModel->delByColumn($id, $field);

        LogServer::addActionLog("修改了[{$field}:{$id}]的权限信息!");

        //授权的时候该管理员只能授权自己有的权限列表
        $currentLoginUsersHadAccessList = Acl::isSuperUser() ? array() : $this->getCurrentLoginUsersAcl();

        foreach ($menuIds as $i) {
            if (false === Acl::isSuperUser() && !in_array($i, $currentLoginUsersHadAccessList)) {
                continue;
            }

            $data = array(
                $field => $id,
                'menuid' =>$i
            );
            $aclModel->set($data);
        }
        $this->renderJson(0, '修改成功');
    }

    /**
     * 获取当前登录用户所有的权限id
     *
     */
    private function getCurrentLoginUsersAcl()
    {
        $user = Acl::getLoginInfo();
        //获取已有权限
        $accessModel = new AccessModel();
        $hadAccessMenusId = $accessModel->getAccessArrByField($user['id'], 'userid');
        $hadAccessMenusId += $accessModel->getAccessArrByField($user['groupid'], 'groupid');
        $return = array();
        foreach($hadAccessMenusId as $val) {
            $return[] = $val['menuid'];
        }
        return array_unique($return);
    }

}