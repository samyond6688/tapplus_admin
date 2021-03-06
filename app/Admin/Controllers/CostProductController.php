<?php

namespace App\Admin\Controllers;

use App\Admin\Action\EditCostProduct;
use App\Admin\Tools\CopyCostProduct;
use App\Models\CostProduct;
use App\Models\Cate;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Modal;

class CostProductController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new CostProduct(), function (Grid $grid) {
            //$grid->column('id')->sortable()->hide();
            $grid->model()->with(['cate']);
            $grid->setActionClass(Grid\Displayers\Actions::class);//设置操作类
            $grid->simplePaginate();
            $grid->disableViewButton();
            $grid->disableCreateButton();
            $grid->disableEditButton();
            $grid->disableQuickEditButton(false);
            $grid->column('product_id');
            $grid->column('cate.id')->using(Cate::all()->pluck('name','id')->toArray());
            $grid->column('amount');
            $grid->column('status')->switch();
            $grid->column('created_at');
            //$grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('product_id');
            });
            //快速创建
            $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
                $create->text('product_id');
                $create->text('amount');
            });
            $self = $this;
            $grid->actions(function (Grid\Displayers\Actions $actions) use ($self) {
                $rowArray = $actions->row;
                $CopyCostProduct = new CopyCostProduct(CostProduct::class);
                $CopyCostProduct->setName(admin_trans_field('product_id').':'.$rowArray->product_id);
                $CopyCostProduct->setTitle(admin_trans('admin.copy'));
                //$actions->append(new CostProduct);

                $actions->append($CopyCostProduct);

                //$actions->append($self->buildCostProduct($rowArray));
            });
        });
    }

    public function buildCostProduct($rowArray)
    {
        Form::dialog('编辑计费点')
            ->click('.edit-form')
            ->success('Dcat.reload()'); // 编辑成功后刷新页面
        // 当需要在同个“class”的按钮中绑定不同的链接时，把链接放到按钮的“data-url”属性中即可
        $editPage = admin_base_path('cates/2/edit');
        return " <span class='btn btn-primary edit-form' data-url='{$editPage}'> &nbsp;计费点 </span>
</div>
";
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
        return Show::make($id, new CostProduct(), function (Show $show) {
            $show->field('id');
            $show->field('amount');
            $show->field('product_id');
            $show->field('status');
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
        return Form::make(new CostProduct(), function (Form $form) {
            $form->disableViewButton();
            $form->display('id')->disable();
            $form->text('amount')->rules('min:0');
            $form->text('product_id')->rules('required|unique:cost_products');
            $form->hidden('status');
            //$form->display('created_at');
            //$form->display('updated_at');
        });
    }
}
