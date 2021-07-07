<?php

namespace App\Admin\Controllers;

use App\Models\Package;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Models\Plugin;
use App\Models\PluginParam;
use App\Models\Game;
use App\Models\ServingPlan;
use Dcat\Admin\Admin;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Widgets\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class PackageController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Package(), function (Grid $grid) {

            $grid->model()->with(['game']);
            $grid->column('name')->display(function($value){
                return $value."[".$this->id."]";
            });

            $grid->game('游戏id')->display(function($game){
                return $game ? $game->name."[".$game->id."]" : '';
            });

            $pluginParamOption = Package::pluginParamName();
            $grid->column('plugin_login')->using($pluginParamOption);
            $grid->column('plugin_pay')->using($pluginParamOption);


            $grid->column('plugin_type')->using(PluginParam::$typeConfig);
            $grid->column('petitioner');
            $grid->column('plugin_params')->display('查看')->modal(function ($modal) {
                // 设置弹窗标题
                $modal->title('其它信息');

                // 自定义图标
                $modal->icon('');
                $modal->xl();

                $plugin_params = json_decode($this->plugin_params);
                $html = "<div style='padding:10px 10px 0'>";
                foreach ($plugin_params as $key => $params) {
                    $html .= "<div style='margin:5px;'>";
                    $html .= "<h4>".Plugin::$nameConfig[$key]."</h4>";
                    foreach ($params as $k => $v) {
                        $html .= "<div><span>".$k."：</span><span>".$v."</span></div>";
                    }
                    $html .= "</div>";
                }
                $html .= "</div>";

                return "$html<div style='margin-top:15px;float:right'><a href='".route('dcat.admin.package.load',$this->id)."' target='_blank'>下载参数</a></div>";
            });
            $grid->column('status')->switch();
            $grid->column('mark');
            $grid->column('created_at');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
        });
    }

    public function load(Request $request){
        $data = Package::find($request->package_id)->toArray();
        $fileName = $data["name"]."_".PluginParam::$typeConfig[$data["plugin_type"]]."_info.txt";
        $this->saveInfo($data,$fileName);
        return response()->download(realpath(base_path('public/uploads/').$fileName), $fileName);
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Package(), function (Show $show) {
            $show->field('id');
            $show->field('game_id');
            $show->field('name');
            $show->field('package_name_id');
            $show->field('plugin_login');
            $show->field('plugin_pay');
            $show->field('plugin_type');
            $show->field('adjust_key');
            $show->field('petitioner');
            $show->field('plugin_params');
            $show->field('status');
            $show->field('mark');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $pluginParam = PluginParam::all()->toArray();
        foreach ($pluginParam as $key => $value) {
            $pluginParam[$key]['name_str'] =  Plugin::$nameConfig[$value['name']];
        }
        //dd($pluginParam);
        Admin::script($this->script(json_encode($pluginParam)));

        return Form::make(new Package(), function (Form $form) use ($pluginParam) {
            $form->select('game_id')->options(Game::all()->pluck('name', 'id'))->required();
            $form->text('name')->required();
            $form->text('package_name_id')->required();
            $form->text('mark');
            $form->text('adjust_key')->required();

            $form->select('plugin_type')->options(PluginParam::$typeConfig)->required();

            $plugin_options = [];
            if($form->isEditing()){
                $pluginParamShow = [];
                foreach ($pluginParam as $key => $value) {
                    if($value['type'] == $form->model()->plugin_type){
                        if(in_array('1', $value['plugin_use']) || in_array('2', $value['plugin_use'])){
                            $plugin_options[$value['id']] = $value['name_str'];
                            $pluginParamShow[] = $value['name'];
                        }
                    }
                }
                Admin::script($this->scriptEdit(json_encode($pluginParamShow)));
            }
            $form->select('plugin_login')->when('>',0, function (Form $form) {

                //Admin::script($this->scriptPluginParam());
            })->options($plugin_options)->required()->saving(function ($value) {
                // 转化成json字符串保存到数据库
                return $value;
            });

            $form->select('plugin_pay')->options($plugin_options)->required()->saving(function ($value) {
                // 转化成json字符串保存到数据库
                return $value;
            });


            $pluginParamsEdit = $form->model()->plugin_params;
            if(!empty($pluginParamsEdit)) $pluginParamsEdit = json_decode($pluginParamsEdit,true);
            foreach ($pluginParam as $key => $plugin) {
               $code = $plugin['name'];
               $name = $plugin['name_str'];
               $params = array_column($plugin['params'], 'params_plugin') ;

               $paramsNew = [];
               foreach ($params as $v) {
                   $paramsNew[$v] = $plugin['params']['params_plugin'] ?? '';
                   !isset($pluginParamsEdit[$code][$v]) && $form->isEditing() && $pluginParamsEdit[$code][$v] = '';//编辑时，如果新加入的参数字段。保存里没有，则默认空
               }
               $paramsNew = $pluginParamsEdit[$code] ?? $paramsNew;//编辑没有参数时，全部

               $plugin_use = $plugin['plugin_use'];

               //包里的 插件的使用用途 只有登录和支付
               if(in_array(1,$plugin_use) || in_array(2,$plugin_use)){
                    $form->embeds($code,"<span>".$name."</span>", function ($form) use($paramsNew) {
                        foreach ($paramsNew as $key => $value) {
                            $form->text($key,$key)->value($value);
                        }
                    });
               }
            }

            $form->hidden('status')->default(1);
            $form->hidden('petitioner')->value();
            $form->hidden('plugin_params')->default(null);

            $form->saving(function (Form $form) use ($pluginParam) {
                if(!empty($form->input('package_name_id'))){//通过包名来判断是否为状态更新还是编辑
                    $plugin_params = [];
                    foreach ($pluginParam as $key => $value) {
                        $code = $value['name'];
                        if($params = $form->input($code)){
                            foreach ($params as $k => $v) {
                                if(!empty($v)){
                                    $plugin_params[$code] = $params;
                                    break;
                                }
                            }
                            $form->deleteInput($code);
                        }
                    }
                    $form->plugin_params = json_encode($plugin_params);
                    $form->petitioner = Admin::user()->name;
                }
            });


            $form->saved(function (Form $form) {
                $data = $form->updates();

                if($form->isCreating()){//添加游戏包时自动生成自然量投放计划
                    $data = [
                        'adj_app_name' => $data['package_name_id'],
                        'is_organic' => 1,
                        'package_name' => $data['name'],
                        'package_plugin_type' => $data['plugin_type'],
                    ];

                    ServingPlan::create($data);
                }

                $rd_key = "table_packages";
                if($form->isEditing()){
                    Redis::hDel($rd_key,$form->getKey());
                }

            });

        });
    }

    public function saveInfo($data,$fileName){
        $str = "";
        $str .= "游戏名：".$data["name"]."\r\n\r\n";
        $str .= "插件类型：".PluginParam::$typeConfig[$data["plugin_type"]]."\r\n\r\n";
        $str .= "包名：".$data["package_name_id"]."\r\n\r\n";
        $str .= "Adjust秘钥：".$data["adjust_key"]."\r\n\r\n";
        $str .= "插件参数：\r\n";
        $plugin_params = json_decode($data["plugin_params"],true);
        foreach ($plugin_params as $key => $params) {
            $str .= "【".Plugin::$nameConfig[$key]."】\r\n";

            foreach ($params as $k => $v) {
                $str .= $k."：".$v."\r\n";
            }
            $str .= "\r\n";
        }

        file_put_contents(base_path("public/uploads/".$fileName), $str);
    }

    protected function script($pluginParam){
        $url = route('dcat.admin.api.pluginParam');
        return <<<JS
            var pluginParam = $pluginParam;
            hideAll(pluginParam)
            $('select[name="plugin_type"]').change(function(){
                hideAll(pluginParam)
                var pluginLoginHtml = '';
                var pluginPayHtml = '';

                let plugin_type = $(this).val();
                if(pluginParam.length > 0){
                    for(let i = 0; i < pluginParam.length; i++) {
                        let plugin = pluginParam[i];
                        if(plugin.type == plugin_type){
                            let plugin_use = plugin.plugin_use;
                            if(plugin_use.includes('1')){//包含登录用途的插件
                                pluginLoginHtml += '<option value="'+plugin.id+'">'+plugin.name+'</option>'
                            }

                            if(plugin_use.includes('2')){//包含支付用途的插件
                                pluginPayHtml += '<option value="'+plugin.id+'">'+plugin.name+'</option>'
                            }
                        }
                    }
                }

               $('select[name="plugin_login"]').html(pluginLoginHtml)
               $('select[name="plugin_pay"]').html(pluginPayHtml)

            })


            //判断选择内容
            $('select[name="plugin_login"],select[name="plugin_pay"]').change(function(){
                hideAll(pluginParam)
                let plugin = []
                let pluginLogin = $('select[name="plugin_login"]').val()
                let pluginPay = $('select[name="plugin_pay"]').val();
                
                

                if(pluginLogin.length > 0){
                    for(let i = 0; i < pluginLogin.length; i++) {
                        if(!plugin.includes(pluginLogin[i])){
                            plugin.push(pluginLogin[i])
                        }
                    }
                }

                if(pluginPay.length > 0){
                    for(let i = 0; i < pluginPay.length; i++) {
                        if(!plugin.includes(pluginPay[i])){
                            plugin.push(pluginPay[i])
                        }
                    }
                }

                for(let i = 0; i < plugin.length; i++) {
                      for(let j = 0; j < pluginParam.length; j++) {
                        if(plugin[i] == pluginParam[j].id){
                            let code = pluginParam[j].name
                            $('#embed-'+code).show();
                        }
                      }
                }

            })

            function hideAll(pluginParam){
                if(pluginParam.length > 0){
                    for(let i = 0; i < pluginParam.length; i++) {
                        let code = pluginParam[i].name
                        $('#embed-'+code).hide();
                    }
                }
            }

JS;
    }

    protected function scriptPluginParam(){
        return <<<JS
        $('.form-control.field_plugin_login').on('change',function(e){
            console.log($(this).val());
        });
JS;
    }
    protected function scriptEdit($pluginParamShow){
        return <<<JS
        var pluginParamShow = $pluginParamShow;
        if(pluginParamShow.length > 0){
            for(let i = 0; i < pluginParamShow.length; i++) {
                $('#embed-'+pluginParamShow[i]).show();
            }
        }
JS;
    }

    public function pluginParam(Request $request){
        return PluginParam::packageParam();
    }
}
