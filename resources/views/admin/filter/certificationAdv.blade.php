<div class="btn-group pull-left">
    <a href="" class="btn btn-sm btn-info" data-toggle="modal" data-target="#apply-modal-{{ $row->id }}" data-id="-{{ $row->id }}"><i class="fa fa-send"></i>&nbsp;&nbsp;{{ trans('api.certification.advanced_cer') }}</a>
</div>

<div class="modal fade" id="apply-modal-{{ $row->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">{{ trans('api.certification.advanced_cer') }}</h4>
            </div>
            <form action="javascript:;" method="post" id="pjax-container-{{ $row->id }}" pjax-container>
                <input type="hidden" id="apply-{$row->id}" name="id" value="{{ $row->id }}" />
                <div class="modal-body">
                    <div class="form">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>手持证件照：</th>
                                    <th><img src="{{ env('APP_URL').'/'.$advanced->filepath}}" style="width:200px;" alt=""></th>
                                </tr>
                            </tbody>
                        </table>

                        <div class="input-group col-sm-12">
                            <textarea type="text" rows="4" class="form-control" placeholder="备注" name="remark">{{ $row->remark }}</textarea>
                        </div>
                        <div class="input-group">
                            <div class="radio">
                                <label class="radio-inline">
                                    <input type="radio" name="advanced_status" value="1" class="minimal" />&nbsp;{{ trans('api.withdraw.withdraws_agree') }}&nbsp;&nbsp;
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="advanced_status" value="-1" class="minimal" />&nbsp;{{ trans('api.withdraw.withdraws_refuse') }}&nbsp;&nbsp;
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary submit btn-apply-submit" data-id="{{ $row->id }}">{{ trans('admin.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>