<?php

// +----------------------------------------------------------------------
// | Laravel 10前后端分离旗舰版框架
// +----------------------------------------------------------------------
// | 版权所有 CMF研发中心
// +----------------------------------------------------------------------
// | 作者: Jason jasonps2020@gmail.com
// +----------------------------------------------------------------------
// | 免责声明:
// | 本软件框架禁止任何单位和个人用于任何违法、侵害他人合法利益等恶意的行为，禁止用于任何违
// | 反我国法律法规的一切平台研发，任何单位和个人使用本软件框架用于产品研发而产生的任何意外
// | 、疏忽、合约毁坏、诽谤、版权或知识产权侵犯及其造成的损失 (包括但不限于直接、间接、附带
// | 或衍生的损失等)，本团队不承担任何法律责任。本软件框架只能用于公司和个人内部的法律所允
// | 许的合法合规的软件产品研发，详细声明内容请阅读《框架免责声明》附件；
// +----------------------------------------------------------------------

namespace App\Models;

//use App\Helpers\Jwt;
//use App\Helpers\JwtUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;

// 数据表前缀
defined('DB_PREFIX') or define('DB_PREFIX', DB::connection()->getTablePrefix());

/**
 * 缓存基类
 * @author zongjl
 * @date 2019/5/23
 * Class BaseModel
 * @package App\Models
 */
class BaseModel extends Model {  // 改为继承Laravel原生Model
    // 创建时间

    const CREATED_AT = 'created_at';
    // 更新时间
    const UPDATED_AT = 'updated_at';
    // 删除时间
    // const DELETED_AT = 'deleted_at';

    // 默认使用时间戳戳功能
    public $timestamps = false;
    // 人员ID
    public $userId;
    // 时间
    public $time;

    /**
     * 构造函数
     * @author zongjl
     * @date 2019/5/30
     */
    public function __construct() {
        // 获取用户ID
//        $this->userId = JwtUtils::getUserId();
    }

    /**
     * 获取当前时间
     * @return int 时间戳
     * @author zongjl
     * @date 2019/5/30
     */
    public function freshTimestamp() {
        return empty($this->timestamps) ? datetime(time(), 'Y-m-d H:i:s') : time();
    }

    /**
     * 避免转换时间戳为时间字符串
     * @param mixed $value 时间
     * @return mixed|string|null
     * @author zongjl
     * @date 2019/5/30
     */
    public function fromDateTime($value) {
        return $value;
    }

    /**
     * 获取时间戳格式
     * @return string 时间戳字符串格式
     * @author zongjl
     * @date 2019/5/30
     */
    public function getDateFormat() {
        return 'U';
    }

    /**
     * 添加或编辑
     * @param array $data 数据源
     * @param string $error 异常错误信息
     * @param bool $is_sql 是否打印SQL
     * @return number 返回受影响行数据ID
     * @author zongjl
     * @date 2019/5/23
     */
    public  function edit($data = [], &$error = '', $is_sql = false) {
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $rowId = 0;
        if ($id) {
            // 更新时间
            $data['updated_at'] = $this->freshTimestamp();
            // 更新人
//            $data['update_user'] = $this->userId;
            // 置空添加时间
            unset($data['created_at']);
            // 置空添加人
//            unset($data['create_user']);
        } else {
            // 添加时间
            $data['created_at'] = $this->freshTimestamp();
            $data['deleted_at'] = NULL;
//            // 添加人
//            $data['create_user'] = $this->userId;
        }
        // 格式化表数据
        $this->formatData($data, $id);
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);
//        $this->getLastSql(1);
        // 入库处理
        if ($id) {
            // 修改数据
            $result = $this->where('id', $id)->update($data);
            // 更新ID
            $rowId = $id;
        } else {
            // 新增数据
            $result = $this->insertGetId($data); //create($data)
//            $result = $this->insert($data);
            // 新增ID
            $rowId = $result;
        }



