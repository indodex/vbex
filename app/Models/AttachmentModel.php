<?php

namespace App\Models;
use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttachmentModel extends Model
{
	protected $table = 'attachment';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'author', 'related', 'tableid', 'download', 'filesize','fileext','filemd5','filepath'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public static function addAttachment($data)
    {
    	return self::insertGetId([
					'uid'      => (int) $data['uid'],
					'author'   => (string)$data['author'],
					// 'filename' => (string)$data['filename'],
					'filesize' => (int)$data['filesize'],
					'fileext'  => (string)$data['fileext'],
					'filepath' => (string)$data['filepath'],
				]);
    }

    public static function getRow($id)
    {	
    	$data = self::find($id); 
    	if (empty($data)) {
    		return null;
    	}
    	return $data->toArray();
    }
}