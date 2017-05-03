<?php
namespace adminbase\Controller;

use adminbase\Service\ResponseService;
use Cml\Config;
use Cml\Tools\StaticResource;
use Cml\Cml;
use Cml\Controller;
use Cml\Http\Input;
use Cml\Http\Request;
use Cml\Http\Response;
use Cml\Vendor\Acl;
use Cml\View;
use adminbase\Model\Acl\UsersModel;
use adminbase\Model\System\LoginLogModel;
use Cml\Vendor\Validate;
use Cml\Vendor\VerifyCode;

class PublicController extends Controller
{
    //未渲染页面
    public function login()
    {
        $user = Acl::getLoginInfo();

        $user && ResponseService::jsJump('adminbase/System/Index/index');

        View::getEngine()
            ->display('Public/login');
    }

    /**
     * 校验登录
     *
     */
    public function checkLogin()
    {
        $username = base64_decode(Input::postString('username'));
        $password = base64_decode(Input::postString('password'));
        $_POST['username'] = $username;
        $_POST['password'] = $password;

        $validate = new Validate($_POST);
        $validate
        ->rule('require', 'code', 'username', 'pwd')
        ->rule('length', 'username', 5, 50)
        ->rule('length', 'password', 6, 50)
        ->label([
            'code' => '验证码',
            'username' => '用户名',
            'password' => '密码'
        ]);

        if (!$validate->validate()) {
            ResponseService::renderJson(-1, $validate->getErrors(2, '|'));
        }

        $code = Input::postString('code');
        if (!VerifyCode::checkCode($code)) {
            ResponseService::renderJson(-1, '验证码错误！');
        }

        $usersModel = new UsersModel();

        $user = $usersModel->getByColumn($username, 'username');

        if ($user['status'] == '0') {
            ResponseService::renderJson(-3, '用户已被禁用');
        }

        if (!$user || md5(md5($password) . Config::get('password_salt')) != $user['password']) {
            ResponseService::renderJson(-2, '用户名或密码错误');
        }

        Acl::setLoginStatus($user['id']);

        $loginLogModel = new LoginLogModel();
        $loginLogModel->set([
            'userid' =>  $user['id'],
            'username' =>  $user['username'],
            'nickname' =>  $user['nickname'],
            'ip' => Request::ip(),
            'ctime' => Cml::$nowTime
        ]);

        $usersModel->updateByColumn($user['id'], [
            'lastlogin' => Cml::$nowTime
        ]);

        ResponseService::renderJson(0, '登录成功！');
    }


    //登出
    public function logout()
    {
        Acl::logout();
        Response::redirect('adminbase/public/login');
    }

    //创建静态文件软链接
     public function createSymLink()
     {
         StaticResource::createSymbolicLink();
     }

}