        if ($result !== false) {
            // 重置缓存
            if ($this->is_cache) {
                $data['id'] = $rowId;
                $this->cacheReset($rowId, $data, $id);
            }
        }
        return $rowId;
    }

    /**
     * 格式化数据
     * @param array $data 数组数据
     * @param int $id 数据记录ID
     * @param string $table 数据表
     * @return array 格式化数据
     * @author zongjl
     * @date 2019/5/24
     */
    private function formatData(&$data = [], $id = 0, $table = '') {
        $data_arr = [];
        $tables = $table ? explode(",", $table) : array($this->getTable());
        $item_data = [];
        foreach ($tables as $table) {
            $temp_data = [];
            $table_fields_list = $this->getTableFields($table);
            foreach ($table_fields_list as $field => $fieldInfo) {
                if ($field == "id") {
                    continue;
                }
                //对强制
                if (isset($data[$field])) {
                    if ($fieldInfo['Type'] == "int") {
                        $item_data[$field] = (int) $data[$field];
                    } else {
                        if (is_array($data[$field])) {
                            $item_data[$field] = (string) json_encode($data[$field], 256);
                        } else {
                            $item_data[$field] = (string) $data[$field];
                        }
                    }
                }
                if (!isset($data[$field]) && in_array($field, array('updated_at', 'created_at'))) {
                    continue;
                }
                //插入数据-设置默认值
                if (!$id && !isset($data[$field])) {
                    if($field!="deleted_at"){
                        $item_data[$field] = $fieldInfo['Default'];
                    }
                }
                if (isset($item_data[$field])) {
                    $temp_data[$field] = $item_data[$field];
                }
            }
            $data_arr[] = $temp_data;
        }
        $data = $item_data;
        return $data_arr;
    }

    /**
     * 获取数据表字段
     * @param string $table 数据表名
     * @return array 字段数组
     * @author zongjl
     * @date 2019/5/24
     */
    private function getTableFields($table = '') {
        $table = $table ? $table : $this->getTable();
        if (strpos($table, DB_PREFIX) === false) {
            $table = DB_PREFIX . $table;
        }
        $field_list = DB::select("SHOW FIELDS FROM {$table}");
        $info_list = [];
        foreach ($field_list as $row) {
            // 对象转数组格式
            $item = object_array($row);
            if ((strpos($item['Type'], "int") === false) || (strpos($item['Type'], "bigint") !== false)) {
                $type = "string";
                $default = $item['Default'] ? $item['Default'] : "";
            } else {
                $type = "int";
                $default = $item['Default'] ? $item['Default'] : 0;
            }
            $info_list[$item['Field']] = array(
                'Type' => $type,
                'Default' => $default
            );
        }
        return $info_list;
    }

    /**
     * 删除数据
     * @param int $id 删除数据ID
     * @param bool $is_sql 是否打印SQL
     * @return bool 返回true或false
     * @author zongjl
     * @date 2019/5/23
     */
    public function drop($id, $is_sql = false) {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        $result = $this->where('id', $id)->update(['mark' => 0]);
        if ($result !== false && $this->is_cache) {
            // 删除成功
            $this->cacheDelete($id);
        }
        return $result;
    }

    /**
     * 查询缓存信息
     * @param int $id 查询数据ID
     * @return string 返回查询结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getInfo($id) {
//        // 获取参数(用户提取操作人信息)
        $arg_list = func_get_args();
        $flag = isset($arg_list[0]) ? $arg_list[0] : 0;
//
        // 获取缓存信息
//        $info = $this->getCacheFunc("info", $id);
//        $info = $this->getCacheFunc("info", $id);//停用缓存
        $info = $this->where(["id" => $id])->first();
        $info = empty($info) ? [] : $info->toArray();
        if ($info) {

            // 添加时间
            if (!empty($info['created_at'])) {
                $info['created_at'] = datetime($info['created_at'], 'Y-m-d H:i:s');
            }

            // 更新时间
            if (!empty($info['updated_at'])) {
                $info['updated_at'] = datetime($info['updated_at'], 'Y-m-d H:i:s');
            }

            // 格式化信息(预留扩展方法,可不用)
            if (method_exists($this, 'formatInfo')) {
                $info = $this->formatInfo($info);
            }
        }
        return $info;
    }

    /**
     * 格式化数据
     * @param array $info 实体数据对象
     * @return array 返回实体对象
     * @author zongjl
     * @date 2019/5/23
     */
    public function formatInfo($info) {
        // 基类方法可不做任何操作，在子类重写即可
        // TODO...
        return $info;
    }

    /**
     * 查询记录总数
     * @param array $map 查询条件（默认：数组格式）
     * @param string $fields 查询字段
     * @param bool $is_sql 是否打印SQL
     * @return int 返回记录总数
     * @author zongjl
     * @date 2019/5/23
     */
    public function getCount($map = [], $fields = null, $is_sql = false) {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        if ($fields) {
            $count = $query->count($fields);
        } else {
            $count = $query->count();
        }
        return (int) $count;
    }

    /**
     * 查询某个字段的求和值
     * @param array $map 查询条件（默认：数组）
     * @param string $field 求和字段
     * @param bool $is_sql 是否打印SQL
     * @return string 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getSum($map = [], $field = '', $is_sql = false) {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        $result = $query->sum($field);
        return $result;
    }

    /**
     * 查询某个字段的最大值
     * @param array $map 查询条件（默认：数组）
     * @param string $field 查询字段
     * @param bool $is_sql 是否打印SQL
     * @return string 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getMax($map = [], $field = '', $is_sql = false) {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        $result = $$query->max($field);
        return $result;
    }

    /**
     * 查询某个字段的最小值
     * @param array $map 查询条件（默认：数组）
     * @param string $field 查询字典
     * @param bool $is_sql 是否打印SQL
     * @return string 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getMin($map = [], $field = '', $is_sql = false) {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        $result = $query->min($field);
        return $result;
    }

    /**
     * 查询某个字段的平均值
     * @param array $map 查询条件（默认：数组）
     * @param string $field 查询字段
     * @param bool $is_sql 是否打印SQL
     * @return string 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getAvg($map = [], $field = '', $is_sql = false) {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        $result = $query->avg($field);
        return $result;
    }

    /**
     * 查询某个字段的单个值
     * @param array $map 查询条件（默认：数组）
     * @param string $field 查询字段
     * @param bool $is_sql 是否打印SQL
     * @return string 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getValue($map = [], $field = 'id', $is_sql = false) {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        $result = $query->value($field);
        return $result;
    }

    /**
     * 查询单条数据
     * @param array $map 查询条件（默认：数组）
     * @param string $field 查询字段（默认：全部）
     * @param bool $is_sql 是否打印SQL
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getOne($map = [], $field = '*', $is_sql = false) {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 分析字段
        if (!is_array($field) && strpos($field, ',')) {
            $field = explode(',', $field);
        }
        // 链式操作
        $result = $query->select($field)->first();

        // 对象转数组
        return $result ? $result->toArray() : [];
    }

    /**
     * 根据记录ID获取某一行的值
     * @param int $id 记录ID
     * @param string $field 指定字段（默认：所有字段）
     * @param bool $is_sql 是否打印SQL
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getRow($id, $field = '*', $is_sql = false) {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 分析字段
        if (!is_array($field) && strpos($field, ',')) {
            $field = explode(',', $field);
        }
        // 链式操作
        $result = $this->where('id', $id)->select($field)->first();

        // 对象转数组
        return $result ? $result->toArray() : [];
    }

    /**
     * 获取某一列的值
     * @param array $map 查询条件
     * @param string $field 字段
     * @param bool $is_sql 是否打印SQL
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/29
     */
    public function getColumn($map = [], $field = 'id', $is_sql = false) {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        $result = $query->pluck($field);

        // 对象转数组
        return $result ? $result->toArray() : [];
    }

    /**
     * 根据条件查询单条缓存记录
     * @param array $map 查询条件
     * @param array $fields 查询字段
     * @param array $sort 排序
     * @param int $id 记录ID
     * @return array 结果返回值
     * @author zongjl
     * @date 2019/5/29
     */
    public function getInfoByAttr($map = [], $fields = [], $sort = [['id', 'desc']], $id = 0) {
        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 排除主键
        if ($id) {
            $map[] = ['id', '!=', $id];
        }

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 排序(支持多重排序)
        $query = $query->when($sort, function ($query, $sort) {
            foreach ($sort as $v) {
                $query->orderBy($v[0], $v[1]);
            }
        });

        // 链式操作
        $result = $query->select('id')->first();
        $result = $result ? $result->toArray() : [];

        // 查询缓存
        $data = [];
        if ($result) {
            $info = $this->getInfo($result['id']);
            if ($info && !empty($fields)) {
                // 分析字段
                if (!is_array($fields) && strpos($fields, ',')) {
                    $fields = explode(',', $fields);
                }
                foreach ($fields as $val) {
                    $data[trim($val)] = $info[trim($val)];
                }
                unset($info);
            } else {
                $data = $info;
            }
        }
        return $data;
    }

    /**
     * 获取数据表
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/29
     */
    public function getTablesList() {
        $tables = [];
        $database = strtolower(env('DB_DATABASE'));
        $sql = 'SHOW TABLES';
        $list = DB::select($sql);
        // 对象转数组
        $data = object_array($list);
        foreach ($data as $v) {
            $tables[] = $v["Tables_in_{$database}"];
        }
        return $tables;
    }

    /**
     * 检查表是否存在
     * @param string $table 数据表名
     * @return bool 返回结果：true存在,false不存在
     * @author zongjl
     * @date 2019/5/29
     */
    public function tableExists($table) {
        if (!empty(DB_PREFIX)) {
            if (strpos($table, DB_PREFIX) === false) {
                $table = DB_PREFIX . $table;
            }
        }
//        if (strpos($table, DB_PREFIX) === false) {
//            $table = DB_PREFIX . $table;
//        }
        $tables = $this->getTablesList();
        return in_array($table, $tables) ? true : false;
    }

    /**
     * 删除数据表
     * @param string $table 数据表名
     * @return mixed 结果返回值
     * @author zongjl
     * @date 2019/5/29
     */
    public function dropTable($table) {
        if (strpos($table, DB_PREFIX) === false) {
            $table = DB_PREFIX . $table;
        }
        return DB::statement("DROP TABLE {$table}");
    }

    /**
     * 获取表字段
     * @param string $table 数据表名
     * @return array 字段数组
     * @author zongjl
     * @date 2019/5/29
     */
    public function getFieldsList($table) {
        if (strpos($table, DB_PREFIX) === false) {
            $table = DB_PREFIX . $table;
        }
        $fields = [];
        $list = DB::select("SHOW COLUMNS FROM {$table}");
        // 对象转数组
        $data = object_array($list);
        foreach ($data as $v) {
            $fields[$v['Field']] = $v['Type'];
        }
        return $fields;
    }

    /**
     * 检查字段是否存在
     * @param string $table 数据表名
     * @param string $field 字段名
     * @return bool 返回结果true或false
     * @author zongjl
     * @date 2019/5/29
     */
    public function fieldExists($table, $field) {
        $fields = $this->getFieldsList($table);
        return array_key_exists($field, $fields);
    }

    /**
     * 插入数据(不存在缓存操作,请慎用)
     * @param array $data 数据源
     * @param bool $get_id 是否返回插入主键ID：true返回、false不返回
     * @return mixed 返回结果
     * @author zongjl
     * @date 2019/5/29
     */
    public function doInsert($data, $get_id = true) {
        if ($get_id) {
            // 插入数据并返回主键
            return $this->insertGetId($data);
        } else {
            // 返回影响数据的条数，没修改任何数据返回 0
            return $this->insert($data);
        }
    }

    /**
     * 更新数据(不存在缓存操作,请慎用)
     * @param array $data 数据源
     * @param array $where 更新条件
     * @param bool $is_sql
     * @return mixed 返回结果
     * @author zongjl
     * @date 2019/5/29
     */
    public function doUpdate($data, $where, $is_sql = false) {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $where);

        return $query->update($data);
    }

    /**
     * 删除数据(不存在缓存操作,请慎用)
     * @param array $where 查询条件
     * @param bool $is_sql 是否打印SQL
     * @return mixed 返回结果
     * @author zongjl
     * @date 2019/5/29
     */
    public function doDelete($where, $is_sql = false) {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $where);

        return $query->delete();
    }

    /**
     * 批量插入数据
     * @param array $data 数据源
     * @param bool $is_cache 是否设置缓存：true设置,false不设置
     * @return bool 返回结果true或false
     * @author zongjl
     * @date 2019/5/30
     */
    public function insertAll($data, $is_cache = true) {
        if (!is_array($data)) {
            return false;
        }
        if ($is_cache) {
            // 插入数据并设置缓存
            $num = 0;
            foreach ($data as $val) {
                $result = $this->edit($val);
                if ($result) {
                    $num++;
                }
            }
            return $num ? true : false;
        } else {
            // 插入数据不设置缓存
            return $this->insert($data);
        }
        return false;
    }

    /**
     * 批量更新数据
     * @param array $data 数据源(备注，需要更新的数据对象中必须包含有效主键)
     * @param bool $is_cache 是否设置缓存：true设置,false不设置
     * @return bool 返回结果true或false
     * @author zongjl
     * @date 2019/5/30
     */
    public function saveAll($data, $is_cache = true) {
        if (!is_array($data)) {
            return false;
        }

        $num = 0;
        foreach ($data as $val) {
            if (!isset($val['id']) || empty($val['id'])) {
                continue;
            }
            if ($is_cache) {
                // 更新数据并设置缓存
                $result = $this->edit($val);
            } else {
                // 更新数据不设置缓存
                $id = $val['id'];
                unset($val['id']);
                $result = $this->where('id', $id)->update($val);
            }
            if ($result) {
                $num++;
            }
        }
        return $num ? true : false;
    }

    /**
     * 批量删除
     * @param array $data 删除记录ID(支持传入数组和逗号分隔ID字符串)
     * @param bool $is_force 是否物理删除,true物理删除false软删除
     * @return bool 返回结果true或false
     * @author zongjl
     * @date 2019/5/30
     */
    public function deleteAll($data, $is_force = false) {
        if (empty($data)) {
            return false;
        }
        if (!is_array($data)) {
            $data = explode(',', $data);
        }

        $num = 0;
        foreach ($data as $val) {
            if ($is_force) {
                // 物理删除
                $result = $this->where('id', $val)->delete();
                if ($result) {
                    $this->cacheDelete($val);
                }
            } else {
                // 软删除
                $result = $this->drop($val);
            }
            if ($result) {
                $num++;
            }
        }
        return $num ? true : false;
    }

    /**
     * 获取数据列表【根据业务场景需要，封装的获取列表数据的常用方法】
     * @param array $map 查询条件
     * @param array $sort 排序（默认：id asc）
     * @param string $limit 限制条数
     * @param bool $is_sql 是否打印SQL
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getList($map = [], $sort = [['id', 'asc']], $limit = '', $is_sql = false) {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 数据分页设置
        if ($limit) {
            list($offset, $page_size) = explode(',', $limit);
            $query = $query->offset($offset)->limit($page_size);
        }

        // 排序(支持多重排序)
        $query = $query->when($sort, function ($query, $sort) {
            foreach ($sort as $v) {
                $query->orderBy($v[0], $v[1]);
            }
        });

        // 查询数据并将对象转数组
        $result = $query->select('id')->get();
        $result = $result ? $result->toArray() : [];

        if (1) {
            $list = [];
            if ($result) {
                foreach ($result as $val) {
                    $info = $this->getInfo($val['id']);
                    if (!$info) {
                        continue;
                    }
                    $list[] = $info;
                }
            }
            return $list;
        } else {
            $list = $result;
        }
        return $list;
    }

    /**
     * 获取数据列表
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/27
     */
    public function getData() {
        // 获取参数
        $arg_list = func_get_args();

        // 查询参数
        $map = isset($arg_list[0]['query']) ? $arg_list[0]['query'] : [];
        // 排序
        $sort = isset($arg_list[0]['sort']) ? $arg_list[0]['sort'] : [['id', 'desc']];
        // 获取条数
        $limit = isset($arg_list[0]['limit']) ? $arg_list[0]['limit'] : '';
        // 回调方法名
        $func = isset($arg_list[1]) ? $arg_list[1] : "Short";
        // 自定义MODEL
        $model = isset($arg_list[2]) ? $arg_list[2] : $this;

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($model, $map);

        // 排序(支持多重排序)
        $query = $query->when($sort, function ($query, $sort) {
            foreach ($sort as $v) {
                $query->orderBy($v[0], $v[1]);
            }
        });

        // 查询数据源
        if ($limit) {
            list($offset, $page_size) = explode(',', $limit);
            $query->offset($offset)->limit($page_size);
        } else {
            // TODO...
        }

        // 查询数据并转为数组
        $result = $query->select('id')->get();
        $result = $result ? $result->toArray() : [];
        $list = [];
        if (is_array($result)) {
            foreach ($result as $val) {
                $info = $model->getInfo($val['id']);
                if (!$info) {
                    continue;
                }
                if (is_object($func)) {
                    // 方法函数
                    $data = $func($info);
                } else {
                    // 直接返回
                    $data = $info;
                }
                $list[] = $data;
            }
        }
        return $list;
    }

    /**
     * 获取数据列表
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/27
     */
    public function pageData() {
        // 获取参数
        $arg_list = func_get_args();
        // 查询参数
        $map = isset($arg_list[0]['query']) ? $arg_list[0]['query'] : [];
        // 排序
        $sort = isset($arg_list[0]['sort']) ? $arg_list[0]['sort'] : [['id', 'desc']];
        // 页码
        $page = isset($arg_list[0]['page']) ? $arg_list[0]['page'] : 1;
        // 每页数
        $perpage = isset($arg_list[0]['perpage']) ? $arg_list[0]['perpage'] : 20;
        // 回调方法名
        $func = isset($arg_list[1]) ? $arg_list[1] : "Short";
        // 自定义MODEL
        $model = isset($arg_list[2]) ? $arg_list[2] : $this;

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 分页设置
        $start = ($page - 1) * $perpage;
        $limit = "{$start},{$perpage}";

        // 闭包查询条件格式化
        $query = $this->formatQuery($model, $map);

        // 查询总数
        $count = $query->count();

        // 排序(支持多重排序)
        $query = $query->when($sort, function ($query, $sort) {
            foreach ($sort as $v) {
                $query->orderBy($v[0], $v[1]);
            }
        });

        // 分页设置
        list($offset, $page_size) = explode(',', $limit);
        $result = $query->offset($offset)->limit($page_size)->select('id')->get();
        $result = $result ? $result->toArray() : [];

        $list = [];
        if (is_array($result)) {
            foreach ($result as $val) {
                $info = $model->getInfo($val['id']);
                if (!$info) {
                    continue;
                }
                if (is_object($func)) {
                    //方法函数
                    $data = $func($info);
                } else {
                    // 直接返回
                    $data = $info;
                }
                $list[] = $data;
            }
        }

        // 返回结果
        $result = array(
            'count' => $count,
            'perpage' => $perpage,
            'page' => $page,
            'list' => $list,
        );
        return $result;
    }

    /**
     * 格式化查询条件
     * @param Model $model 模型
     * @param array $map 查询条件
     * @return mixed 返回结果
     * @author zongjl
     * @date 2019/5/30
     */
    public function formatQuery($model, $map) {
        $query = $model->where(function ($query) use ($map) {
            foreach ($map as $k => $v) {
                if ($v instanceof \Closure) {
                    $query = $query->where($v);
                    continue;
                }
                // 判断是否是键值对类型
                if ($key = @key($v) !== 0) {
//                    $key = key($v);
                    $val = empty($v[$key]) ? 0 : $v[$key];
                    $v = [$key, (is_array($val) ? 'in' : '='), $val];
                }


                switch (empty($v[1]) ? 0 : $v[1]) {
                    case 'like':
                        // like查询
                        if (strpos($v[0], '|') !== false) {
                            $query->where(function ($query) use ($v) {
                                $item = explode('|', $v[0]);
                                foreach ($item as $vo) {
                                    $query->orWhere($vo, $v[1], $v[2]);
                                }
                            });
                        } else {
                            $query->where($v[0], $v[1], $v[2]);
                        }
                        break;
                    case 'in':
                        // in查询
                        if (!is_array($v[2])) {
                            $v[2] = explode(',', $v[2]);
                        }
                        $query->whereIn($v[0], $v[2]);
                        break;
                    case 'not in':
                        // not in查询
                        if (!is_array($v[2])) {
                            $v[2] = explode(',', $v[2]);
                        }
                        $query->whereNotIn($v[0], $v[2]);
                        break;
                    case 'between':
                        // between查询
                        if (!is_array($v[2])) {
                            $v[2] = explode(',', $v[2]);
                        }
                        $query->whereBetween($v[0], $v[2]);
                        break;
                    case 'not between':
                        // not between查询
                        if (!is_array($v[2])) {
                            $v[2] = explode(',', $v[2]);
                        }
                        $query->whereNotBetween($v[0], $v[2]);
                        break;
                    case 'null':
                        // null查询
                        $query->whereNull($v[0]);
                        break;
                    case "not null":
                        // not null查询
                        $query->whereNotNull($v[0]);
                        break;
                    case "or":
                        // or查询
                        //格式：or (status=1 and status=2)
                        $where = $v[0];
                        $query->orWhere(function ($query) use ($where) {
                            // 递归解析查询条件
                            $this->formatQuery($query, $where);
                        });
                        break;
                    case 'xor':
                        // xor查询
                        // 格式：and (status=1 or status=2)
                        $where = $v[0];
                        $query->where(function ($query) use ($where) {
                            foreach ($where as $w) {
                                $query->orWhere(function ($query) use ($w) {
                                    // 递归解析查询条件
                                    $this->formatQuery($query, [$w]);
                                });
                            }
                        });
                        break;
                    default:
                        // 常规查询
                        if (count($v) == 2) {
//                            $query->where($v[0], '=', $v[1]);
                            if (is_array($v)) {
                                $query->wherein($k, $v);
                            } else {
                                $query->where($v[0], '=', $v[1]);
                            }
                        } else {
                            if (is_array($v[2]) && $v[1] == "=") {
                                $query->wherein($v[0], $v[2]);
                            } else {
                                $query->where($v[0], $v[1], $v[2]);
                            }
                        }
                        break;
                }
            }
        });
        return $query;
    }

    /**
     * 添加打印SQL语句监听事件
     * @param bool $is_sql 是否打印SQL
     * @author zongjl
     * @date 2019/5/29
     */
    public function is_sql($is_sql = false) {
        self::getLastSql($is_sql);
    }

    /**
     * 添加打印SQL语句监听事件
     * @param bool $is_sql 是否打印SQL
     * @author zongjl
     * @date 2019/5/29
     */
    public function getLastSql($is_sql = false) {
        if ($is_sql) {
            DB::listen(function ($query) {
                $bindings = $query->bindings;
                $sql = $query->sql;
                foreach ($bindings as $replace) {
                    $value = is_numeric($replace) ? $replace : "'" . $replace . "'";
                    $sql = preg_replace('/\?/', $value, $sql, 1);
                }
                echo $sql . ";\n";
            });
        }
    }

    /**
     * 开启事务
     * @author zongjl
     * @date 2019/5/30
     */
    public function startTrans() {
        // 事务-缓存相关处理
        $GLOBALS['trans'] = true;
        $transId = uniqid("trans_");
        $GLOBALS['trans_id'] = $transId;
        $GLOBALS['trans_keys'] = [];
        $info = debug_backtrace();
        $this->setCache($transId, $info[0]);

        // 开启事务
        DB::beginTransaction();
    }

    /**
     * 事务回滚
     * @author zongjl
     * @date 2019/5/30
     */
    public function rollBack() {
        // 事务回滚
        DB::rollBack();

        // 回滚缓存处理
        foreach ($GLOBALS['trans_keys'] as $key) {
            $this->deleteCache($key);
        }
        $this->deleteCache($GLOBALS['trans_id']);
        $GLOBALS['trans'] = false;
        $GLOBALS['trans_keys'] = [];
    }

    /**
     * 提交事务
     * @author zongjl
     * @date 2019/5/30
     */
    public function commit() {
        // 提交事务
        DB::commit();

        // 事务缓存同步删除
        $GLOBALS['trans'] = false;
        $GLOBALS['trans_keys'] = [];
        $this->deleteCache($GLOBALS['trans_id']);
    }

    /**
     * 开启执行日志
     * @author zongjl
     * @date 2019/5/31
     */
    public function beginSQLLog() {
        DB::connection()->enableQueryLog();
    }

    /**
     * 结束日志并打印
     * @author zongjl
     * @date 2019/5/30
     */
    public function endSQLLog() {
        // 获取查询语句、参数和执行时间
        $result = DB::getLastSql();
        if ($result) {
            foreach ($result as &$val) {
                $bindings = $val['bindings'];
                $sql = $val['query'];
                foreach ($bindings as $replace) {
                    $value = is_numeric($replace) ? $replace : "'" . $replace . "'";
                    $val['query'] = preg_replace('/\?/', $value, $sql, 1);
                }
            }
        }
        print_r($result);
        exit;
    }

    // ... existing code ...

    /**
     * 垂直分表（按字段拆分）公共方法
     * @param string $mainTable 主表名（不带前缀）
     * @param string $subTable 子表名（不带前缀）
     * @param array $splitFields 需要拆分到子表的字段列表（如：['avatar', 'bio']）
     * @return array 包含操作SQL或错误信息的数组（格式：['success' => bool, 'data' => SQL数组|'error' => 错误信息]）
     */
    public function verticalSharding(string $mainTable, string $subTable, array $splitFields): array {
        // 校验主表是否存在
        if (!$this->tableExists($mainTable)) {
            return ['success' => false, 'error' => "主表 {$mainTable} 不存在"];
        }

        // 校验拆分字段是否存在
        $mainFields = array_keys($this->getFieldsList($mainTable));
        $missingFields = array_diff($splitFields, $mainFields);
        if (!empty($missingFields)) {
            return ['success' => false, 'error' => "主表缺少字段: " . implode(', ', $missingFields)];
        }

        $mainTableWithPrefix = DB_PREFIX . $mainTable;
        $subTableWithPrefix = DB_PREFIX . $subTable;
        $sqlList = [];

        // 1. 创建子表（若不存在）
        if (!$this->tableExists($subTable)) {
            $subFields = $this->getFieldsList($mainTable);
            $createSql = "CREATE TABLE {$subTableWithPrefix} (";
            $createSql .= "id INT UNSIGNED AUTO_INCREMENT COMMENT '子表主键',";
            $createSql .= "{$mainTable}_id INT UNSIGNED NOT NULL COMMENT '主表外键',";
            foreach ($splitFields as $field) {
                $createSql .= "{$field} {$subFields[$field]},";
            }
            $createSql .= "created_at INT COMMENT '创建时间',";
            $createSql .= "updated_at INT COMMENT '更新时间',";
            $createSql .= "PRIMARY KEY (id),";
            $createSql .= "INDEX idx_{$mainTable}_id ({$mainTable}_id)";
            $createSql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='{$mainTable}垂直分表（{$subTable}）';";
            $sqlList[] = $createSql;
        }

        // 2. 迁移数据到子表
        $fieldStr = implode(', ', $splitFields);
        $sqlList[] = "INSERT INTO {$subTableWithPrefix} ({$mainTable}_id, {$fieldStr}, created_at, updated_at) 
                      SELECT id, {$fieldStr}, created_at, updated_at FROM {$mainTableWithPrefix};";

        // 3. 删除主表拆分字段
        foreach ($splitFields as $field) {
            $sqlList[] = "ALTER TABLE {$mainTableWithPrefix} DROP COLUMN {$field};";
        }

        return ['success' => true, 'data' => $sqlList];
    }

    /**
     * 水平分表（按行拆分）公共方法（示例：按ID哈希分表）
     * @param string $mainTable 主表名（不带前缀）
     * @param int $shardNum 分表数量（如：4）
     * @param string $shardField 分表依据字段（如：'user_id'）
     * @return array 包含操作SQL或错误信息的数组
     */
    public function horizontalSharding(string $mainTable, int $shardNum, string $shardField = 'id'): array {
        if (!$this->tableExists($mainTable)) {
            return ['success' => false, 'error' => "主表 {$mainTable} 不存在"];
        }

        $mainTableWithPrefix = DB_PREFIX . $mainTable;
        $sqlList = [];

        // 1. 创建分表（格式：主表名_0, 主表名_1...）
        for ($i = 0; $i < $shardNum; $i++) {
            $subTable = "{$mainTable}_{$i}";
            $subTableWithPrefix = DB_PREFIX . $subTable;
            if (!$this->tableExists($subTable)) {
                $createSql = "CREATE TABLE {$subTableWithPrefix} LIKE {$mainTableWithPrefix};";
                $sqlList[] = $createSql;
            }
        }

        // 2. 迁移数据到分表（示例：按ID哈希取模）
        for ($i = 0; $i < $shardNum; $i++) {
            $subTable = "{$mainTable}_{$i}";
            $subTableWithPrefix = DB_PREFIX . $subTable;
            $sqlList[] = "INSERT INTO {$subTableWithPrefix} 
                          SELECT * FROM {$mainTableWithPrefix} 
                          WHERE MOD({$shardField}, {$shardNum}) = {$i};";
        }

        // 3. 清空主表数据（可选，根据业务需求）
        $sqlList[] = "TRUNCATE TABLE {$mainTableWithPrefix};";

        return ['success' => true, 'data' => $sqlList];
    }

    /**
     * 混合分表（垂直+水平）公共方法
     * @param string $mainTable 主表名（不带前缀）
     * @param string $verticalSubTable 垂直分表后的子表名（不带前缀）
     * @param array $splitFields 垂直拆分字段列表
     * @param int $horizontalShardNum 水平分表数量
     * @param string $shardField 水平分表依据字段
     * @return array 包含操作SQL或错误信息的数组
     */
    public function mixedSharding(
            string $mainTable,
            string $verticalSubTable,
            array $splitFields,
            int $horizontalShardNum,
            string $shardField = 'id'
    ): array {
        // 先执行垂直分表
        $verticalResult = $this->verticalSharding($mainTable, $verticalSubTable, $splitFields);
        if (!$verticalResult['success']) {
            return $verticalResult;
        }

        // 对垂直分表后的主表和子表分别执行水平分表
        $horizontalMainResult = $this->horizontalSharding($mainTable, $horizontalShardNum, $shardField);
        $horizontalSubResult = $this->horizontalSharding($verticalSubTable, $horizontalShardNum, $shardField);

        if (!$horizontalMainResult['success'] || !$horizontalSubResult['success']) {
            return [
                'success' => false,
                'error' => "水平分表失败：主表错误[{$horizontalMainResult['error']}]，子表错误[{$horizontalSubResult['error']}]"
            ];
        }

        return [
            'success' => true,
            'data' => array_merge(
                    $verticalResult['data'],
                    $horizontalMainResult['data'],
                    $horizontalSubResult['data']
            )
        ];
    }

