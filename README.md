
```
git clone https://github.com/suhaefifzn/laravel-rest-api-template.git

git clone https://gitweb.sbimghk.com/php/cmfLaravel12.git     gitweb    zS.&xRB8AYA.6#@

cd existing_repo
git remote add origin https://gitweb.sbimghk.com/php/cmfLaravel12.git
git branch -M main
git push -uf origin main
```





# LARAVEL REST API TEMPLATE WITH SOA PATTERN
Template untuk membuat REST API dengan Laravel + JWT + SOA (Service-Oriented Architecture).

## Stuktur Folder Tambahan
- Services - Menyimpan Logika Bisnis, dipanggil di Controller
- DTOs - Membungkus data input, agar tidak langsung mengoper $request

## Requirements
- php >= 8.2
- Composer

## Instalasi
- `composer install`
- `cp .env.example .env`
- `php artisan key:generate`
- `php artisan jwt:secret`
- buka `.env` kemudian isi variabel `CLIENT_TOKEN` dan `INITIAL_USER_PASSWORD`
- `php artisan migrate` untuk migrasi tabel users dan auth, setelahnya endpoint authentications sudah dapat dicoba dengan user default bawaan: `test@example.com`
- `php artisan make:dto {name}` untuk membuat DTO Class. Ganti `{name}` dengan nama class, contoh: `php artisan make:dto Game`
- `php artisan make:service {name}` untuk membuat Service Class. Ganti `{name}` dengan nama class, contoh: `php artisan make:service GameService`







## 部署 ##
```
    # php.ini 文件中解除限制函数
    'shell_exec','exec','symlink','proc_open','putenv'，"proc_get_status"
    # php.ini 一个个搜索看有没有删除 如果没删除的删除下 [ 如果还是无法执行看报错再删除下 ]

    case:1
        01  composer i;
        02  copy .env.example .env || cp .env.example .env ;
        03  php artisan key:generate;--生成Laravel Key
        04  php artisan jwt:secret; --生成JWT Key
        05  php artisan config:cache; --清空缓冲数据
        06  php artisan storage:link; --日志目录软指向
        07  php artisan migrate;  --生成数据表
        08  php artisan db:seed StoreMenu;  --添加Store商家权限
        09  php artisan db:seed AdminMenu;  --添加Admin数据表
        09  php artisan db:seed OrtherData;  --添加Admin数据表
    case:2
        01  composer i;
        02  copy .env.example .env || cp .env.example .env ;
        03  php artisan first:install;
    case:3
        02  php artisan schedule:run;    //运行定时任务    https://laravel.p2hp.com/cndocs/10.x/scheduling#defining-schedules
```




# composer #
```
进入项目根目录, 执行如下命令：
composer install
```

# 脚本 #
```
cd public/scaffold
sh run.sh user t_user
```


# storage #
```
   根目录下创建storage/app 文件夹 
   根目录下创建storage/framework/cache 文件夹 
   根目录下创建storage/framework/sessions 文件夹 
   根目录下创建storage/framework/views 文件夹 
   根目录下创建storage/logs 文件夹 
   chrome -R 777 storage 
```


# config #
```
所有的配置信息都在.env 文件中
```

# nginx #
```
server {
    listen 80;
    server_name ***.***.com;
    root   /basepath/carbon-api/public;
    index  index.html index.htm index.php server.php;

    # https配置
    listen 443 ssl;
    ssl_certificate /etc/nginx/cert/console.yffrom.com.pem;
    ssl_certificate_key /etc/nginx/cert/console.yffrom.com.key;
    ssl_session_timeout 5m;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE:ECDH:AES:HIGH:!NULL:!aNULL:!MD5:!ADH:!RC4;  #使用此加密套件
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
    ssl_prefer_server_ciphers on;

    # 跨越处理，可以按实际域名处理
    add_header Access-Control-Allow-Origin *;
    add_header Access-Control-Allow-Headers Origin,Accept,Authorization,platformId,ut,DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type;
    add_header Access-Control-Allow-Methods GET,POST,OPTIONS,PUT;
    add_header Access-Control-Allow-Credentials true;

    gzip on; #开启gzip功能
    gzip_types *; #压缩源文件类型,压缩所有
    gzip_comp_level 6; #gzip压缩级别
    gzip_vary on;  #往头信息中添加压缩标识
    gzip_min_length 1k; #进行压缩响应页面的最小长度,content-length
    gzip_http_version 1.1; #指定压缩响应所需要的最低HTTP请求版本
    gzip_disable "MSIE[1-6]\.";  #对IE6以下的版本都不进行压缩
    gzip_proxied off; #nginx作为反向代理不压缩服务端返回数据
    gzip_buffers 4 16k; #缓存空间大小

    # logs 确保路径存在且可以写入
    access_log /usr/log/nginx/access.log;
    error_log  /usr/log/nginx/error.log;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }
    location ~ \.php$ {                                                                           
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include        fastcgi_params;
        include fastcgi.conf;
        include pathinfo.conf;
    }
    # 静态资源文件
    location ~ /assets.*\.(html|htm|gif|jpg|jpeg|bmp|png|ico|txt|js|css|ttf|woff|ico|mp3)$         # 静态资源文件
    {
         root /basepath/carbon-api/resources;
         expires      30d;
    }
}
```

