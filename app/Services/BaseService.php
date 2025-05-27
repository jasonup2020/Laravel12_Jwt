<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPInterface.php to edit this template
 */

namespace App\Services;

/**
 *
 * @author 003
 */
class BaseService {

    // 模型
    protected $model;
    // 验证类
    protected $validate;

    /**
     * 获取数据列表
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function getList() {
        // 初始化变量
        $map = $map_orther = $map_where = [];
        $sort = [['id', 'desc']];
        $is_sql = 0;

        // 获取参数
        $argList = func_get_args();
        if (!empty($argList)) {
            // 查询条件
            $map = (isset($argList[0]) && !empty($argList[0])) ? $argList[0] : [];
            if (!empty($map["where"]) || !empty($map["orther"])) {
                $map_where = !empty($map["where"]) ? $map["where"] : [];
                $map_orther = !empty($map["orther"]) ? $map["orther"] : [];
                $map = $map_where;
            }

            // 排序
            $sort = (isset($argList[1]) && !empty($argList[1])) ? $argList[1] : [['id', 'desc']];
            // 是否打印SQL
            $is_sql = isset($argList[2]) ? isset($argList[2]) : 0;
        }
        // 打印SQL
        if ($is_sql) {
            $this->model->getLastSql(1);
        }


        // 常规查询条件
        $param = request()->input();
        if ($param) {
            // 筛选名称
            if (isset($param['name']) && $param['name']) {
                $map[] = ['name', 'like', "%{$param['name']}%"];
            }

            // 筛选标题
            if (isset($param['title']) && $param['title']) {
                $map[] = ['title', 'like', "%{$param['title']}%"];
            }

            // 筛选类型
            if (isset($param['type']) && $param['type']) {
                $map[] = ['type', '=', $param['type']];
            }

            // 筛选状态
            if (isset($param['status']) && $param['status']) {
                $map[] = ['status', '=', $param['status']];
            }

            // 手机号码
            if (isset($param['mobile']) && $param['mobile']) {
                $map[] = ['mobile', '=', $param['mobile']];
            }
        }

        // 设置查询条件
        if (is_array($map)) {
            $map[] = ['mark', '=', 1];
        } elseif ($map) {
            $map .= " AND mark=1 ";
        } else {
            $map .= " mark=1 ";
        }

        // 排序(支持多重排序)
        $query = $this->model->formatQuery($this->model, $map)->when($sort, function ($query, $sort) {
            foreach ($sort as $v) {
                $query->orderBy($v[0], $v[1]);
            }
        });

//       
        $count = $query->count();
        // 分页条件
        $offset = (PAGE - 1) * PERPAGE;

        if (0) {
//            $result = $query->offset($offset)->limit(PERPAGE)->select('*')->get();
            $result = $query->offset($offset)->limit(PERPAGE)->select('id')->get();
            $result = $result ? $result->toArray() : [];
            $list = [];
            if (is_array($result)) {
                foreach ($result as $val) {
                    $info = $this->model->getInfo($val['id']);
                    //                $info = $this->model->getInfo($val);
                    $list[] = $info;
                }
            }
        } else {
            $result = $query->offset($offset)->limit(PERPAGE)->select('*')->get();
            $result = $result ? $result->toArray() : [];
            $list = $result;
        }



        //返回结果
        $message = array(
            "msg" => __("public.MESSAGE_OK"),
            "code" => 0,
            "data" => $list,
            "count" => $count,
        );
        return $message;
    }

    /**
     * 获取记录详情
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function info() {
        // 记录ID
        $id = request()->input("id", 0);
        $info = [];
        if ($id) {
            $info = $this->model->getInfo($id);
        }
        return message(__("public.MESSAGE_OK"), true, $info);
    }

    /**
     * 添加或编辑记录
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function edit() {
        // 获取参数
        $argList = func_get_args();
        // 查询条件
        $data = isset($argList[0]) ? $argList[0] : [];
        // 是否打印SQL
        $is_sql = isset($argList[1]) ? $argList[1] : false;
        if (!$data) {
            $data = request()->input();
        } else {
            $data = array_merge(request()->input(), $data);
        }
        $error = '';
        $rowId = $this->model->edit($data, $error, $is_sql);
        if ($rowId) {
            return message();
        }
        return message($error, false);
    }

    /**
     * 删除记录
     * @return array
     * @since 2020/11/12
     * @author 牧羊人
     */
    public function delete() {
        // 参数
        $param = request()->input();
        // 记录ID
        $ids = getter($param, "id");
        if (empty($ids)) {
            return message("记录ID不能为空", false);
        }
        if (is_array($ids)) {
            // 批量删除
            $result = $this->model->deleteAll($ids);
            if (!$result) {
                return message(__("public.MESSAGE_NO"), false);
            }
            return message(__("public.MESSAGE_OK"));
        } else {
            // 单个删除
            $info = $this->model->getInfo($ids);
            if ($info) {
                $result = $this->model->drop($ids);
                if ($result !== false) {
                    return message();
                }
            }
//            return message($this->model->getError(), false);
            return message(__("public.MESSAGE_NO"), false);
        }
    }

