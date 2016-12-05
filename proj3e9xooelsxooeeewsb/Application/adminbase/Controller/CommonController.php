<?php
namespace adminbase\Controller;

use adminbase\Server\ResponseServer;
use Cml\Config;
use Cml\Cml;
use Cml\Controller;
use Cml\Http\Request;
use Cml\Http\Response;
use Cml\Secure;
use Cml\Vendor\Acl;
use adminbase\Model\Acl\MenusModel;
use adminbase\Model\System\SystemLogModel;
use adminbase\Server\AclServer;
use Cml\View;

class CommonController extends Controller
{
    /**
     * 权限验证
     *
     */
    public function init()
    {
        $user = Acl::getLoginInfo();

        if (!$user) {//未登录
            Request::isAjax() ? ResponseServer::jsJump('adminbase/Public/login') : Response::redirect('adminbase/Public/login');
        }

        if (!Acl::checkAcl($this)) {//无权限
            AclServer::noPermission();
        }

        $url = ltrim(str_replace('\\', '/',
            Cml::getContainer()->make('cml_route')->getFullPathNotContainSubDir()
        ), '/');

        //记录操作日志
        $menuModel = new MenusModel();
        $currentMenu = $menuModel->getByUrl($url);
        $tmp = $_POST;
        $post = Secure::htmlspecialchars($_POST);
        $_POST = $tmp;

        $fields = Config::get('log_unset_field');
        foreach ($fields as $val) {
            isset($post[$val]) && $post[$val] = '';
        }

        SystemLogModel::getInstance()->set([
            'action' => $currentMenu ? $currentMenu['title'] : $url,
            'url' => $url,
            'userid' => $user['id'],
            'username' => $user['username'],
            'get' => var_export(Secure::htmlspecialchars($_GET), true),
            'post' => var_export($post, true),
            'ip' => Request::ip(),
            'ctime' => Cml::$nowTime
        ]);

        $menus = Acl::getMenus();
        $bread = '';
        foreach ($menus as $key => &$val) {
            if ($val['url'] == $url) {
                $val['current'] = true;
                $bread[$url] = $val['title'];
            }

            if (empty($val['sonNode'])) {
                unset($menus[$key]);
            } else {
                foreach ($val['sonNode'] as &$v) {
                    if ($v['url'] == $url) {
                        $bread[$val['url']] = $val['title'];
                        $bread[$v['url']] = $v['title'];
                        $v['current'] = true;
                        $val['current'] = true;
                    }
                }
            }
        }

        Request::isAjax() || View::getEngine('Html')
            ->assignByRef('menu', $menus)
            ->assign('bread', $bread)
            ->assign('user', Acl::getLoginInfo())
            ->assign('title', $currentMenu ? $currentMenu['title'] : $url);
    }

    /**
     * 根据数据总数获取总页数
     *
     * @param int $totalCount
     *
     * @return float|int
     */
    public function getTotalPage($totalCount = 0)
    {
        $totalPage = ceil($totalCount / Config::get('page_num'));
        return $totalPage < 1 ? 1 : $totalPage;
    }

    /**
     * json输出
     *
     * @param int $code
     * @param string $msg
     * @param array $data
     */
    protected function renderJson($code = -1, $msg = '未登录', $data = [])
    {
        ResponseServer::renderJson($code, $msg, $data);
    }
}