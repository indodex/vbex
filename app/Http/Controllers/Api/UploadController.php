<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Controllers\ApiController as Controller;
use App\Services\UploadFlie;

class UploadController extends Controller
{
	public function __construct()
    {
        parent::__construct();
    }

    public function uploadIamge(Request $request)
    {
    	$token = $request->input('api_token', null);
        $uid = $this->uid;
        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('public.please_login'));
        }
        $name = $request->input('name');
        $file = $request->file($name);
        $fileSize = $file->getClientSize();
        $maxSize = 2 * 1024 * 1024;

        if($fileSize > $maxSize) {
            return $this->setStatusCode(400)->responseNotFound(__('api.upload.too_big'));
        }

        $fileType = $file->getClientMimeType();
        $type = explode('/', $fileType);
        if(!in_array($type[1], array('jpg', 'jpeg', 'png', 'gif', 'pdf'))) {
            return $this->setStatusCode(400)->responseNotFound(__('api.upload.format_error'));
        }

        $uploadService = new UploadFlie();
        $response = $uploadService->upload($request, $uid, $name);
        if ($response) {
        	return $this->responseSuccess($response, __('api.upload.success'));
        }

        return $this->setStatusCode(400)->responseNotFound(__('api.upload.fail'));
    }
}