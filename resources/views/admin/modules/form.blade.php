<div class="modal fade common" id="modal_form" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="width:640px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                <h4 class="modal-title">请输入模块信息</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-info">
                            <form id="form" action="/admin/modules" method="post" class="form-horizontal">
                                {{ csrf_field() }}
                                <input id="method" name="_method" type="hidden" value="POST">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">表名:</label>
                                        <div class="col-sm-4">
                                            <input id="name" name="name" class="form-control" placeholder="">
                                        </div>
                                        <label class="col-sm-2 control-label">模块名称:</label>
                                        <div class="col-sm-4">
                                            <input id="title" name="title" class="form-control" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">模型类:</label>
                                        <div class="col-sm-10">
                                            <input id="model_class" name="model_class" class="form-control" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">控制器类:</label>
                                        <div class="col-sm-10">
                                            <input id="controller_class" name="controller_class" class="form-control" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">视图路径:</label>
                                        <div class="col-sm-10">
                                            <input id="view_path" name="view_path" class="form-control" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button class="btn btn-default" data-dismiss="modal">取消</button>
                                    <button type="submit" class="btn btn-info pull-right">提交</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
