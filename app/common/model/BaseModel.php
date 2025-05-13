<?php
namespace app\common\model;

use think\Model;
use think\facade\Db;

class BaseModel extends Model
{
    /**
     * @Description: (删除表)
     * @param string $table_name 表名[不带前缀]
     * @return boolean
     * @author 子青时节 <654108442@qq.com>
     */
    public function dropTable($table_name)
    {
        $table_name = config('database.connections.mysql.prefix').strtolower($table_name);
        return Db::query("DROP TABLE IF EXISTS `{$table_name}`");
    }
    
    /**
     * @Description: (检查表是否存在)
     * @param string $table_name 表名[不带前缀]
     * @return boolean
     * @author 子青时节 <654108442@qq.com>
     */
    public function tableExist($table_name)
    {
        $table_name = config('database.connections.mysql.prefix').strtolower($table_name);
        if (Db::query("SHOW TABLES LIKE '{$table_name}'")) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * @Description: (获取表字段)
     * @param string $table_name 表名[不带前缀]
     * @return array
     * @author 子青时节 <654108442@qq.com>
     */
    public function getTableFields($table_name)
    {
        $fields = [];
        $table_name = config('database.connections.mysql.prefix').strtolower($table_name);
        $data = Db::query("SHOW COLUMNS FROM $table_name");
        foreach ($data as $v) {
            $fields[$v['Field']] = $v['Type'];
        }
        return $fields;
    }
    
    /**
     * @Description: (检查字段是否存在)
     * @param string $table_name 表名[不带前缀]
     * @return boolean
     * @author 子青时节 <654108442@qq.com>
     */
    public function tableFieldExist($table_name, $field)
    {
        $fields = $this->getTableFields($table_name);
        return array_key_exists($field, $fields);
    }
}