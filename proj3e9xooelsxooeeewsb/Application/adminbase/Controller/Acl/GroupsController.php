<?php
/**
 * 用户组管理
 *
 * @authon linhecheng
 */
namespace adminbase\Controller\Acl;

use adminbase\Server\AclServer;
use adminbase\Server\System\LogServer;
use Cml\Config;
use Cml\Http\Input;
use Cml\View;
use adminbase\Controller\CommonController;
use adminbase\Model\Acl\AccessModel;
use adminbase\Model\Acl\GroupsModel;
use adminbase\Server\SearchServer;

class GroupsController extends CommonController
{
    //用户组列表
    public function index()
    {
        $groupsModel = new GroupsModel();

        SearchServer::processSearch([
            'name' => 'like'
        ], $groupsModel, true);

        $totalCount = $groupsModel->getTotalNums();
        View::getEngine()
            ->assign('totalPage', $this->getTotalPage($totalCount))
            ->assign('totalCount', $totalCount)
            ->displayWithLayout('Acl/Groups/index', 'regional');
    }

    /**
     * ajax请求分页
     *
     * @acljump adminbase/Acl/Groups/index
     */
    public function ajaxPage()
    {
        $groupsModel = new GroupsModel();
        SearchServer::processSearch([
            'name' => 'like'
        ], $groupsModel, false);

        $list = $groupsModel->getGroupsList(Config::get('page_num'));
        foreach ($list as &$val) {
            $val['lastlogin'] = date('Y-m-d H:i:s', $val['lastlogin']);
            $val['ctime'] = date('Y-m-d H:i:s', $val['ctime']);
            $val['stime'] = date('Y-m-d H:i:s', $val['stime']);
        }

        $this->renderJson(0, '', $list);
    }


    /**
     * 新增用户
     *
     */
    public function add()
    {
        $groupsModel = new GroupsModel();

        $group = $groupsModel->getAllGroups();
        View::getEngine()
            ->assign('group', $group)
            ->display('Acl/Groups/add');
    }

    /**
     * 编辑用户组
     *
     */
    public function edit()
    {
        $id = Input::getInt('id');
        AclServer::currentLoginUserIsHadPermisionToOpGroup($id) || $this->renderJson(-2, '您所有的用户组没有操作该用户[组]的权限!');

        $groupsModel = new GroupsModel();

        View::getEngine()
            ->assign('group', $groupsModel->getByColumn($id))
            ->display('Acl/Groups/edit');
    }

    /**
     * 保存用户组
     *
     * @acljump adminbase/Acl/Groups/add|adminbase/Acl/Groups/edit
     *
     */
    public function save()
    {
        $data = [];
        $id = Input::postInt('id');

        $data['name'] = Input::postString('name', '');
        $data['remark'] = Input::postString('remark', '');
        $groupsModel = new GroupsModel();
        if (is_null($id)) {//新增
            $res = $groupsModel->set($data);
        } else {
            AclServer::currentLoginUserIsHadPermisionToOpGroup($id) || $this->renderJson(-2, '您所有的用户组没有操作该用户[组]的权限!');
            LogServer::addActionLog("修改了用户组[{$id}]的信息" . json_encode($data));
            $res = $groupsModel->updateByColumn($id, $data);
        }
        $res ? $this->renderJson(0, '保存成功') : $this->renderJson(-1, '操作失败');
    }


    /**
     * 删除用户组
     *
     */
    public function del()
    {
        $id = Input::getInt('id');
        $id < 1 && $this->renderJson(-1, '删除失败');

        AclServer::currentLoginUserIsHadPermisionToOpGroup($id) || $this->renderJson(-2, '您所有的用户组没有操作该用户[组]的权限!');

        $groupsModel = new GroupsModel();
        if ($id === intval(Config::get('administratorid'))) $this->renderJson(-1, '不能删除超管');
        if ($groupsModel->delByColumn($id)) {
            LogServer::addActionLog("删除了用户组[{$id}]!");
            //删除对应的权限
            $accessModel = new AccessModel();
            $accessModel->delByColumn($id, 'groupid');
            $this->renderJson(0, '删除成功');
        } else {
            $this->renderJson(-1, '删除失败');
        }
    }
}