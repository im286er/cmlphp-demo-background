<?php
/**
 * 用户管理
 *
 * @authon linhecheng
 */
namespace adminbase\Controller\Acl;

use adminbase\Model\Acl\AccessModel;
use adminbase\Server\AclServer;
use adminbase\Server\System\LogServer;
use Cml\Vendor\Acl;
use Cml\Cml;
use Cml\Config;
use Cml\Http\Input;
use Cml\View;
use adminbase\Controller\CommonController;
use adminbase\Model\Acl\GroupsModel;
use adminbase\Model\Acl\UsersModel;
use Cml\Vendor\Validate;
use adminbase\Server\SearchServer;

class UsersController extends CommonController
{
    //用户列表
    public function index()
    {
        $usersModel = new UsersModel();
        SearchServer::processSearch([
            'username' => 'like',
            'nickname' => 'like'
        ], $usersModel, true);

        $totalCount = $usersModel->getTotalNums();
        View::getEngine()
            ->assign('totalPage', $this->getTotalPage($totalCount))
            ->assign('totalCount', $totalCount)
            ->displayWithLayout('Acl/Users/index', 'regional');
    }

    /**
     * ajax请求分页
     *
     * @acljump adminbase/Acl/Users/index
     */
    public function ajaxPage()
    {
        $usersModel = new UsersModel();
        SearchServer::processSearch([
            'username' => 'like',
            'nickname' => 'like'
        ], $usersModel, true);

        $list = $usersModel->getUsersList(Config::get('page_num'));
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
            ->display('Acl/Users/add');
    }

    /**
     * 编辑用户
     *
     */
    public function edit()
    {
        $id = Input::getInt('id');

        AclServer::currentLoginUserIsHadPermisionToOpUser($id) || exit('您所有的用户组没有操作该用户[组]的权限!');

        $usersModel = new UsersModel();
        $groupsModel = new GroupsModel();

        $group = $groupsModel->getAllGroups();
        View::getEngine()
            ->assign('user', $usersModel->getByColumn($id))
            ->assign('group', $group)
            ->display('Acl/Users/edit');
    }

    /**
     * 保存用户
     *
     * @acljump adminbase/Acl/Users/add|adminbase/Acl/Users/edit
     *
     */
    public function save()
    {
        $data = [];
        $id = Input::postInt('id');

        $id > 0 && (AclServer::currentLoginUserIsHadPermisionToOpUser($id) || $this->renderJson(-2, '您所有的用户组没有操作该用户[组]的权限!'));

        $username = Input::postString('username');
        $data['nickname'] = Input::postString('nickname');
        $data['password'] = Input::postString('password');
        $data['checkpassword'] = Input::postString('checkpassword');
        $data['groupid'] = Input::postInt('groupid');
        //$data['exportaccess'] = Input::postInt('groupid') == 1 ? 1 : Input::postInt('exportaccess');
        $data['remark'] = Input::postString('remark', '');

        $newGroupInfo = GroupsModel::getInstance()->getByColumn($data['groupid'], 'id');
        if (!AclServer::currentLoginUserIsHadPermisionToOpGroup($data['groupid'])) {
            $this->renderJson(-2, "您所有的用户组没有权限将该用户的用户组设为【{$newGroupInfo['name']}】!");
        }

        if (mb_strlen($data['nickname']) < 3 || mb_strlen($data['nickname']) > 20) {
            $this->renderJson(-1, '昵称长度必须大于3小于20！');
        }

        if ($id < 1 || isset($data['password'])) {
            if (mb_strlen($data['password']) < 6) {
                $this->renderJson(-1, '密码长度必须大于6小于20！');
            }
            if ($data['password'] != $data['checkpassword']) {
                $this->renderJson(-1, '两次密码输入不正确，请重新输入！');
            }
        }

        unset($data['checkpassword']);

        if (is_null($data['password']) || empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = md5(md5($data['password']) . Config::get('password_salt'));
        }

        $usersModel = new UsersModel();

        //判断是否已有同名用户
        $equalName = $usersModel->getByColumn($username, 'username');

        if (is_null($id)) {//新增
            $equalName && $this->renderJson(-1, '用户名已存在');
            $data['username'] = $username;
            $data['ctime'] = Cml::$nowTime;
            $res = $usersModel->set($data);
        } else {
            if ($equalName && $equalName['id'] != $id) {
                $this->renderJson(-1, '用户名已存在');
            }
            $data['username'] = $username;
            $data['stime'] = Cml::$nowTime;

            LogServer::addActionLog("修改了用户[{$id}]的信息" . json_encode($data));
            $res = $usersModel->updateByColumn($id, $data);
        }
        $res ? $this->renderJson(0, '保存成功') : $this->renderJson(-1, '操作失败');
    }

