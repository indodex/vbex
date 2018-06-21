<?php
namespace App\Admin\Extensions;

use Encore\Admin\Admin;

use Encore\Admin\Grid\Filter\AbstractFilter;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;

class WithdrawsShow
{
    protected $data;

    /**
     * @var string
     */
    protected $view = 'admin::filter.withdrawsShow';

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

$(".btn-close").click(function () {
    $("#show-modal" + _id).modal('toggle');
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
});

SCRIPT;
        return $script;
    }

    protected function render()
    {
        Admin::script($this->script());

        if ($this->data->status == 2) {
            $this->view = 'admin::filter.withdrawsAction';
        }

        return view($this->view, [
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