<template>
<div class="hac-index">
<section class="section-banner">
    <div class="container">
        <div class="inner">
        	<h1 class="text-center">资金安全保障，全新交易挖矿</h1>
			<h3 class="text-center">VB交易所开创全新用户交易挖矿模式，让交易产生更多收益</h3>
        </div>
        <div class="sum row">
        	<div class="col-xs-4">
	        	<div class="item">
	        		<div class="mb20"><em class="lab">昨日产出</em></div>
	        		<h4 class="mb20 fs-16">昨日挖矿产出</h4>
	        		<div class="num mb20">
	        			<i class="fa fa-bitcoin"></i>
	        			{{mineList.earningsMine || '--'}}
	        		</div>
	        		<p><a class="btn">挖矿规则 <i class="fa fa-angle-right ml5"></i></a></p>
	        	</div>
        	</div>
        	<div class="col-xs-4">
	        	<div class="item">
	        		<div class="mb20"><em class="lab">昨日收益</em></div>
	        		<h4 class="mb20 fs-16">昨日待分配收益累计合</h4>
	        		<div class="num mb20">
	        			<i class="fa fa-bitcoin"></i>
	        			{{mineList.earningsWait || '--'}}
	        		</div>
	        		<p>
	        			<span class="mr10">昨日每百万VB收益折合: </span>
	        			<span class="text-white"><i class="fa fa-bitcoin mr5"></i>{{mineList.earningsVbEvery || '--'}}</span>
	        		</p>
	        		<p>
	        			<span class="mr10">昨日持有VB静态日收益: </span>
	        			<span class="text-white"><i class="fa fa-bitcoin mr5"></i>{{mineList.earningsVbStatic || '--'}}</span>
	        		</p>
	        		<p>
	        			<span class="mr10">VB动态市盈率: </span>
	        			<span class="text-white">{{mineList.earningsVbProfit || '--'}}</span>
	        		</p>
	        	</div>
        	</div>
        	<div class="col-xs-4">
	        	<div class="item">
	        		<div class="mb20"><em class="lab">今日收益</em></div>
	        		<h4 class="mb20 fs-16">今日待分配收益累计合</h4>
	        		<div class="num mb20">
	        			<i class="fa fa-bitcoin"></i>
	        			{{mineList.earningsCurrent || '--'}}
	        		</div>
	        		<p>
	        			<span class="mr10">今日连续持有VB每百万份折合:</span>
	        			<span class="text-white"><i class="fa fa-bitcoin mr5"></i>{{mineList.earningsCurrentVb || '--'}}</span>
	        		</p>
	        	</div>
        	</div>
        </div>
    </div>
    <div class="nlist">
    	<div class="container">
    		<div class="item" v-if="newsTitle">
    			<em class="lab">公告</em>
    			{{newsTitle}}
    			<router-link to="/news" class="more">更多<i class="fa fa-angle-right ml5"></i></router-link>
    		</div>
    		<div class="item text-center" v-else>
    			暂无公告
    		</div>
    	</div>
    </div>
</section>

