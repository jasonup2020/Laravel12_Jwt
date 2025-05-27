<?php

// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Laravel 12前后端分离旗舰版框架
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

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 控制器基类
 */
class BaseController extends \Illuminate\Routing\Controller {

    // 模型
    protected $model;
    // 服务
    protected $service;
    // 校验
    protected $validate;
    // 登录ID
    protected $userId;
    // 登录信息
    protected $userInfo;

    /**
     * 构造函数
     * @author 牧羊人
     * @since 2020/11/10
     * BaseController constructor.
     */
    public function __construct() {
//        parent::__construct();

        // 初始化网络请求配置
        $this->initRequestConfig();

        // 初始化系统常量
        $this->initSystemConst();
        // 初始化配置
        $this->initConfig();

        
//        // 登录检测中间件
//        $this->middleware('user.login');
//
//        // 权限检测中间件
//        $this->middleware('check.auth');
//
//        // 初始化登录信息
//        $this->middleware(function ($request, $next) {
////            $userId = JwtUtils::getUserId();
////
////            // 登录验证
////            $this->initLogin($userId);
//
//            return $next($request);
//        });
    }

    /**
     * 初始化请求配置
     * @since 2020/11/10
     * @author 牧羊人
     */
    private function initRequestConfig() {
        // 定义是否GET请求
        defined('IS_GET') or define('IS_GET', \request()->isMethod('GET'));

        // 定义是否POST请求
        defined('IS_POST') or define('IS_POST', \request()->isMethod('POST'));

        // 定义是否AJAX请求
        defined('IS_AJAX') or define('IS_AJAX', \request()->ajax());

        // 定义是否PAJAX请求
        defined('IS_PJAX') or define('IS_PJAX', \request()->pjax());

        // 定义是否PUT请求
        defined('IS_PUT') or define('IS_PUT', \request()->isMethod('PUT'));

        // 定义是否DELETE请求
        defined('IS_DELETE') or define('IS_DELETE', \request()->isMethod('DELETE'));

        // 请求方式
        defined('REQUEST_METHOD') or define('REQUEST_METHOD', \request()->method());
    }

    /**
     * 初始化系统常量
     * @author 牧羊人
     * @since 2020/11/10
     */
    private function initSystemConst() {
        // 项目根目录
        defined('ROOT_PATH') or define('ROOT_PATH', base_path());

        // 文件上传目录
        defined('ATTACHMENT_PATH') or define('ATTACHMENT_PATH', base_path('public/uploads'));

        // 图片上传目录
        defined('IMG_PATH') or define('IMG_PATH', base_path('public/uploads/images'));

        // 临时存放目录
        defined('UPLOAD_TEMP_PATH') or define('UPLOAD_TEMP_PATH', ATTACHMENT_PATH . "/temp");

        // 定义普通图片域名
        defined('IMG_URL') or define('IMG_URL', env('IMG_URL', ""));

        // 数据表前缀
        defined('DB_PREFIX') or define('DB_PREFIX', DB::connection()->getTablePrefix());

        // 数据库名
        defined('DB_NAME') or define('DB_NAME', DB::connection()->getDatabaseName());

        // 系统全称
        defined('SITE_NAME') or define('SITE_NAME', env('SITE_NAME', ""));
        // 系统简称
        defined('NICK_NAME') or define('NICK_NAME', env('NICK_NAME', ""));
        // 系统版本号
        defined('VERSION') or define('VERSION', env('VERSION', ""));

        // // 新增：分页每页数量（默认15条）
        // defined('PERPAGE') or define('PERPAGE', (int) env('PERPAGE', 15));
        // // 新增：默认当前页码（默认第1页）
        // defined('PAGE') or define('PAGE', (int) env('PAGE', 1));
    }

    /**
     * 初始化配置
     * @return void
     * @author 牧羊人
     * @date: 2023/3/28 12:06
     */
    public function initConfig() {
        // 请求参数
        $this->param = \request()->input();

        // 分页基础默认值
        defined('PERPAGE') or define('PERPAGE', isset($this->param['limit']) ? $this->param['limit'] : 20);
        defined('PAGE') or define('PAGE', isset($this->param['page']) ? $this->param['page'] : 1);
    }

    /**
     * 登录验证
     * @param $userId 用户ID
     * @return void
     * @author 牧羊人
     * @date: 2023/3/28 12:06
     */
    public function initLogin($userId) {
        // 登录用户ID
        $this->userId = $userId;

        // 登录用户信息
        if ($userId) {
            $adminModel = new UserModel();
            $userInfo = $adminModel->getInfo($this->userId);
            $this->userInfo = $userInfo;
        }
    }

    /**
     * 获取数据列表
     * @return mixed
     * @author 牧羊人
     * @date: 2023/3/28 12:05
     */
    public function index() {
        $result = $this->service->getList();
        return $result;
    }

    /**
     * 获取数据详情
     * @return mixed
     * @author 牧羊人
     * @date: 2023/3/28 12:05
     */
    public function info() {
        $result = $this->service->info();
        return $result;
    }

    /**
     * 添加或编辑
     * @return mixed
     * @author 牧羊人
     * @date: 2023/3/28 12:05
     */
    public function edit() {
        $result = $this->service->edit();
        return $result;
    }

    /**
     * 删除数据
     * @return mixed
     * @author 牧羊人
     * @date: 2023/3/28 12:05
     */
    public function delete() {
        $result = $this->service->delete();
        return $result;
    }

    /**
     * 设置状态
     * @return mixed
     * @author 牧羊人
     * @date: 2023/3/28 12:05
     */
    public function status() {
        $result = $this->service->status();
        return $result;
    }

    /**
     * 批量删除
     * @return array|void
     * @author 牧羊人
     * @date: 2023/3/28 12:05
     */
    public function batchDelete() {
        if (IS_POST) {
            $ids = explode(',', $_POST['id']);
            //批量删除
            $num = 0;
            foreach ($ids as $key => $val) {
                $res = $this->model->drop($val);
                if ($res !== false) {
                    $num++;
                }
            }
            return message('本次共选择' . count($ids) . "个条数据,删除" . $num . "个");
        }
    }

}
