<?php
namespace App\Admin\Extensions;

use Encore\Admin\Admin;

use Encore\Admin\Grid\Filter\AbstractFilter;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;

class RechargeApply
{
    protected $data;

    /**
     * @var string
     */
    protected $view = 'admin::filter.rechargeApply';

    public function __construct($data)
    {
        $this->data = $data;
    }

    protected function script()
    {
        $applyConfirm = '审核确认';
        $confirm = trans('admin.confirm');
        $cancel = trans('admin.cancel');

        $script = <<<SCRIPT

$(".btn-apply-submit").click(function () {

    var _id = $(this).data('id'),
        _postData = $('#pjax-container-' + _id).serialize();
        _postData += '&_method=PUT&_token='+LA.token;

    swal({
      title: "$applyConfirm",
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
            url: '/admin/recharge/apply',
            data: _postData,
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
    });
});

SCRIPT;
        return $script;
    }

    protected function render()
    {
        Admin::script($this->script());

        return view('admin::filter.rechargeApply', [
            'action' => $this->urlWithoutFilters(),
            'row' => $this->data
        ])->render();
    }

    /**
     * Get url without filter queryString.
     *
     * @return string
     */
    protected function urlWithoutFilters()
    {

        /** @var \Illuminate\Http\Request $request * */
        $request = Request::instance();

        $query = $request->query();

        $question = $request->getBaseUrl().$request->getPathInfo() == '/' ? '/?' : '?';

        return count($request->query()) > 0
            ? $request->url().$question.http_build_query($query)
            : $request->fullUrl();
    }

    public function __toString()
    {
        return $this->render();
    }
}