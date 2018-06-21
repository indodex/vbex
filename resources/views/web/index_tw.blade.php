<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html> <!--<![endif]-->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv = "X-UA-Compatible" content = "IE=edge,chrome=1" />
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <title>哈希網</title>
    <meta name="keywords" content="哈希網">
    <meta name="description" content="哈希網">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <link href="css/hac-index.css" rel="stylesheet">
</head>
<body>
<div class="header">
<nav class="navbar navbar-inverse navbar-fixed-top  navbar-affix affix-top hac-navbar" role="navigation" data-spy="affix" data-offset-top="60">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">
                <div class="logo"><img src="img/logo.png"></div>
            </a>
        </div>
        <div class="navbar-collapse collapse " aria-expanded="true">
            <ul class="nav navbar-nav navbar-left h4">
                <li><a href="{{ env("APP_URL") }}">首页</a></li>
                <li><a href="{{ env("APP_URL") }}">交易中心</a></li>
                @if (Route::has('login'))
                @if (Auth::check())
                <li><a href="{{ env("APP_URL") }}/financial">账务中心</a></li>
                <li><a href="{{ env("APP_URL") }}/user">个人中心</a></li>
                @else
                <li><a href="{{ env("APP_URL") }}/login">账务中心</a></li>
                <li><a href="{{ env("APP_URL") }}/login">个人中心</a></li>
                @endif
                @endif
                <li><a href="{{ env("APP_URL") }}/news">公告</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right  h4">
                <li class="active"><div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="img/guoqi/gjduqi_001.png" height="18" alt="中文"> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="#">中文简体</a></li>
                        <li><a href="#">中文繁体</a></li>
                        <li><a href="#">English</a></li>
                    </ul>
                </div></li>
                @if (Route::has('login'))
                    @if (Auth::check())
                        <li><div class="btn-group">
                            <a href="javascript:;" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ Auth::user()->name }}<span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                @if (Auth::user()->is_admin)
                                    <li><a href="{{ url('/admin/user') }}">后台管理</a></li>
                                @endif
                                <li><a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            退出登录
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form></li>
                            </ul>
                        </div></li>
                    @else
                        <li><a href="/login">登录</a></li>
                        <li><a href="/register">注册</a></li>
                    @endif
                @endif
            </ul>
        </div>
        <!--/.nav-collapse -->
    </div>
</nav>
</div>
<section class="section-1">
    <div class="container">
        <div class="text-center slogan">
            <div>台湾第一家全球数字资产交易所即将上线</div>
            <div class="mt10">敬请期待！</div>
        </div>
        <div class="text-center timer">
            <span class="time-li">
                <div class="time" id="DAY-txt">23</div>
                <div>DAY</div>
            </span>
            <span class="time-li">
                <div class="time" id="HOUR-txt">23</div>
                <div>HOUR</div>
            </span>
            <span class="time-li">
                <div class="time" id="MINUTE-txt">23</div>
                <div>MINUTE</div>
            </span>
            <span class="time-li">
                <div class="time" id="SECONT-txt">23</div>
                <div>SECONT</div>
            </span>
        </div>
        <div class="text-center paper-box">
            <div class="fs-18">参与台湾第一个数字货币私募</div>
            <div class="has-btn-wrap">
            	<button type="button" class="btn btn-primary hac-btn-bg fs-14">点击参与</button>
        	</div>
        </div>
    </div>
</section>
<section class="market-list">
    <div class="tab-header">
        <div class="container">
            <!-- Nav tabs -->
            <div class="row">
            <ul class="nav nav-tabs" role="tablist" id="market-nav">
                
            </ul>
            </div>
        </div>
    </div>
    <div class="container">
        <!-- Tab panes -->
        <div id="market-content" class="tab-content row">
            <div role="tabpanel" class="tab-pane">
                <table class="datatable table table-striped dataTable market-tb" cellspacing="0" width="100%" id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info" style="width: 100%;">
                    <thead>
                    <tr role="row">
                        <th class="text-center" style="width: 150px;">币种 / 买方</th>

                        <th class="text-center" style="width: 137px;">价格</th>
                        <th class="text-center" style="width: 137px;">最高价</th>
                        <th class="text-center" style="width: 137px;">最低价</th>
                        <th class="text-center" style="width: 137px;">交易量</th>
                        <th class="text-center" style="width: 80px;">涨跌幅</th>
                        <th class="text-center" style="width: 54px;"></th>
                    </tr>
                    </thead>
                    <tbody class="text-center">
                     <tr role="row" class="market-tb-r odd">
                        <td class="sorting_1">
                            <!--<i class="fa fa-btc" aria-hidden="true"></i>-->
                            <!--<span class="coin-type">ETC</span>-->
                            <span>--</span>
                        </td>
                        <td><span>--</span></td>
                        <td><span class="text-danger">--</span></td>
                        <td><span class="text-success">--</span></td>
                        <td><span>--</span></td>
                        <td><span>--</span></td>
                        <td><a><i class="fa fa-angle-right" aria-hidden="true"></i></a></td>
                     </tr>
                     
                     </tbody>
                </table>
            </div>
            
        </div>
    </div>
