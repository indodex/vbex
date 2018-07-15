<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\NewsModel;
use App\Models\CategoriesModel;
use App;

use App\Http\Controllers\ApiController as Controller;

class NewsController extends Controller
{
	public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
    	$cond['status'] = 'ACTIVE';
    	// if (!empty( $request->input('category',null) ) {
    	// 	$cond['category_id'] = $request->input('category_id',null);
    	// }
    	// 
    	if (!empty($request->input('category_id',null))) {
    		$cond['category_id'] = $request->input('category_id',null);
    	} else {
            $cond = array();
            $cond[] = ['status','=','ACTIVE'];
            $cond[] = ['category_id','>',0];
    	}
    	$page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
    	$result = $this->getNewsModel()->getList($cond, $page, $limit);
    	//dd($result);
    	$list = [];
    	foreach ($result->items() as $key => $val) {
    		$row['id']		   = $val->id;
			$row['title']      = $val->title;
			//$row['author']     = $val->user->name;
			$row['excerpt']    = $val->excerpt;
			// $row['category']   = $val->category->name;
			$row['image']      = $val->image;
			$row['created_at'] = $val->created_at;

    		$list[] = $row;
    	}

    	$result = $result->toArray();

    	$paginate['currentPage'] = $result['current_page'];
        $paginate['lastPage'] = $result['last_page'];
        $paginate['perPage'] = $result['per_page'];
        $paginate['total'] = $result['total'];

        $data['list'] = $list;
        $data['paginate'] = $paginate;

    	return $this->setStatusCode(200)
                        ->responseSuccess($data, 'success');
    }


    public function buttomMenu(Request $request){
        $cond['status'] = 'ACTIVE';
        $cond['category_id'] = 5;
        $result = $this->getNewsModel()->getList($cond);
        //dd($result);
        $list = [];
        foreach ($result->items() as $key => $val) {
            $row['id']         = $val->id;
            $row['title']      = $val->title;
            //$row['author']     = $val->user->name;
            $row['excerpt']    = $val->excerpt;
            // $row['category']   = $val->category->name;
            $row['image']      = $val->image;
            $row['created_at'] = $val->created_at;

            $list[] = $row;
        }

        $result = $result->toArray();

        $paginate['currentPage'] = $result['current_page'];
        $paginate['lastPage'] = $result['last_page'];
        $paginate['perPage'] = $result['per_page'];
        $paginate['total'] = $result['total'];

        $data['list'] = $list;
        $data['paginate'] = $paginate;

        return $this->setStatusCode(200)
                        ->responseSuccess($data, 'success');
    }



    public function content(Request $request)
    {	
    	$id = (int)$request->input('id',0);
    	if (!$id) {
    		return $this->setStatusCode(400)->responseNotFound(__('api.public.illegal_operation'));
		}
		$result = $this->getNewsModel()->getRow($id);

		if ($result) {

			$data = $result->toArray();
			$data['year'] = $result->created_at->year.'-'.$result->created_at->month;
			$data['day'] = $result->created_at->day;
			$data['time'] = $result->created_at->toTimeString();

			return $this->setStatusCode(200)
                        ->responseSuccess($data, 'success');
		}
		return $this->setStatusCode(400)->responseNotFound(__('api.public.empty_data'));

    }

    public function aboutUs(Request $request){
        return $this->menuContent(1);
    }

    public function terms(Request $request){
        return $this->menuContent(2);
    }

    public function privacy(Request $request){
        return $this->menuContent(3);
    }

    public function fees(Request $request){
        return $this->menuContent(4);
    }

    public function contac(Request $request){
        return $this->menuContent(5);
    }

    public function applyList(Request $request){
        return $this->menuContent(6);
    }

    private function menuContent($id){
        $result = $this->getNewsModel()->getRow($id);
        if ($result) {

            $data = $result->toArray();
            $data['year'] = $result->created_at->year.'-'.$result->created_at->month;
            $data['day'] = $result->created_at->day;
            $data['time'] = $result->created_at->toTimeString();

            return $this->setStatusCode(200)
                        ->responseSuccess($data, 'success');
        }
        return $this->setStatusCode(400)->responseNotFound(__('api.public.empty_data'));
    }

    public function getCategories(Request $request)
    {
    	$data = CategoriesModel::all(['id','name'])->toArray();

    	return $this->setStatusCode(200)
                        ->responseSuccess($data, 'success');
    }

    public function getNewsModel()
    {

        $land = App::getLocale();
    	$newsModel = new NewsModel();
        $newsModel->setTable($land);
        return $newsModel;
    }
}