    /**
     * 设置记录状态
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function status() {
        $data = request()->input();
        // 记录ID
        $id = getter($data, "id", 0);
        if (!$id) {
            return message('记录ID不能为空', false);
        }
        // 状态
        $status = getter($data, "status");
        if (!$status) {
            return message('记录状态不能为空', false);
        }
        $error = '';
        $item = [
            'id' => $id,
            'status' => $status
        ];
        $rowId = $this->model->edit($item, $error);
        if (!$rowId) {
            return message($error, false);
        }
        return message();
    }

    /**
     * 获得状态相关说明 
     * @access getEnum?key=status,mark&is_query=1
     * @copyright (c) getEnum(["is_status"=>"0|否 1|是"])
     * @return type
     */
    public function getEnum() {
        $data = request()->input();
        // 记录ID
        $is_query = getter($data, "is_query", 0);
        $is_statu = getter($data, "key", "status,mark");
        $is_statu3 = getter($data, "key", "");
        if (!empty($is_statu)) {
            $is_statu = explode(",", $is_statu);
        }
        $is_label = "label";
        if (false !== strpos(request()->header('referer', ''), 'https://servicewechat.com')) {
            $is_label = "text";
        }
        $ret_pub = [[$is_label => '不限/全部', "value" => -1]];
        if (empty($is_query)) {
            $ret_pub = [];
        }
        $ret_pub[] = [$is_label => '正常', "value" => 1];
        $ret_pub[] = [$is_label => '停用', "value" => 2];

        $ret_ret=[];
        if (in_array("status", $is_statu)) {
            $ret_ret["status"] = $ret_pub;
        }
        if (in_array("mark", $is_statu)) {
            $ret_ret["mark"] = $ret_pub;
        }

        $argList = func_get_args();
        $orther_ret = isset($argList[0]) ? $argList[0] : [];
        
        if (!empty($orther_ret)) {
            $ret_pub2 = [[$is_label => '不限/全部', "value" => -1]];
            if (empty($is_query)) {
                $ret_pub2 = [];
            }
            foreach ($orther_ret as $key => $value) {
                $ret_pub3 = [];
                $is_statu4 = explode(" ", $value);
                $ret_pub3 = $ret_pub2;
                foreach ($is_statu4 as $key2 => $value1) {
                    $is_statu2 = explode("|", $value1);
                    $ret_pub3[] = [$is_label => $is_statu2[1] ?? "Null", "value" => (int) $is_statu2[0] ?? 0];
                }
                if (in_array("$key", $is_statu) || empty($is_statu3)) {
//                    $ret_ret[$key] = $ret_pub;
                    $ret_ret[$key] = $ret_pub3;
                }else{
                    if($is_statu3==$key){
                        $ret_ret[$key] = $ret_pub3;
                    }
                }
            }
        }
        return message(__("public.MESSAGE_OK"), true, $ret_ret);
    }

    /**
     * 返回方法初始化相关查询条件数据
     * @access list(SQL条件,SQL排序,SQL打印,SQL其它条件) = parent::retArgs(func_get_args());
     * @access array $map []|["where=>[]]|["orther=>[userinfo=>[]]]
     * @access list($map,$sort,$is_sql,$map_orther) = parent::retArgs(func_get_args());
     * @return list
     */
    public function retArgs() {
        $map = $map_orther = $map_where = [];
        $sort = [['id', 'desc']];
        $is_sql = 0;

        // 查询条件
        $argList = func_get_args()[0];
        if (!empty($argList)) {
            // 查询条件
            $map = (isset($argList[0]) && !empty($argList[0])) ? $argList[0] : [];
            if (!empty($map["where"]) || !empty($map["orther"])) {
                $map_where = !empty($map["where"]) ? $map["where"] : [];
                $map_orther = !empty($map["orther"]) ? $map["orther"] : [];
                $map = $map_where;
            }

            // 排序
            $sort = (isset($argList[1]) && !empty($argList[1])) ? $argList[1] : [['id', 'desc']];
            // 是否打印SQL
            $is_sql = isset($argList[2]) ? isset($argList[2]) : 0;
        }
        return [$map, $sort, $is_sql, $map_orther];
    }
}