</section>
<section class="adv-section">
    <div class="container">
        <h2>专业可靠的区块链资产交易平台</h2>
        <div class="row">
            <div class="col-sm-6 col-md-4 adv-item">
                <span class="adv-ico"></span>
                <p>合规合法</p>
                <div class="desc">
                    千万注册资本正规公司运作，核心合伙人为资深法律界人士，风控、合规、反洗钱体系完善。
                </div>
            </div>
            <div class="col-sm-6 col-md-4 adv-item">
                <span class="adv-ico adv-ico-2"></span>
                <p>快捷方便</p>
                <div class="desc">
                    充值即时、提现迅速，区块链资产充值2分钟内到账。
                </div>
            </div>
            <div class="col-sm-6 col-md-4 adv-item">
                <span class="adv-ico adv-ico-3"></span>
                <p>技术领先</p>
                <div class="desc">
                    多年高并发金融系统积累，交易引擎独创专业内存撮合技术与分布式账务技术，每秒处理能力超过百万单。
                </div>
            </div>
            <div class="col-sm-6 col-md-4 adv-item">
                <span class="adv-ico adv-ico-4"></span>
                <p>系统可靠</p>
                <div class="desc">
                    银行级用户数据加密、动态身份验证，多级风险识别控制，保障交易安全。
                </div>
            </div>
            <div class="col-sm-6 col-md-4 adv-item">
                <span class="adv-ico adv-ico-5"></span>
                <p>资金安全</p>
                <div class="desc">
                    钱包多层加密，离线存储于银行保险柜，资金第三方托管，银行定期出具资产报告。
                </div>
            </div>
            <div class="col-sm-6 col-md-4 adv-item">
                <span class="adv-ico adv-ico-6"></span>
                <p>7X24小时客户服务</p>
                <div class="desc">
                    行业最优质客户服务，网页、邮件、热线、微信多沟通渠道，保证任何客服需求十分钟得到解答处理。
                </div>
            </div>
        </div>
    </div>
</section>
<section class="join-section">
    <div class="container">
        <h2>安全有保障，资金全托管</h2>
        <!--<span class="bar-colours-1">5,3,9,6,5,9,7,3,5,2</span>--> 
        <div class="has-btn-wrap">
        	<button type="button" class="btn btn-primary hac-btn-bg fs-14">现在加入</button>
    	</div>
    </div>
</section>
<div class="clearfix"></div>

<div class="footer panel-footer">
    <div class="container">
        <div class="row">
            <div class="col-sm-3 hidden-xs hidden-sm">
                <div class="logo"><img src="img/logo.png"></div>
            </div>
            <div class="col-sm-9 row footer-content">
                <div class="col-xs-6 col-md-2">
                    <p>平台</p>
                    <ul>
                        <li><a href="" target="_blank">交易中心</a></li>
                        <li><a href="" target="_blank">系统公告</a></li>
                        <li><a href="" target="_blank">新闻中心</a></li>
                    </ul>
                </div>
                <div class="col-xs-6 col-md-2">
                    <p>关于我们</p>
                    <ul>
                        <li><a href="" target="_blank">用户协议</a></li>
                        <li><a href="" target="_blank">费率说明</a></li>
                        <li><a href="" target="_blank">API文档</a></li>
                    </ul>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4">
                    <p>平台</p>
                    <ul>
                        <li><a href="" target="_blank">常见问题</a></li>
                        <li><a href="" target="_blank">客服邮箱：service@hashtw.com</a></li>
                        <li><a href="" target="_blank">商务合作：service@hashtw.com</a></li>
                    </ul>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4">
                    <p>关注我们</p>
                    <ul>
                        <li><a href="" target="_blank"></a></li>
                        <li><a href="" target="_blank"></a></li>
                        <li><a href="" target="_blank"></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="js/jquery-1.11.3.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
