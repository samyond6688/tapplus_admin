<?php

namespace App\Admin\Controllers;

use App\Models\MediumAccount;
use App\Models\Partner;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Models\Administrator;
use App\Models\Medium;
use Dcat\Admin\Widgets\Card;

class MediumAccountController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new MediumAccount(), function (Grid $grid) {
            $grid->model()->with(['medium']);
            $grid->column('tracker');
            $grid->column('medium.name');
            $grid->column('type')->using(MediumAccount::$typeConfig);


            $grid->column('account');
            $grid->column('password')->display('查看') // 设置按钮名称
            ->modal(function ($modal) {
                // 设置弹窗标题
                $modal->title('账号密码');

                // 自定义图标
                $modal->icon('');

                $card = new Card(null, $this->password);

                return "<div style='padding:10px 10px 0'>$card</div>";
            });

            $grid->column('account_id');
            $grid->column('account_name');

            $grid->column('agent_id');
            $grid->column('company_id')->using(MediumAccount::$companyConfig);
            $grid->column('owner_id')->display(function($value){
                return Administrator::find($value)->name;
            });
            $grid->column('created_at');
            $grid->column('mark');
            $grid->column('status')->switch();

            $grid->disableFilterButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->expand();
                $filter->equal('account')->width(3);
                //$filter->equal('account_name')->width(3);
                $filter->equal('type')->select(MediumAccount::$typeConfig)->width(3);
                $filter->equal('agent_id')->select(MediumAccount::$agentConfig)->width(3);
                $filter->equal('owner_id')->select(Administrator::all()->pluck('name', 'id'))->width(3);
                $filter->equal('company_id')->select(MediumAccount::$companyConfig)->width(3);
                $filter->equal('medium_id')->select(Medium::all()->pluck('name', 'id'))->width(3);
            });
        });
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
        return Show::make($id, new MediumAccount(), function (Show $show) {
            $show->field('id');
            $show->field('medium_id');
            $show->field('type');
            $show->field('account');
            $show->field('password');
            $show->field('account_id');
            $show->field('account_name');
            $show->field('agent_id');
            $show->field('company_id');
            $show->field('owner');
            $show->field('status');
            $show->field('mark');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $class = $this;
        return Form::make(new MediumAccount(), function (Form $form) use ($class) {

            $form->select('medium_id')->options(Medium::all()->pluck('name', 'id'))->required();
            $form->select('type')->options(MediumAccount::$typeConfig)->required();
            $form->text('account')->required();
            $form->text('password')->required();
            $form->text('account_id');

            $Partner = new Partner();
            $config = $Partner->allConfig();
            $form->select('account_name')->options(array_combine($config,$config))->required();
            if($form->isCreating()){
                $tracker = $class->getTracker();
                $form->text('tracker')->value($tracker);
            }

            if($form->isEditing()){
                $form->text('tracker')->disable();
            }

            $agentConfig = $Partner->agentConfig();
            $form->select('agent_id')->options(array_combine($agentConfig,$agentConfig))->required();
            $form->select('company_id')->options(MediumAccount::$companyConfig)->required();
            $form->select('owner_id')->options(Administrator::all()->pluck('name', 'id'))->required();
            $form->hidden('status')->default(1);
            $form->text('mark');

        });
    }

    protected function getTracker(){
        for ($i=0; $i < 20; $i++){
            $tracker = getRandomString(3);
            if(!MediumAccount::where('tracker',$tracker)->first()){
                return $tracker;
                break;
            }
            break;
        }
    }
}
