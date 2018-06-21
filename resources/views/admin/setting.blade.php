<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">设置</h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">

        <form method="post" action="{{$action}}" id="scaffold" >

            <div class="box-body">

                <div class="form-horizontal">

                <div class="form-group">

                    <label for="inputClientEmail" class="col-sm-1 control-label">客户邮箱</label>

                    <div class="col-sm-4">
                        <input type="text" name="setting[client_email]" class="form-control"  placeholder="客服邮箱" value="{{ $setting['client_email'] }}">
                    </div>

                    <span class="help-block hide" id="table-name-help">
                        <i class="fa fa-info"></i>&nbsp; Table name can't be empty!
                    </span>

                </div>
                <div class="form-group">
                    <label for="inputCooperationName" class="col-sm-1 control-label">商务合作</label>

                    <div class="col-sm-4">
                        <input type="text" name="setting[cooperation_email]" class="form-control"  placeholder="商务合作邮箱" value="{{ $setting['cooperation_email'] }}">
                        
                    </div>

                </div>
 
                <div class="form-group">
                    <label for="inputControllerName" class="col-sm-1 control-label">赠送比率</label>

                    <div class="col-sm-4">
                        <!-- <span style="width: 50px;">1 ：</span> -->
                        <input type="text" name="setting[gift_ratio]" class="form-control"  placeholder="赠送比率" value="{{ $setting['gift_ratio'] }}">
                    </div>
                    <span class="help-block" id="table-name-help">
                        <i class="fa fa-info"></i>&nbsp; 邀请充值用户赠送比率 1: ? 只需要填写后面无需填写1：! 
                    </span>
                </div>

<!--                 <div class="form-group">
                    <div class="col-sm-offset-1 col-sm-11">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" checked value="migration" name="create[]" /> 开启注册
                            </label>
                            <label>
                                <input type="checkbox" checked value="model" name="create[]" /> Create model
                            </label>
                            <label>
                                <input type="checkbox" checked value="controller" name="create[]" /> Create controller
                            </label>
                            <label>
                                <input type="checkbox" checked value="migrate" name="create[]" /> Run migrate
                            </label>
                        </div>
                    </div>
                </div> -->

                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-info pull-right">submit</button>
            </div>

            {{ csrf_field() }}

            <!-- /.box-footer -->
        </form>

    </div>

</div>




</script>