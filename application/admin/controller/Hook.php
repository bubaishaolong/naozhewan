<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017  [  ]
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\model\HookPlugin;
use app\common\builder\ZBuilder;
use app\admin\model\Hook as HookModel;
use app\admin\model\HookPlugin as HookPluginModel;
use think\Db;

/**
 * 钩子控制器
 * @package app\admin\controller
 */
class Hook extends Admin
{
    /**
     * 钩子管理
     * @author 无名氏
     * @return mixed
     */
    public function index()
    {
        $map = $this->getMap();
        $order = $this->getOrder();

        // 数据列表
        $data_list = HookModel::where($map)->order($order)->paginate();

        // 分页数据
        $page = $data_list->render();

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setPageTitle('钩子管理') // 设置页面标题
            ->setSearch(['name' => '钩子名称']) // 设置搜索框
            ->addColumns([ // 批量添加数据列
                ['name', '名称'],
                ['description', '描述'],
                ['plugin', '所属插件', 'callback', function($plugin){
                    return $plugin == '' ? '系统' : $plugin;
                }],
                ['system', '系统钩子', 'yesno'],
                ['status', '状态', 'switch'],
                ['right_button', '操作', 'btn']
            ])
            ->addOrder('name,status')
            ->addTopButtons('add,enable,disable,delete') // 批量添加顶部按钮
            ->addRightButtons('edit,delete') // 批量添加右侧按钮
            ->setRowList($data_list) // 设置表格数据
            ->setPages($page) // 设置分页数据
            ->fetch(); // 渲染模板
    }

    /**
     * 新增
     * @author 无名氏
     */
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();
            //$data['system'] = 1;
            // 验证
            $result = $this->validate($data, 'Hook');
            if(true !== $result) $this->error($result);
            $data['plugin'] = implode(',',$data['plugin']);
            if ($hook = HookModel::create($data)) {
                cache('hook_plugins', null);
                // 记录行为
                action_log('hook_add', 'admin_hook', $hook['id'], UID, $data['name']);
                $dataadd['hook']=$data['name'];
                $dataadd['plugin']=$data['plugin'];
                $datainfo = HookPluginModel::where(array('hook'=>$data['name'],'plugin'=>$data['plugin']))->find();
                if(!$datainfo){
                    HookPluginModel::create($dataadd);
                }

                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
            }
        }
        $datalook = Db::name('admin_plugin')->column('name,title');
        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->setPageTitle('新增')
            ->addText('name', '钩子名称', '由字母和下划线组成，如：<code>page_tips</code>')
            ->addTextarea('description', '钩子描述')
            ->addRadio('system', '是否的系统钩子','',['不是','是'],0)
            //->addSelect('plugin','插件来源','',$datalook)
            ->addCheckbox('plugin','插件来源','',$datalook)
            ->setTrigger('system', '0', 'plugin')
            ->fetch();
    }

    /**
     * 编辑
     * @param int $id 钩子id
     * @author 无名氏
     */
    public function edit($id = 0)
    {
        if ($id === 0) $this->error('参数错误');

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'Hook');
            if(true !== $result) $this->error($result);
            $data['plugin'] = implode(',',$data['plugin']);
            if ($hook = HookModel::update($data)) {
                // 调整插件顺序
                if ($data['sort'] != '') {
                    HookPluginModel::sort($data['name'], $data['sort']);
                }
                $dataadd['hook']=$data['name'];
                $dataadd['plugin']=$data['plugin'];
                $datainfo = HookPluginModel::where(array('hook'=>$data['name'],'plugin'=>$data['plugin']))->find();
                if(!$datainfo){
                    HookPluginModel::where(array('hook'=>$data['name'],'plugin'=>$data['plugin']))->Update($dataadd);
                }
                cache('hook_plugins', null);
                // 记录行为
                action_log('hook_edit', 'admin_hook', $hook['id'], UID, $data['name']);
                $this->success('编辑成功', 'index');
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        $info = HookModel::get($id);

        // 该钩子的所有插件
        $hooks = HookPluginModel::where('hook', $info['name'])->order('sort')->column('plugin');
        if($hooks){
            $hooks = explode(',',$hooks[0]);
            $hooks = parse_array($hooks);
        }
        $datalook = Db::name('admin_plugin')->column('name,title');
       // dump($datalook);die;
        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->setPageTitle('编辑')
            ->addHidden('id')
            ->addText('name', '钩子名称', '由字母和下划线组成，如：<code>page_tips</code>')
            ->addText('description', '钩子描述')
            ->addCheckbox('plugin','插件来源','',$datalook)
            ->addSort('sort', '插件排序', '', $hooks)
            ->setFormData($info)
            ->fetch();
    }

    /**
     * 快速编辑（启用/禁用）
     * @param string $status 状态
     * @author 无名氏
     * @return mixed
     */
    public function quickEdit($status = '')
    {
        $id        = $this->request->post('pk');
        $status    = $this->request->param('value');
        $hook_name = HookModel::where('id', $id)->value('name');

        if (false === HookPluginModel::where('hook', $hook_name)->setField('status', $status == 'true' ? 1 : 0)) {
            $this->error('操作失败，请重试');
        }
        cache('hook_plugins', null);
        $details = $status == 'true' ? '启用钩子' : '禁用钩子';
        return parent::quickEdit(['hook_edit', 'admin_hook', $id, UID, $details]);
    }

    /**
     * 启用
     * @param array $record 行为日志内容
     * @author 无名氏
     * @return mixed
     */
    public function enable($record = [])
    {
        return $this->setStatus('enable');
    }

    /**
     * 禁用
     * @param array $record 行为日志内容
     * @author 无名氏
     * @return mixed
     */
    public function disable($record = [])
    {
        return $this->setStatus('disable');
    }

    /**
     * 删除钩子
     * @param array $record 行为日志内容
     * @author 无名氏
     * @return mixed
     */
    public function delete($record = [])
    {
        $ids   = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        $map['id']     = ['in', $ids];
        $map['system'] = 1;
        if (HookModel::where($map)->find()) {
            $this->error('禁止删除系统钩子');
        }
        return $this->setStatus('delete');
    }

    /**
     * 设置状态
     * @param string $type 类型
     * @param array $record 行为日志内容
     * @author 无名氏
     * @return mixed
     */
    public function setStatus($type = '', $record = [])
    {
        $ids = $this->request->param('ids/a');
        foreach ($ids as $id) {
            $hook_name = HookModel::where('id', $id)->value('name');
            if (false === HookPluginModel::where('hook', $hook_name)->setField('status', $type == 'enable' ? 1 : 0)) {
                $this->error('操作失败，请重试');
            }
        }
        cache('hook_plugins', null);
        $hook_delete = is_array($ids) ? '' : $ids;
        $hook_names  = HookModel::where('id', 'in', $ids)->column('name');
        return parent::setStatus($type, ['hook_'.$type, 'admin_hook', $hook_delete, UID, implode('、', $hook_names)]);
    }
}
