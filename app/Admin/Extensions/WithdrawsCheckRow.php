<?php
namespace App\Admin\Extensions;

use Encore\Admin\Admin;

class WithdrawsCheckRow
{
    protected $id;
    protected $status;

    public function __construct($id)
    {
        $this->id = $id;
    }

    protected function script()
    {   
        // $applyConfirm = '提现确认';
        // $refuseConfirm = '驳回提现';

        

        $confirm = trans('admin.confirm');
        $cancel = trans('admin.cancel');


        return <<<SCRIPT

$('.btn-check-row').on('click', function () {
    var applyConfirm = '提现确认';
    var _id = $(this).data('id');
    var status = $(this).data('status');   
    
    // console.log($(this).data('id'));
    // console.log($(this).data('status'));

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
    
    

});

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());
        
            // return "<a class='grid-check-row btn btn-info btn-xs' data-id='{$this->id}' data-status='2'><i class='fa fa-check-circle'></i>  通过</a> <a class='grid-check-row btn btn-warning btn-xs' data-id='{$this->id}' data-status='0'><i class='fa fa-trash'></i>  驳回</a>";
        
            return "<div class='btn-group pull-left ' ><a href='' class='btn btn-sm btn-info btn-check-row' data-id='{$this->id}' data-status='1'><i class='fa fa-send'></i>&nbsp;&nbsp;提现</a></div>";
       
        
    }

    public function __toString()
    {
        return $this->render();
    }
}