<section class="section-maincoin" v-if="markeQuotation.length > 0">
	<div class="container">
		<div class="row">
			<div class="item" v-for="item in markeQuotation">
				<div class="inner">
					<div class="fs-12 mb10 mt20 text-333">{{item.code}}</div>
					<div class="fs-20 mb20" :class="'text-'+((item.change > 0)?'increase':'reduce')"><span class="mr5">{{item.sign}}</span>{{item.price}}</div>
					<div><em class="lab" :class="'bg-'+((item.change > 0)?'increase':'reduce')">{{item.change}}</em></div>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="section-market">
    
    <div class="container">
    	<div class="tab-header clearfix">
    		<div class="pull-left">
	            <div class="row">
		            <ul class="nav nav-tabs" role="tablist" id="market-nav">
		                <li role="presentation" class="col-xs-3" :class="(i == 0)&&'active'" v-for="(m,i) in markets">
			                <a :href="'#'+m.market+'_market'" aria-controls="" v-on:click="toShow(i)" rol="tab" data-toggle="tab">
			                	{{ m.market }}{{$t('cmn.market')}}
			                	<i class="fa fa-caret-up fa-2x" aria-hidden="true"></i>
			                </a>
		                </li>
		            </ul>
	            </div>
            </div>
            <div class="serchblock mt10">
            	<div class="code-search">
            		<input class="form-control" />
            		<a class="search-btn text-center"><i class="fa fa-search"></i></a>
            	</div>
            </div>
	    </div>
    	
        <div id="market-content" class="tab-content row" v-loading="!marketContent.loaded || !markets.length">
            <div role="tabpanel" class="tab-pane active" >
                <table class="datatable table table-striped dataTable market-tb" cellspacing="0" width="100%" id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info" style="width: 100%;">
                    <thead>
                    <tr role="row">
                        <th class="text-center" style="width: 150px;">{{$t('cmn.codeType')}}</th>

                        <th class="text-center" style="width: 137px;">{{$tc('cmn.price',1)}}</th>
                        <th class="text-center" style="width: 137px;">{{$t('cmn.maxprc')}}</th>
                        <th class="text-center" style="width: 137px;">{{$t('cmn.minprc')}}</th>
                        <th class="text-center" style="width: 137px;">{{$t('cmn.amount')}}</th>
                        <th class="text-center" style="width: 80px;">{{$t('cmn.rank')}}</th>
                        <th class="text-center" style="width: 54px;"></th>
                    </tr>
                    </thead>
                    <tbody class="text-center">
                     <tr role="row" class="market-tb-r odd" :id="c.market" v-for="(c,k) in marketContent.list">
                        <td class="sorting_1">
                            <span>{{ c.code }}</span>
                        </td>
                        <td><span>{{ c.info.price }}</span></td>
                        <td><span class="text-increase">{{ c.info.height }}</span></td>
                        <td><span class="text-reduce">{{ c.info.low }}</span></td>
                        <td><span>{{ c.info.volume }}</span></td>
                        <td><span :class="parseFloat(c.info.riseRate)>0?'text-increase':'text-reduce'">{{ c.info.riseRate }}</span></td>
                        <td>
                        	<router-link :to="{path:'/market/trad', query:{market:c.market}}">
                        		<i class="fa fa-angle-right" aria-hidden="true"></i>
                        	</router-link>
                        </td>
                     </tr>
                     
                     </tbody>
                </table>
            </div>
            
        </div>
    </div>
</section>



<section class="section-adv">
    <div class="container">
        <!--<h2>{{$tc('arts.hpStitle',1)}}</h2>-->
        <div class="row">
            <div class="col-sm-6 col-md-3 adv-item">
                <span class="adv-ico"></span>
                <p class="fs-18">{{$tc('arts.hpSfstSub13',0)}}</p>
                <div class="desc" v-html="$tc('arts.hpSfstCtn13',0)"></div>
            </div>
            <div class="col-sm-6 col-md-3 adv-item">
                <span class="adv-ico adv-ico-2"></span>
                <p class="fs-18">{{$tc('arts.hpSfstSub13',1)}}</p>
                <div class="desc" v-html="$tc('arts.hpSfstCtn13',1)"></div>
            </div>
            <div class="col-sm-6 col-md-3 adv-item">
                <span class="adv-ico adv-ico-3"></span>
                <p class="fs-18">{{$tc('arts.hpSfstSub13',2)}}</p>
                <div class="desc" v-html="$tc('arts.hpSfstCtn13',2)"></div>
            </div>
            <div class="col-sm-6 col-md-3 adv-item">
                <span class="adv-ico adv-ico-4"></span>
                <p class="fs-18">{{$tc('arts.hpSfstSub46',0)}}</p>
                <div class="desc" v-html="$tc('arts.hpSfstCtn46',0)"></div>
            </div>
        </div>
    </div>
</section>

<section class="section-desc">
    <div class="container">
        <div class="pull-left">
        	<div class="views"></div>
        </div>
        <div class="pull-right">
        	<div class="desc text-right">
        		<h4 class="mb20">顶级技术，创新挖矿</h4>
        		<p>独立专业的区块链资产研究评估体系长期跟踪产业链并提供最权威中立的<br />资产分析一站式的项目进度跟踪及信息披露系统，独立专业的区块链资产研究评估</p>
        		<p>业链并提供最权威中立的资产分析一站式的项目进度跟踪及信息披露系统威中立的资产分析一站式的项目进息披露系统，独立专业的区块</p>
        		<p class="mb20">链资产研究评估体系长期跟踪产业链并提供最权威中立的资产分析一站式的<br />项目进度跟踪及信息披露系统威中立的资产分析一站式的项目进度跟踪及信息<br />披露系统，独立专业的区块链资产研究评估体系长期</p>
        		<a class="more">更多详情请密切关注 <i class="fa fa-angle-right ml5"></i></a>
        	</div>
        </div>
    </div>
</section>
<div class="clearfix"></div>