# 常用PHP公式 #
```
    bcadd — 加法 ,    bcsub — 减法,    bcmul — 乘法 ,   bcdiv — 除法,      bcpow — 乘方,   bcmod — 取模,   bcsqrt — 求二次方根

```

# 支付设置 #
```
    支付使用了 [yansongda v3](https://pay.yansongda.cn/ "yansongda v3") 的支付扩展
    web	电脑支付 
    wap	手机网站支付 
    app	APP支付	 
    pos	刷卡支付 
    scan	扫码支付	 
    transfer	账户转账 
    mini	小程序支付 

    payment/wechat/app    
    payment/alipay/transfer
```
## docker 启动

### 部署
命令会创建`main`分支镜像并推送私有镜像仓库
```
// TAG 最好填上版本号
make build-push [TAG=v1.x.x]
```

### 默认启动`latest`版本
 不建议在生产使用,应该指定版本号
```
make service-run 
```

### 指定版本号启动
- 指定版本号,版本不存在会创建镜像  
- 版本号存在会重启服务
```
make service-run TAG=v1.0.1
```

## Git 启动
```shell
    重新设置composer packagist，命令如下：
    1.（1） composer config -g repo.packagist composer https://packagist.phpcomposer.com
    （2）阿里云镜像：composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
      (3切换回官方源) composer config -g --unset repos.packagist

    2.重新加载插件 php composer.phar require --prefer-dist 插件名称

    http://abc.com/office.git
    添加Git用户登录密码配置
    git config --global --add safe.directory /www/wwwroot/cmf_laravel12.sbimghk.com

    git config --global credential.helper store
    git branch --set-upstream-to=origin/main_CN main_CN

    查看本地与远程分支
    git branch -a

    将远端分支检出到本地
    git checkout jason_new
    git pull
        
    远程有代码冲突
    git reset --hard
```




     * Create a new job instance.
     * 1.我们env是设置的database （异步消息队列）   
     *  QUEUE_DRIVER=database #sync:同步,database:数据库
      2.开启队列监听
      php artisan queue:listen
      3.或者 开启后台监听(不影响自己输入其他命令)
      php artisan queue:listen &
      4.也能用work 后面参数是休息时间和尝试次数
      php artisan queue:work connection --daemon --sleep=3 --tries=3





同步[2025/4/30/CHANE]里面的全部的资源
http://postimg.sbimg.cc/job/updateImg/DhHuaweiImgNotUp?jobUpFile=2025/4/30/CHANEL25 HOBO/01

图片同步  DhHuaweiImgNotUp   bast64 MjAyNC8xMS8wNi9OSUtF   =    2024/11/06/NIKE
php artisan  dh_order:huawie_img_not3_1 MjAyNC8xMS8wNi9OSUtF


PC页面点击网站部署不成功时可以通过命令行进行处理
php artisan run:site_build --type=buildAll  --params=449

composer require slowlyo/owl-admin
php artisan admin:publish
php artisan admin:install


composer require slowlyo/owl-dict
composer require slowlyo/owl-operation-log
composer require slowlyo/owl-amis-json-parse

# 安装必要的包Google邮件服务
### composer require google/apiclient
### composer require league/oauth2-google
composer require php-imap/php-imap
