<?php
/**
 * 菜单管理
 *
 * @authon linhecheng
 */
namespace adminbase\Controller\Acl;

use adminbase\Server\System\LogServer;
use Cml\Http\Input;
use Cml\View;
use adminbase\Controller\CommonController;
use adminbase\Model\Acl\MenusModel;
use Cml\Vendor\Tree;

class MenusController extends CommonController
{
    public function menusList()
    {
        $menusModel = new MenusModel();
        $menus = $menusModel->getList(0, 5000);
        $menus = Tree::getTreeNoFormat($menus);

        View::getEngine()
            ->assignByRef('menus', $menus)
            ->displayWithLayout('Acl/Menus/menusList', 'regional');

    }

    //增加菜单
    public function add()
    {
        View::getEngine()
            ->assign('pid', Input::getInt('pid', 0))
            ->display('Acl/Menus/add');
    }

    //增加菜单
    public function edit()
    {
        $id = Input::getInt('id');
        $id > 0 || exit('非法操作');
        $menuModel = new MenusModel();
        $menu = $menuModel->getByColumn($id);

        View::getEngine()->assign('menu', $menu)
            ->display('Acl/Menus/edit');
    }

    /**
     * 保存菜单
     *
     */
    public function save()
    {
        $data = array();
        $id = Input::postInt('id');
        $data['pid'] = Input::postInt('pid', 0);
        $data['title'] = Input::postString('title');
        $data['url'] = Input::postString('url');
        $data['isshow'] = Input::postInt('isshow');
        $data['sort'] = Input::postInt('sort', 0);

        $menuModel = new MenusModel();
        if(is_null($id)) {//新增
            $res = $menuModel->set($data);
        } else  {
            LogServer::addActionLog("修改了菜单[{$id}]的信息".json_encode($data));
            unset($data['pid']);
            $res = $menuModel->updateByColumn($id, $data);
        }
        $res ? $this->renderJson(0, '保存成功') : $this->renderJson(-1, '操作失败');
    }

    /**
     * 删除菜单
     *
     */
    public function del()
    {
        $id = Input::getInt('id');
        $id < 1 && $this->renderJson(-1, '删除失败');
        $menuModel = new MenusModel();
        if ($menuModel->hasSonMenus($id)) {
            $this->renderJson(-1, '该菜单下有子菜单不能删除！');
        } else {
            LogServer::addActionLog("删除了菜单[{$id}]!");
            $menuModel->delByColumn($id) ?  $this->renderJson(0, '删除成功') : $this->renderJson(-1, '删除失败');
        }
    }
}