// ... existing code ...
    // ... existing code ...

    /**
     * 垂直分表查询（根据主表ID查询子表数据）
     * @param int $mainId 主表记录ID
     * @param string $subTable 垂直分表后的子表名（不带前缀）
     * @param array|string $fields 查询字段（默认*）
     * @param bool $isSql 是否打印SQL
     * @return array 子表数据数组（空数组表示无数据或子表不存在）
     * @author 开发团队
     * @date 2024-07-17
     */
    public function verticalShardingQuery(int $mainId, string $subTable, $fields = '*', bool $isSql = false): array {
        // 垂直分表查询示例（查询用户ID=123的扩展信息）
        // $verticalData = $baseModel->verticalShardingQuery( mainId: 123,subTable: 'users_extra',fields: 'avatar,bio');
        // 校验子表是否存在
        if (!$this->tableExists($subTable)) {
            return ['error' => "子表 {$subTable} 不存在"];
        }

        $subTableWithPrefix = DB_PREFIX . $subTable;
        $mainTable = $this->getTable(); // 当前模型对应的主表名
        // 注册打印SQL监听事件
        $this->getLastSql($isSql);

        // 构建查询（主表ID关联子表外键）
        $query = DB::table($subTableWithPrefix)
                ->where("{$mainTable}_id", $mainId); // 假设外键为`主表名_id`
        // 处理查询字段
        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }
        $result = $query->select($fields)->get()->toArray();

        return empty($result) ? [] : object_array($result);
    }

    /**
     * 水平分表查询（根据分表规则定位子表并查询）
     * @param mixed $shardValue 分表依据字段的值（如用户ID）
     * @param int $shardNum 分表数量（如4）
     * @param string $mainTable 主表名（不带前缀，默认当前模型表）
     * @param array $map 查询条件（格式：[['字段', '操作符', '值'], ...]）
     * @param bool $isSql 是否打印SQL
     * @return array 子表数据数组（空数组表示无数据或分表不存在）
     * @author 开发团队
     * @date 2024-07-17
     */
    public function horizontalShardingQuery($shardValue, int $shardNum, string $mainTable = '', array $map = [], bool $isSql = false): array {

        // 水平分表查询示例（查询用户ID=456在分表数量为4时的订单数据）
        // $horizontalData = $baseModel->horizontalShardingQuery(    shardValue: 456,    shardNum: 4,    mainTable: 'orders',    map: [['status', '=', 'paid']]);

        $mainTable = $mainTable ?: $this->getTable();
        $shardIndex = abs(crc32((string) $shardValue) % $shardNum); // 哈希取模计算分表索引
        $subTable = "{$mainTable}_{$shardIndex}"; // 子表名格式：主表名_索引
        // 校验子表是否存在
        if (!$this->tableExists($subTable)) {
            return ['error' => "分表 {$subTable} 不存在"];
        }

        $subTableWithPrefix = DB_PREFIX . $subTable;

        // 注册打印SQL监听事件
        $this->getLastSql($isSql);

        // 构建查询
        $query = DB::table($subTableWithPrefix);

        // 应用查询条件
        foreach ($map as $condition) {
            $query->where(...$condition);
        }

        $result = $query->get()->toArray();
        return empty($result) ? [] : object_array($result);
    }

    /**
     * 混合分表查询（垂直+水平分表联合查询）
     * @param int $mainId 主表记录ID
     * @param string $verticalSubTable 垂直分表后的子表名（不带前缀）
     * @param int $horizontalShardNum 水平分表数量
     * @param array|string $fields 查询字段（默认*）
     * @param bool $isSql 是否打印SQL
     * @return array 混合分表数据数组（包含主表和子表关联数据）
     * @author 开发团队
     * @date 2024-07-17
     */
    public function mixedShardingQuery(int $mainId, string $verticalSubTable, int $horizontalShardNum, $fields = '*', bool $isSql = false): array {

        // 混合分表查询示例（查询用户ID=789的主表和扩展分表数据）
        // $mixedData = $baseModel->mixedShardingQuery(    mainId: 789,    verticalSubTable: 'users_extra',    horizontalShardNum: 4,    fields: 'avatar,bio');
        // 1. 垂直分表查询：获取主表关联的子表数据
        $verticalData = $this->verticalShardingQuery($mainId, $verticalSubTable, $fields, $isSql);
        if (isset($verticalData['error'])) {
            return $verticalData; // 垂直分表查询失败
        }

        // 2. 水平分表查询：根据主表ID定位水平分表
        $horizontalData = $this->horizontalShardingQuery(
                $mainId,
                $horizontalShardNum,
                $this->getTable(),
                [['id', '=', $mainId]],
                $isSql
        );
        if (isset($horizontalData['error'])) {
            return $horizontalData; // 水平分表查询失败
        }

        // 3. 合并主表和子表数据（示例：按ID关联）
        return [
            'main_table_data' => $horizontalData[0] ?? [],
            'vertical_sub_table_data' => $verticalData
        ];
    }

