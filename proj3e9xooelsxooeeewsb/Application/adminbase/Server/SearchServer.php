<?php namespace adminbase\Server;

use Cml\Model;
use Cml\Server;
use Cml\Http\Input;
use Cml\View;

/**
 * 处理搜索相关逻辑
 *
 */
class SearchServer extends Server
{
    public static $timeField = 'ctime';

    /*
     * var
     * */
    public static $processTime = null;

    /**
     * 处理搜索
     *
     * @param array $fields
     * @param Model $model
     * @param bool|false $toTpl
     *
     * @return void
     */
    public static function processSearch($fields = [
        'userid' => '',
        'start_time' => '>',
        'end_time' => '<'
        ], &$model, $toTpl = false
    ){
        if (is_null(self::$processTime)) {
            self::$processTime = function ($time) {
                return $time;
            };
        }

        foreach ($fields as $field => $conditType) {
            if (!isset($_GET[$field])) {//用户没有搜索的时候也传一个空植到模板，避免notice
                View::getEngine()->assign($field, '');
                continue;
            }
            if (is_int($_GET[$field])) {
                $val = Input::getInt($field);
            } else if (is_array($_GET[$field])) {
                $val = $_GET[$field];
            } else {
                $val = Input::getString($field);
            }
            if (strpos($field, 'time') !== false) {
                $val = strtotime($val);
                if ($field == 'start_time') {
                    $timeFieldToTpl = 'startTime';
                } else {
                    $timeFieldToTpl = 'endTime';
                }
                $isTimeSearch = true;
            } else {
                $isTimeSearch = false;
            }

            if ($toTpl) {//用户没有搜索的时候也传一个空植到模板，避免notice
                if ($isTimeSearch) {
                    View::getEngine()->assign($timeFieldToTpl, $val ? date('Y/m/d H:i:s', $val) : '');
                } else {
                    if (!isset($val)) $val = '';
                    View::getEngine()->assign($field, $val);
                }
            }

            if (is_null($val) || $val === '' || $val === false) {
                continue;
            }

            $func = self::$processTime;
            switch ($conditType) {
                case 'like':
                    $model->db()->whereLike($field, true, $val, true);
                    break;
                case '>':
                    $model->db()->whereGte($isTimeSearch ? self::$timeField : $field, $isTimeSearch ? $func($val) : $val);
                    break;
                case '<' :
                    $model->db()->whereLte($isTimeSearch ? self::$timeField : $field, $isTimeSearch ? $func($val) + (3600 * 24 - 1) : $val);
                    break;
                case 'in' :
                    $model->db()->whereIn($field, $val);
                    break;
                default :
                    $model->db()->where($field, $val);
            }
        }
    }
}