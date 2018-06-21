<?php

namespace App\Services;

use App\Services\BaseService;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;

use App\Models\AttachmentModel;

class UploadFlie extends BaseService
{
	public function upload(Request $request,$uid,$name) 
	{
		$file = $request->file($name);
        $ext = $file->getClientOriginalExtension();

        // 原图
        $fileName = $name.date('YmdHis').'_'.rand(1000,9999).'.' . $ext;
        $filePath = "uploads/avatar/" .$uid;
        $path = $file->move($filePath, $fileName);

        $id = AttachmentModel::addAttachment([
                'uid' => $uid,
                'author' => 'test',//$this->getUser()->name,
                'filename' => $fileName,
                'filesize' => $file->getClientSize(),
                'fileext' => $ext,
                'filemd5' => '',
                'filepath'=> $filePath.'/'.$fileName,
            ]);

        if ($id) {
        	return ['id'=>$id,'path'=>$filePath.'/'.$fileName];
        }
        return false;
	}
}