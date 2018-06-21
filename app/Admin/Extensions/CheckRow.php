<?php
namespace App\Admin\Extensions;

use Encore\Admin\Admin;

class CheckRow
{
    protected $id;
    protected $status;

    public function __construct($id, $status)
    {
        $this->id = $id;
        $this->status = $status;
    }

    protected function script()
    {   
        // $applyConfirm = '提现确认';
        // $refuseConfirm = '驳回提现';

        

        $confirm = trans('admin.confirm');
        $cancel = trans('admin.cancel');


        return <<<SCRIPT

$('.grid-check-row').on('click', function () {
    var applyConfirm;
    var _id = $(this).data('id');
    var status = $(this).data('status');
    
    if (status == 2) {
        applyConfirm = '审核确认';
    }

    if (status == 0) {
        applyConfirm = '驳回确认';
    }

    if (status == 1) {
        applyConfirm = '提现确认';
    }

    if (status == -2) {
        applyConfirm = '退回确认';
    }

    swal({
      title: applyConfirm,
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "$confirm",
      closeOnConfirm: false,
      cancelButtonText: "$cancel"
    },
    function(){
        $.ajax({
            method: 'post',
            url: '/admin/withdraws/currency',
            data: {id:_id,status:status},
            success: function (data) {
                $.pjax.reload('#pjax-container');

                if (typeof data === 'object') {
                    if (data.status) {
                        swal(data.message, '', 'success');
                    } else {
                        swal(data.message, '', 'error');
                    }
                }
                $("#apply-modal" + _id).modal('toggle');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            }
        });
    })
    // Your code.
    // console.log($(this).data('id'));
    // console.log($(this).data('status'));
    

});

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());
        if ($this->status == 3) {
            return "<a class='grid-check-row btn btn-info btn-xs' data-id='{$this->id}' data-status='2'><i class='fa fa-check-circle'></i>  通过</a> <a class='grid-check-row btn btn-warning btn-xs' data-id='{$this->id}' data-status='0'><i class='fa fa-trash'></i>  驳回</a>";
        }

        if ($this->status == 2) {
            return "<a class='grid-check-row btn btn-success btn-xs' data-id='{$this->id}' data-status='1'><i class='fa fa-check-circle'></i>  提现</a>";
        }

        if ($this->status == -1) {
            return "<a class='grid-check-row btn btn-danger btn-xs' data-id='{$this->id}' data-status='-2'><i class='fa fa-check-circle'></i>  退回</a>";
        }
        
    }

    public function __toString()
    {
        return $this->render();
    }
}