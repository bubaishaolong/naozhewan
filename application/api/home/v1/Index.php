<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/20
 * Time: 9:12
 */

namespace app\api\home\v1;

use app\api\home\Home;

class Index extends Home
{
    /**
     * 请求的链接
     * public/index.php/api/v1/index/index
     */
    public function index(){
        $data =model('shop/menber')->select();//model(模块/模型下的模型名)
        //model('shop/menber')->validate($data);
        return Api(200,$data,'获取消息成功');
        $data = $this->request->Post();
        //json格式的内容输出
        $value['value'] = '584@qq.com';
        //validate(需要验证的数据,验证器的类名);
        $result =$this->validate($value,'User');
        if(true !== $result){
            // 验证失败 输出错误信息
            return Api(201,$result,'错误信息');
        }
        return Api(200,'data is null','获取消息成功');
    }
}