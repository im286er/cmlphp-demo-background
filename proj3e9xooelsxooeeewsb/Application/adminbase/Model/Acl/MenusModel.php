<?php
/**
 * 菜单管理
 *
 */
namespace adminbase\Model\Acl;

use Cml\Model;

class MenusModel extends Model
{
    protected $table = 'menus';

    /**
     * 判断是否存在子菜单
     *
     * @param int $id
     *
     * @return bool
     */
    public function hasSonMenus($id)
    {
        $data = $this->db()->table($this->table)
            ->where('pid', $id)
            ->columns(['count(id)' => 'nums'])
            ->select();
        return $data[0]['nums'] > 0;
    }

    /**
     * 根据url获取菜单 主要用于获取不显示到菜单的菜单
     *
     * @param string $url
     *
     * @return array | bool
     */
    public function getByUrl($url)
    {
        $menu = $this->db()->table($this->table)
            ->where('url', $url)
            ->select();
        return isset($menu[0]) ? $menu[0] : false;
    }
}