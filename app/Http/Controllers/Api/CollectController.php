<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use QL\QueryList;

class CollectController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //采集某页面所有的图片
    	$data = QueryList::get('http://www.feixiaohao.com')
							->rules([
								'name'    => array('.boxContain>table>tbody>tr', 'html'),
							])->query()->getData();

		$info = $data->all();
		foreach ($info as &$value) {
			$value['name'] = $this->strip_html_tags($value['name'],['a','img']);
		}
		// 缓存起来
        echo "<pre>";
		
		print_r($info);exit;
    }

    function strip_html_tags($str, $tags, $content=0){
        if($content){
            $html=array();
            foreach ($tags as $tag) {
                $html[]='/(<'.$tag.'.*?>[\s|\S]*?<\/'.$tag.'>)/';
            }
            $data=preg_replace($html,'',$str);
        }else{
            $html=array();
            foreach ($tags as $tag) {
                $html[]="/(<(?:\/".$tag."|".$tag.")[^>]*>)/i";
            }
            $data=preg_replace($html, '', $str);
        }
        return $data;
    }

}