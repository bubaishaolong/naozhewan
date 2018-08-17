<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017  [  ]
// +----------------------------------------------------------------------
// | 官方网站：http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | 作者: 无名氏
// +----------------------------------------------------------------------

// [ PHP版本检查 ]
header("Content-type: text/html; charset=utf-8");
if (version_compare(PHP_VERSION, '5.5', '<')) {
    die('PHP版本过低，最少需要PHP5.5，请升级PHP版本！');
}

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');



// 定义后台入口文件
define('ADMIN_FILE', 'admin.php');

// 检查是否安装
if(!is_file('../data/install.lock')){
    define('BIND_MODULE', 'install');
}
//查找到聊天服务器的位置Events
define('BINDMODULE','shop/gatewayworker/Events');

// 加载框架引导文件
require '../thinkphp/start.php';