<script src="js/peity/jquery.peity.min.js"></script>
<script>
    window.setInterval("getTime()",1000);
    function getTime(){
        var regEx = new RegExp("\\-","gi");
        var nowTime=new Date();
        var startTime=new Date(("2018-01-20 00:00:00").replace(regEx,"/"));
        function add0(m) {
            return m < 10 ? '0' + m : m
        }
        if(parseInt((startTime.getTime()-nowTime.getTime())/1000)>0){
            var leftsecond = parseInt((startTime.getTime()-nowTime.getTime())/1000);
            var day=Math.floor(leftsecond/(60*60*24));
            var hour=Math.floor((leftsecond-day*24*60*60)/3600);
            var minute=Math.floor((leftsecond-day*24*60*60-hour*3600)/60);
            var second=Math.floor(leftsecond-day*24*60*60-hour*3600-minute*60);
            $("#DAY-txt").text(add0(day));
            $("#HOUR-txt").text(add0(hour));
            $("#MINUTE-txt").text(add0(minute));
            $("#SECONT-txt").text(add0(second));
            return false;
        }
    }
	
	
	var cArr = null;
	getMarkets();
	function getMarkets(){
		getCodes();
		$.ajax({
			type:'get',
			url:'/api/v1/market/getMarkets',
			async:true,
			dataType:'json',
			data:{isChildren:1},
			success:function(response){
				var res = response.data;
				buildMarkets(res);
//				console.log(res)
				var timer = setInterval(function(){
					if(cArr){
						dataWrite(cArr);
						clearInterval(timer)
					}
				}, 1000)
			}
		});
		
		
	}
	
	function buildMarkets(obj){
		buildNav(obj.data);
		buildContent(obj.data);
	}
	
	function buildNav(obj){
		for(var i = 0; i < obj.length; i++){
			var item = obj[i];
			var $li = $('<li role="presentation" class="col-xs-3" />');
			var $a = $('<a />').text(item.market + '市场');
			$a.attr({
				href:'#'+item.market+'_market',
				'aria-controls':item.market+'_market',
				rol:'tab',
				'data-toggle':'tab'
			});
			$a.append('<i class="fa fa-caret-up fa-2x" aria-hidden="true"></i>')
			if(i == 0){
				$li.addClass('active');
			}
			$('#market-nav').append($li.append($a));
		}
	}
	
	function buildContent(obj){
		var $model = $('#market-content').find('.tab-pane').eq(0);
		for(var i = 0; i < obj.length; i++){
			var item = obj[i];
			var arr = item.currencies;
			var $ctn = $model.clone();
			$ctn.attr({id:item.market+'_market'});
			if(i == 0){
				$ctn.addClass('active');
			}
			
			for(var j = 0; j < arr.length; j++){
				var $tr = $ctn.find('.market-tb-r').eq(0).clone();
				$tr.find('td').eq(0).find('span').text(arr[j].code);
				$tr.find('td').eq(6).find('a').attr('href','/market/trad?market='+arr[j].code+'_'+item.market)
				$ctn.find('.market-tb').append($tr)
			}
			
			$ctn.find('.market-tb-r').eq(0).remove();
			$('#market-content').append($ctn)
		}
		
		$model.remove();
	}
	
	function getCodes(){
		$.ajax({
			type:'get',
			url:'/api/v1/market/getMarketsCurrency',
			dataType:'json',
			data:"",
			success:function(response){
				cArr = response.data.data;
			}
		});
	}
	
	function dataWrite(){
		for (var i in cArr){
			var $tr = $('#'+i + '_market').find('.market-tb-r');
			for(var j = 0; j < cArr[i].length; j++){
				$tr.eq(j).find('td').eq(1).find('span').text(cArr[i][j].price);
				$tr.eq(j).find('td').eq(2).find('span').text(cArr[i][j].height);
				$tr.eq(j).find('td').eq(3).find('span').text(cArr[i][j].low);
				$tr.eq(j).find('td').eq(4).find('span').text(cArr[i][j].volume + i);
				var cname = (cArr[i][j].riseRate >= 0)?'danger':'success';
				$tr.eq(j).find('td').eq(5).find('span').addClass('text-'+cname).text(parseFloat(cArr[i][j].riseRate).toFixed(2)+'%');
			}
		}
	}
	
</script>