// ... existing code ...
// ... existing code ...

    /**
     * object转array
     * @param type $object
     * @return type
     */
    private function object2array_pre($object) {
        if (is_object($object)) {
            $arr = (array) ($object);
        } else {
            $arr = $object;
        }
        if (is_array($arr)) {
            foreach ($arr as $varName => $varValue) {
                $arr[$varName] = self::object2array_pre($varValue);
            }
        }
        return $arr;
    }

//    // 定义时间字段存储格式（Y-m-d H:i:s）
//    protected $dateFormat = 'Y-m-d H:i:s';
//    // 启用默认时间戳（若 BaseModel 未覆盖）
//    public $timestamps = true;
    /**
     * 模型引导方法（用于自定义时间字段自动填充）
     */
//    public static function boot()
//    {
//        parent::boot();
//
//        // 创建时自动填充 email_time 为当前时间
//        static::creating(function ($model)  {
//            $model->created_at = date("Y-m-d H:i:s"); // now() 生成当前时间（Carbon 实例）
//        });
//
//        // 可选：更新时保持 email_time 不变（或根据需求修改）
//        static::updating(function ($model) {
//            $model->updated_at = date("Y-m-d H:i:s"); // 保持创建时的时间
//        });
//
//        // 可选：更新时保持 email_time 不变（或根据需求修改）
//        static::deleting(function ($model) {
//            $model->deleted_at = date("Y-m-d H:i:s"); // 保持创建时的时间
//        });
//    }
}
