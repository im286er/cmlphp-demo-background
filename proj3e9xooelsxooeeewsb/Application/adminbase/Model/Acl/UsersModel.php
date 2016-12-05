<?php
namespace adminbase\Model\Acl;

use Cml\Model;

class UsersModel extends Model
{
    protected $table = 'users';

    /**
     * 获取用户列表
     *
     * @param int $limit
     *
     * @return array
     */
    public function getUsersList($limit = 20)
    {
        return $this->db()->table([$this->table => 'u'])
            ->columns('u.*', 'g.name')
            ->join(['groups' => 'g'], 'u.groupid=g.id')
            //->where('u.status', 1)
            ->orderBy('id', 'asc')
            ->paginate($limit);
    }
}