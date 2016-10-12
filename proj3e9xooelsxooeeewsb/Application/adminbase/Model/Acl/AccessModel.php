<?php
namespace adminbase\Model\Acl;

use Cml\Model;

class AccessModel extends Model
{
    protected $table = 'access';

    /**
     * 通过字段获取有权限的菜单id数组
     *
     * @param array | int $id
     * @param string $field
     *
     * @return array
     */
    public function getAccessArrByField($id, $field = 'groupid')
    {
        is_array($id) || $id = array($id);
        return $this->db()->table($this->table)
            ->whereIn($field, $id)
            ->columns('menuid')
            ->select(0, 5000);
    }
}