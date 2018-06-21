<div class="btn-group pull-left">
    <a href="" class="btn btn-sm btn-default" data-toggle="modal" data-target="#show-modal-{{ $row->id }}" data-id="-{{ $row->id }}"><i class="fa fa-send"></i>&nbsp;&nbsp;{{ trans('admin.view') }}</a>
</div>

<div class="modal fade" id="show-modal-{{ $row->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">{{ trans('admin.operation_log') }}</h4>
            </div>
            
                <input type="hidden" id="apply-{$row->id}" name="id" value="{{ $row->id }}" />
                <div class="modal-body">
                    <div class="form">
                        <div class="input-group col-sm-12">
                            {{ $row->remark }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-close" data-id="{{ $row->id }}">{{ trans('admin.close') }}</button>
                </div>
        </div>
    </div>
</div>