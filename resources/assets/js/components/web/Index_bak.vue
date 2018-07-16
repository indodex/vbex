<template>
<div style="background-color:#FFF;">
<!--<link href="/css/hac-index.css" rel="stylesheet" type="text/css" />-->
<section class="section-1">
    <div class="container">
        <div class="text-center slogan">
            <div>{{$tc('arts.hpLogan', 1)}}</div>
            <div class="mt10">{{$tc('arts.hpLogan', 2)}}</div>
        </div>
        <!-- <div class="text-center timer">
            <span class="time-li">
                <div class="time" id="DAY-txt">{{ day }}</div>
                <div>DAY</div>
            </span>
            <span class="time-li">
                <div class="time" id="HOUR-txt">{{ hour }}</div>
                <div>HOUR</div>
            </span>
            <span class="time-li">
                <div class="time" id="MINUTE-txt">{{ minute }}</div>
                <div>MINUTE</div>
            </span>
            <span class="time-li">
                <div class="time" id="SECONT-txt">{{ secont }}</div>
                <div>SECONT</div>
            </span>
        </div> -->
        <div class="text-center paper-box">
            <div class="fs-18">{{$t('arts.hpLoganSub')}}</div>
            <!-- <div class="has-btn-wrap">
            	<button type="button" class="btn btn-primary hac-btn-bg fs-14">{{$t('arts.clickJoin')}}</button>
        	</div> -->
        </div>
    </div>
</section>
<section class="market-list">
    <div class="tab-header">
        <div class="container">
            <!-- Nav tabs -->
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
    </div>
    <div class="container">
        <!-- Tab panes -->
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
<section class="adv-section">
    <div class="container">
        <h2>{{$tc('arts.hpStitle',1)}}</h2>
        <div class="row">
            <div class="col-sm-6 col-md-3 adv-item">
                <span class="adv-ico"></span>
                <p>{{$tc('arts.hpSfstSub13',0)}}</p>
                <div class="desc" v-html="$tc('arts.hpSfstCtn13',0)"></div>
            </div>
            <div class="col-sm-6 col-md-3 adv-item">
                <span class="adv-ico adv-ico-2"></span>
                <p>{{$tc('arts.hpSfstSub13',1)}}</p>
                <div class="desc" v-html="$tc('arts.hpSfstCtn13',1)"></div>
            </div>
            <div class="col-sm-6 col-md-3 adv-item">
                <span class="adv-ico adv-ico-3"></span>
                <p>{{$tc('arts.hpSfstSub13',2)}}</p>
                <div class="desc" v-html="$tc('arts.hpSfstCtn13',2)"></div>
            </div>
            <div class="col-sm-6 col-md-3 adv-item">
                <span class="adv-ico adv-ico-4"></span>
                <p>{{$tc('arts.hpSfstSub46',0)}}</p>
                <div class="desc" v-html="$tc('arts.hpSfstCtn46',0)"></div>
            </div>
            <!--<div class="col-sm-6 col-md-4 adv-item">
                <span class="adv-ico adv-ico-5"></span>
                <p>{{$tc('arts.hpSfstSub46',1)}}</p>
                <div class="desc">{{$tc('arts.hpSfstCtn46',1)}}</div>
            </div>
            <div class="col-sm-6 col-md-4 adv-item">
                <span class="adv-ico adv-ico-6"></span>
                <p>{{$tc('arts.hpSfstSub46',2)}}</p>
                <div class="desc">{{$tc('arts.hpSfstCtn46',2)}}</div>
            </div>-->
        </div>
    </div>
</section>
<section class="join-section">
    <div class="container">
        <h2>{{$tc('arts.hpStitle',2)}}</h2>
        <!--<div class="has-btn-wrap">
        	<button type="button" class="btn btn-primary hac-btn-bg fs-14">{{$t('arts.clickJoin')}}</button>
    	</div>-->
    </div>
</section>
<div class="clearfix"></div>

<div class="footer panel-footer">
    <div class="container">
        <div class="clearfix footer-row">
        	<ul class="pull-left">
        		<li><router-link :to="{'path':'/news/content', 'query':{'info':'Aboutus'}}">{{$tc('foot.colctn1', 0)}}</router-link></li>
        		<li><router-link :to="{'path':'/news/content', 'query':{'info':'Terms'}}">{{$tc('foot.colctn1', 1)}}</router-link></li>
        		<li><router-link :to="{'path':'/news/content', 'query':{'info':'Privacy'}}">{{$tc('foot.colctn1', 2)}}</router-link></li>
        		<li><router-link :to="{'path':'/news/content', 'query':{'info':'Fees'}}">{{$tc('foot.colctn2', 1)}}</router-link></li>
        		<li><router-link :to="{'path':'/news/content', 'query':{'info':'Contact'}}">{{$tc('foot.colctn2', 2)}}</router-link></li>
        	</ul>
        	<ul class="pull-right fs-16">
        		<li><a><i class="fa fa-facebook"></i></a></li>
        		<li><a><i class="fa fa-twitter"></i></a></li>
        		<li><a><i class="fa fa-tumblr-square"></i></a></li>
        		<li><a><i class="fa fa-weixin"></i></a></li>
        		<li><a><i class="fa fa-qq"></i></a></li>
        	</ul>
        </div>
        <div class="clearfix footer-row">
        	<ul class="pull-left">
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
                day:'',
                hour:'',
                minute:'',
                secont:'',
                markets:[],
                info:{price:'-',height:'-',low:'-',volume:'-',riseRate:'-'},
                marketContent:{list:'', loaded:false},
        	}
      	},
        mounted(){ 
        	var vm = this;
        	setInterval(function () {
		            vm.etTime();
	        },1000);
        	vm.getMarkets();
            vm.getContent();
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
            etTime(){
		        var regEx = new RegExp("\\-","gi");
		        var nowTime = new Date();
		        var startTime = new Date(("2018-02-01 00:00:00").replace(regEx,"/"));
		        
		        if(parseInt((startTime.getTime()-nowTime.getTime())/1000)>0){
		            var leftsecond = parseInt((startTime.getTime()-nowTime.getTime())/1000);
		            var day = Math.floor(leftsecond/(60*60*24));
		            var hour = Math.floor((leftsecond-day*24*60*60)/3600);
		            var minute = Math.floor((leftsecond-day*24*60*60-hour*3600)/60);
		            var secont = Math.floor(leftsecond-day*24*60*60-hour*3600-minute*60);
		            this.day = this.add0(day);
		            this.hour = this.add0(hour);
		            this.minute = this.add0(minute);
		            this.secont = this.add0(secont);
		            return false;
		        }
		    },
		    add0(m) {
	            return m < 10 ? '0' + m : m
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
                    var t = setInterval(function(){
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
	                    	clearInterval(t)
	                    }
                    },500)
                });
	        },
	        toShow(i){
	        	var vm = this;
	        	vm.marketContent.list = vm.markets[i]['currencies'];
	        }
       	}
    }
</script>







