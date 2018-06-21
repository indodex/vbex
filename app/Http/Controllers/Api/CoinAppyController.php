<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiController as Controller;

use App\Models\CoinApplyModel;

class CoinAppyController extends Controller
{

    public function __construct() 
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return $this->response([
                'code' => 400,
                'message' => '验证失败',
                'data' => $errors
            ]);
        }

        $params = $request->all('email', 'budget', 'phone', 'position', 'welfare', 'coinName', 'coinCode', 'coinUrl', 'issueTime', 'issueTotal', 'jetton', 'coinType', 'icoPrice', 'icoRecord', 'purpose', 'userNumber', 'issueCountry', 'bourse', 'team', 'address', 'paperUrl', 'codeUrl');

        $insertData = [
            'uid' => $this->uid,
            'email' => $params['email'],
            'weixin' => $params['budget'],
            'phone' => $params['phone'],
            'position' => $params['position'],
            'welfare' => $params['welfare'],
            'name' => $params['coinName'],
            'code' => $params['coinCode'],
            'coin_url' => $params['coinUrl'],
            'issue_time' => $params['issueTime'],
            'issue_total' => $params['issueTotal'],
            'jetton' => $params['jetton'],
            'coin_type' => $params['coinType'],
            'ico_price' => $params['icoPrice'],
            'ico_record' => $params['icoRecord'],
            'purpose' => $params['purpose'],
            'user_number' => $params['userNumber'],
            'issue_country' => $params['issueCountry'],
            'bourse' => $params['bourse'],
            'team' => $params['team'],
            'address' => $params['address'],
            'paper_url' => $params['paperUrl'],
            'code_url' => $params['codeUrl'],
        ];

        $coinModel = new CoinApplyModel();
        $id = $coinModel->createApply($insertData);
        if($id > 0) {
            return $this->responseSuccess(['id' => $id]);
        } else {
            return $this->setStatusCode(400)->responseError('申请失败');
        }
    }

    public function show(Request $request)
    {
        $id = $request->input('id');

        if(empty($id)) {
            return $this->setStatusCode(400)->responseNotFound('缺少参数');
        }

        $data = CoinApplyModel::find($id);
        if(empty($data)) {
            return $this->setStatusCode(400)->responseNotFound('查无数据');
        }



        $fields = [
            'email' => '电子邮件地址',
            'budget' => '微信号',
            'phone' => '联系电话',
            'position' => '联系人姓名与职称',
            'welfare' => '项目名称',
            'coin_name' => '数字货币名称',
            'coin_code' => '数字货币编码',
            'coin_url' => '项目网址',
            'issue_time' => '发行时间',
            'issue_total' => '发行总量和发行规则',
            'jetton' => '筹码分布',
            'coin_type' => '数字货币类型',
            'ico_price' => '成本价',
            'ico_record' => '众筹记录',
            'purpose' => '用途',
            'user_number' => '社区用户量',
            'issue_country' => '发行国家',
            'bourse' => '已上线交易所',
            'team' => '团队',
            'address' => '团队办公地点',
            'paper_url' => '白皮书链接',
            'code_url' => '代码开源链接',
            'remark' => '备注',
        ];
        $data = $data->toArray();
        $result = [];
        foreach ($data as $key => $value) {
            if(isset($fields[$key])) {
                $result[$key] = $fields[$key] . '：' . $value;
            }
        }

        return $this->responseSuccess($result);
    }



    public function lists(Request $request)
    {
        $uid = (int) $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound('请登录');
        }

        $data = CoinApplyModel::where(['uid' => $uid])->get();
        if(empty($data)) {
            return $this->setStatusCode(400)->responseNotFound('查无数据');
        }

        return $this->responseSuccess($data->toArray());
    }



    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => 'required',
            'budget' => 'required',
            'phone' => 'required',
            'position' => 'required',
            'welfare' => 'required',
            'coinName' => 'required',
            'coinCode' => 'required',
            'coinUrl' => 'required',
            'issueTime' => 'required',
            'issueTotal' => 'required',
            'jetton' => 'required',
            'coinType' => 'required',
            'icoPrice' => 'required',
            'icoRecord' => 'required',
            'purpose' => 'required',
            'userNumber' => 'required',
            'issueCountry' => 'required',
            'bourse' => 'required',
            'team' => 'required',
            'address' => 'required',
            'paperUrl' => 'required',
            'codeUrl' => 'required',
        ],
        [
            'required' => __('validation.required'),
        ],
        [
            'email' => '电子邮件地址',
            'budget' => '微信号',
            'phone' => '联系电话',
            'position' => '联系人姓名与职称',
            'welfare' => '项目名称',
            'coinName' => '数字货币名称',
            'coinCode' => '数字货币编码',
            'coinUrl' => '项目网址',
            'issueTime' => '发行时间',
            'issueTotal' => '发行总量和发行规则',
            'jetton' => '筹码分布',
            'coinType' => '数字货币类型',
            'icoPrice' => '成本价',
            'icoRecord' => '众筹记录',
            'purpose' => '用途',
            'userNumber' => '社区用户量',
            'issueCountry' => '发行国家',
            'bourse' => '已上线交易所',
            'team' => '团队',
            'address' => '团队办公地点',
            'paperUrl' => '白皮书链接',
            'codeUrl' => '代码开源链接',
        ]);
    }
}
