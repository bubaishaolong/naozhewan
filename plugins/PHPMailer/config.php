<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

/**
 * 插件配置信息
 */
return [
    ['text', 'host', '邮箱服务地址','例如：smtp.163.com'],
    ['number', 'port', '邮箱服务端口','例如：25'],
    ['text', 'form_user', '邮箱账户','发件人地址'],
    ['password', 'password', '邮箱授权码','->_->'],
    ['text', 'form_user_alias', '发件人别名','例如：隔壁老王'],
    ['radio', 'smtp_secure', ' SMTP','一般默认tls',['tls'=>'tls','ssl'=>'ssl'],'tls'],
];