<div class="footer panel-footer">
    <div class="container">
        <div class="clearfix footer-row">
        	<ul class="text-center">
        		<li><router-link :to="{'path':'/news/content', 'query':{'info':'Aboutus'}}">{{$tc('foot.colctn1', 0)}}</router-link></li>
        		<li><router-link :to="{'path':'/news/content', 'query':{'info':'Terms'}}">{{$tc('foot.colctn1', 1)}}</router-link></li>
        		<li><router-link :to="{'path':'/news/content', 'query':{'info':'Privacy'}}">{{$tc('foot.colctn1', 2)}}</router-link></li>
        		<li><router-link :to="{'path':'/news/content', 'query':{'info':'Fees'}}">{{$tc('foot.colctn2', 1)}}</router-link></li>
        		<li><router-link :to="{'path':'/news/content', 'query':{'info':'Contact'}}">{{$tc('foot.colctn2', 2)}}</router-link></li>
        	</ul>
        </div>
        <div class="clearfix">
        	<ul class="text-center">
        		<li>©2018 vbex.io All Rights Reserved</li>
        	</ul>
        </div>
    </div>
</div>	
</div>

</template>

<script>
    export default {
		data(){
        	return {
                markets:[],
                info:{price:'-',height:'-',low:'-',volume:'-',riseRate:'-'},
                marketContent:{list:'', loaded:false},
                mineList:'',
                newsTitle:'',
                markeQuotation:[]
        	}
      	},
        mounted(){ 
        	var vm = this;
        	vm.getMarkets();
            vm.getContent();
            vm.getNews();
            vm.getMarkeQuotation();
            vm.mineSum();
            setInterval(function(){
            	vm.getContent();
            },10000)
       },
       methods:{
            getUserInfo(){
                var vm = this;
                axios.get(this.commonApi.api.getUserInfo).then(function(response){
                     vm.user = response.data.data;
                     vm.isDeduction = vm.user.isDeduction;	//???不存在改变量
                });
            },
	        getMarkets(){
	        	var vm = this;
	        	axios.get(this.commonApi.api.getMarkets,{ params: { 'isChildren': 1 } }).then(function(response){
                    var data = response.data.data.data;
                    var n = data.length;
                    for (var i = 0; i < n; i++) {
                    	var l = data[i]['currencies'].length
                    	for (var k = 0; k < l; k++) {
                    		data[i]['currencies'][k]['info'] = vm.info;
                    	}
                    }
                    
                    vm.markets = data;
                    vm.marketContent.list = vm.markets[0]['currencies'];
                });
	        },
	        getContent(){
	        	var vm = this;
	        	axios.get(this.commonApi.api.getMarketsCurrency).then(function(response){
                    var content = response.data.data.data;
                    vm.marketContent.loaded = true;
//                  var t = setInterval(function(){
                    	if(vm.markets.length){
		                    var k = vm.markets.length;
		                    for (var i = 0; i < k; i++) {
		                    	var n = vm.markets[i].currencies.length;
		                    	for (var m = 0; m < n; m++) {
		                    		var l = content[vm.markets[i]['market']].length;
		
		                    		for (var h = 0; h < l; h++) {
		                    			if (vm.markets[i].currencies[m]['symbol'] == content[vm.markets[i]['market']][h]['symbol']) {
		                    				vm.markets[i].currencies[m]['info'] = content[vm.markets[i]['market']][h];
		                    			}
		                    		}
		                    	}
		                    }
		                    vm.marketContent.list = vm.markets[0]['currencies'];
//	                    	clearInterval(t)
	                    }
//                  },500)
                });
	        },
	        toShow(i){
	        	var vm = this;
	        	vm.marketContent.list = vm.markets[i]['currencies'];
	        },
	        mineSum(){
	        	var vm = this;
	        	axios.get(this.commonApi.api.mineSum).then(function({data} = {}){
	        		if(data.code == 200) {
	        			vm.mineList = data.data
	        		} 
	        	}).catch(err => {
	        		console.log(err)
	        	})
	        },
	        getNews(){
	        	var vm = this;
	        	axios.get(this.commonApi.api.newsList, {params:{limit:1}}).then(function({data} = {}){
	            	if(data.code == 200&&data.data.list.length > 0) {
	            		vm.newsTitle = data.data.list[0].title
	            	}
	        	})
	        },
	        getMarkeQuotation(){
	        	var vm = this;
	        	axios.get(this.commonApi.api.markeQuotation).then(function({data} = {}){
	        		console.log(data.data)
	        		if(data.code == 200) {
	        			vm.markeQuotation = data.data
	        		} 
	        	}).catch(err => {
	        		console.log(err)
	        	})
	        }
       	}
    }
</script>

<style lang="less" scoped>
@import url('../../../../../public/css/less/hac/hac-index.less');
</style>





