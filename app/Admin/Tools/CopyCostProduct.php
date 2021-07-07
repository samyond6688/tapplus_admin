<?php

namespace App\Admin\Tools;

use Dcat\Admin\Grid\RowAction;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class CopyCostProduct extends RowAction
{
    protected $model;
    protected $name;
    public function __construct(string $model = null)
    {
        $this->model = $model;
    }

    /**
     * 标题
     *
     * @return string
     */
    public function title()
    {
        return $this->title ?:'Copy';
    }
    public function setTitle($title)
    {
        $this->title = $title;
    }
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * 设置确认弹窗信息，如果返回空值，则不会弹出弹窗
     *
     * 允许返回字符串或数组类型
     *
     * @return array|string|void
     */
    public function confirm()
    {
        return [
            // 确认弹窗 title
            "您确定要复制这行数据吗？",
            // 确认弹窗 content
            $this->name,
        ];
    }

    /**
     * 处理请求
     *
     * @param Request $request
     *
     * @return \Dcat\Admin\Actions\Response
     */
    public function handle(Request $request)
    {
        // 获取当前行ID
        $id = $this->getKey();

        // 获取 parameters 方法传递的参数
        $name = $request->get('name');
        $model = $request->get('model');

        // 复制数据
        $model::find($id)->replicate()
            ->fill(['product_id' => $model::max('product_id')+1])
            ->save();

        // 返回响应结果并刷新页面
        return $this->response()->success("复制成功: [{$name}]")->refresh();
    }

    /**
     * 设置要POST到接口的数据
     *
     * @return array
     */
    public function parameters()
    {
        return [
            // 发送当前行 username 字段数据到接口
            'name' => $this->row->name,
            // 把模型类名传递到接口
            'model' => $this->model,
        ];
    }
}