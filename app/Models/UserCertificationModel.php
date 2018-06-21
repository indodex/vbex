<?php

namespace App\Models;
use App\Models\BaseModel as Model;
use App\Models\UserModel;
use App\Models\AttachmentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserCertificationModel extends Model
{
	protected $table = 'user_certification';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'name', 'birthday', 'papers_type', 'papers_number', 'papers_before','papers_after','sex','profession','address','advanced','remark','status','advanced_status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'uid');
    }

    public function before() : HasOne
    {
        return $this->hasOne(AttachmentModel::class, 'id');
    }

    public function after() : HasOne
    {
        return $this->hasOne(AttachmentModel::class, 'id');
    }

    public function advanced() : HasOne
    {
        return $this->hasOne(AttachmentModel::class, 'id');
    }

    public function add($data)
    {
    	return $this->insertGetId([
				'uid'             => (int)$data['uid'],
				'name'            => (string)$data['name'],
				'birthday'        => (string)$data['birthday'],
				'papers_type'     => (int)$data['papers_type'],
				'papers_number'   => (int)$data['papers_number'],
				'papers_before'   => (int)$data['papers_before'],
				'papers_after'    => (int)$data['papers_after'],
				'sex'             => (int)$data['sex'],
				'profession'      => (string)$data['profession'],
				'address'         => (string)$data['address'],
				'advanced'        => (string)$data['advanced'],
				'remark'          => (string)$data['remark'],
				'status'          => (string)$data['status'],
				'advanced_status' => (string)$data['advanced_status'],
            ]);
    }

    public function updateRow($data, $cond)
    {	
    	if (empty($cond) || empty($data)) {
    		return false;
    	}

    	$saveData['name']            = (string)$data['name'];
        $saveData['birthday']        = (string)$data['birthday'];
        $saveData['papers_type']     = (int)$data['papers_type'];
        $saveData['papers_number']   = (string)$data['papers_number'];
        $saveData['papers_before']   = (int)$data['papers_before'];
        $saveData['papers_after']    = (int)$data['papers_after'];
        $saveData['sex']             = (int)$data['sex'];
        $saveData['address']         = (string)$data['address'];
        $saveData['profession']      = (string)$data['profession'];
        $saveData['advanced']        = (int)$data['advanced'];
        $saveData['remark']          = (string)$data['remark'];
        if (isset($data['status'])) {
        	$saveData['status']          = (int)$data['status'];
        }
        if (isset($data['advanced_status'])) {
        	$saveData['advanced_status'] = (int)$data['advanced_status'];
        }
        
        return $this->where($cond)->update($saveData);
    }

    public static function toCertification ($id,$data){
    	if (empty($id) || empty($data)) {
    		return false;
    	}

    	$cond['id'] = (int)$id;
    	if ($data['status']) {
    		$saveData['status'] = (int)$data['status'];
    	}

    	if ($data['advanced_status']) {
    		$saveData['advanced_status'] = (int)$data['advanced_status'];
    	}
    	$saveData['remark'] = (string)$data['remark'];

    	return self::where($cond)->update($saveData);

    }

    public function getInfoByUid($uid)
    {
        if (empty($uid)) {
            return null;
        }

        $cond['uid'] = (int)$uid;

        return self::where($cond)->first();
    }

}