    /**
     * 删除用户
     *
     */
    public function del()
    {
        $id = Input::getInt('id');
        $id < 1 && $this->renderJson(-1, '删除失败');
        $users = new UsersModel();

        AclServer::currentLoginUserIsHadPermisionToOpUser($id) || $this->renderJson(-2, '您所有的用户组没有操作该用户[组]的权限!');

        if ($id === intval(Config::get('administratorid'))) {
            $this->renderJson(-1, '不能删除超管');
        }

        if ($users->delByColumn($id)) {
            LogServer::addActionLog("删除了用户[{$id}]");
            //删除对应的权限
            $accessModel = new AccessModel();
            $accessModel->delByColumn($id, 'userid');
            $this->renderJson(0, '删除成功');
        } else {
            $this->renderJson(-1, '删除失败');
        }
    }

    /**
     * 禁用/解禁用户
     *
     */
    public function disable()
    {
        $data = [];
        $id = Input::getInt('id');

        AclServer::currentLoginUserIsHadPermisionToOpUser($id) || $this->renderJson(-2, '您所有的用户组没有操作该用户[组]的权限!');

        $status = Input::getInt('status');

        $data['status'] = $status ? 0 : 1;
        $id < 1 && $this->renderJson(-1, '操作失败');
        $users = new UsersModel();
        if ($id === intval(Config::get('administratorid'))) {
            $this->renderJson(-1, '不能操作超管');
        }

        if ($res = $users->updateByColumn($id, $data)) {
            LogServer::addActionLog("禁用了用户[{$id}]");
            $this->renderJson(0, '操作成功');
        } else {
            $this->renderJson(-1, '操作失败');
        }
    }

    /**
     * 修改个人资料
     *
     */
    public function editSelfInfo()
    {
        $user = Acl::getLoginInfo();
        $user = UsersModel::getInstance()->getByColumn($user['id']);

        View::getEngine()
            ->assignByRef('user', $user)
            ->display('Acl/Users/editSelfInfo');
    }

    /**
     * 修改个人资料 - 保存
     *
     * @acljump adminbase/Acl/Users/editSelfInfo
     *
     */
    public function saveSelfInfo()
    {
        $user = Acl::getLoginInfo();
        $userModel = new UsersModel();
        $user = $userModel->getByColumn($user['id']);

        $data = [];
        $data['nickname'] = Input::postString('nickname');
        $data['stime'] = Cml::$nowTime;

        if (isset($_POST['oldpwd']) && Validate::isLength($_POST['oldpwd'], 6, 20)) {
            if ($user['password'] != md5(md5($_POST['oldpwd']) . Config::get('password_salt'))) {
                exit(json_encode([
                    'code' => -2,
                    'msg' => '旧密码错误'
                ]));
            }

            if ($_POST['pwd'] != $_POST['repwd']) {
                exit(json_encode([
                    'code' => -2,
                    'msg' => '两次输入密码不一致！'
                ]));
            }

            if (!Validate::isLength($_POST['pwd'], 6, 20)) {
                exit(json_encode([
                    'code' => -2,
                    'msg' => '新密码长度必须为6-20个字符！'
                ]));
            }
            $data['password'] = md5(md5($_POST['pwd']) . Config::get('password_salt'));
            Acl::logout();
        }

        if ($userModel->updateByColumn($user['id'], $data)) {
            $this->renderJson(0, '修改成功');
        }
    }
}