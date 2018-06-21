<template>
	<div class="ucontainer">
    	<bread-head></bread-head>
    	<div class="panel">
    		<div class="p-title">
    			<span class="fs-16 n" v-if="entrust">{{$t('cmn.entrust')}}{{$t('cmn.record')}}</span>
    			<div class="added">
    				<div class="pull-left">
    					<div class="dropdown">
    						<div data-toggle="dropdown" aria-haspopup="true">
    							{{$t('cmn.market')}}{{$t('punctuation.colon')}}<em>{{curMarket}}</em><i class="fa fa-angle-down"></i>
    						</div>
    						<ul class="dropdown-menu">
    							<li v-for="list(market, mindex) in markets">
                                    <a :data-symbol="market.symbol" @click="checkMarket(mindex)">{{market.buy.coin}} / {{market.sell.coin}}</a>
                                </li>
    						</ul>
    					</div>
    					<div class="dropdown">
    						<div data-toggle="dropdown" aria-haspopup="true">
    							{{$t('cmn.type')}}{{$t('punctuation.colon')}}<em>{{types[curType]}}</em><i class="fa fa-angle-down"></i>
    						</div>
    						<ul class="dropdown-menu">
    							<li v-for="list(name, index) in types"><a :data-symbol="index" v-on:click="getType(index)">{{name}}</a></li>
    						</ul>
    					</div>
    					<div class="dropdown">
    						<div data-toggle="dropdown" aria-haspopup="true">
    							{{$t('cmn.state')}}{{$t('punctuation.colon')}}<em>{{status[curStatus]}}</em><i class="fa fa-angle-down"></i>
    						</div>
    						<ul class="dropdown-menu">
    							<li v-for="list(name, index) in status"><a :data-symbol="index" v-on:click="getStatus(index)">{{name}}</a></li>
    						</ul>
    					</div>
    				</div>
    			</div>
    		</div>
    		<!--资产列表-->
			<div class="u-tb" v-loading="!records.loaded">
				<div class="row tb-hd text-center">
					<div class="col-xs-2">{{$t('cmn.time')}}</div>
					<div class="col-xs-2">{{$t('cmn.entrust')}}{{$t('units.quantity')}}/{{$t('cmn.bargain')}}{{$t('units.quantity')}}</div>
					<div class="col-xs-3">{{$t('cmn.entrust')}}{{$t('cmn.price')}}/{{$t('financial.priceOfToday')}}</div>
					<div class="col-xs-3">{{$t('financial.transaction')}}</div>
					<div class="col-xs-2">{{$t('cmn.state')}}/{{$t('cmn.operation')}}</div>
				</div>
				<div class="tb-ctn row">
					<!--<div class="no-data text-center">暂无记录</div>-->
					<ul class="tb-list text-center" v-if="records.list.length">
						<li class="clearfix"  v-for="record in records.list">
							<div class="col-xs-2">{{record.createdAt}}</div>
							<div class="col-xs-2">{{record.num}} / {{record.successfulNum}}</div>
							<div class="col-xs-3">{{record.price}} / {{record.successfulPrice}}</div>
							<div class="col-xs-3">{{record.successfulCount}}</div>
							<div class="col-xs-2">{{record.statusStr}}</div>
						</li>
					</ul>
                    <div v-if="!records.list.length" class="no-data col-xs-12 text-center">{{$t('cmn.noRecords')}}</div>
				</div>
				
				<!--分页-->
				<pager :curnum="records.paginate.currentPage" :lastPage="records.paginate.lastPage" @skip="getEntrust"></pager>
				<!--分页end-->
			</div>
    		<!--资产列表end-->
    		
    	</div>  
    	
    	
    </div>
</template>

<script>
	//
	//	更新币后无法更新其他状态, 需要重新套, 统一用一函数
	//
	
    import breadHead from '../common/breadHead'
    export default {
        components: {
            breadHead
        },
        data() {
          return {
            markets:'',
            curMarket:'--',
            types:'',
            curType:'',
            status:'',
            curStatus:'',
            symbol:'',
            curSymbol:'',
            entrust:'',
            records:{list:'', loaded:false, paginate:{currentPage:''}},
            market:''
          };
        },
        mounted(){
            var vm    = this;
            vm.symbol = this.$route.query.market;

            // 市场
            axios.get(this.commonApi.api.marketAll).then(function(response){
                var res = new Object(response.data.data);
                vm.markets = res.list;
                if(!vm.symbol) {
                    var market = vm.markets[0]
                    vm.curMarket = market.buy.coin + ' / ' + market.sell.coin;
                    vm.symbol = market.buy.coin + '_' + market.sell.coin;
                } else {
                    vm.curMarket = vm.symbol.replace('_', ' / ');
                }
            })

            // 类型
            axios.get(this.commonApi.api.entrustTypes).then(function(response){
                var res = new Object(response.data.data);
                vm.types = res.list;
                vm.curType = 'all';
            })

            // 状态
            axios.get(this.commonApi.api.entrustStatus).then(function(response){
                var res = new Object(response.data.data);
                vm.status = res.list;
                vm.curStatus = 'all';
            })

        },
        activated(){
            this.symbol    = this.$route.query.market;
        },
        watch:{
            symbol:function (){
                this.getEntrust()
            }
        },
        methods:{
            getEntrust:function(num){
                var vm = this;
                var page = num || 1;
                if (!vm.symbol) {
                    vm.symbol = vm.$route.query.market;
                }
            	vm.records.loaded = false;

                // 充值地址信息
                axios.get(this.commonApi.api.accountEntrust, 
                    {
                        params: 
                        {
                            'symbol':vm.symbol,
                            'type':vm.curType,
                            'status':vm.curStatus,
                            'page':page
                        }
                    }).then(function(response){
            			vm.records.loaded = true;
                    	if(response.data.code == 200){
                    		vm.$merge(vm.records, response.data.data)
	                        vm.records.list = response.data.data.list;
            			}else{
		                	vm.records.list = '';
		                	vm.records.paginate.lastPage = vm.records.paginate.currentPage = 1;
//          				vm.$comfirmbox({
//	                            title:'',
//	                            content:response.data.message
//	                        })
            			}
                });
            },
            getType: function(i){
                this.curType = i ? i : 'all';
                this.getEntrust()
            },
            getStatus: function(i){
                console.log(this.curStatus,i)
                this.curStatus = i ? i : 'all';
                this.getEntrust()
            },
            checkMarket: function(i){
                this.curSymbol = i;
                var market = new Object(this.markets[this.curSymbol]);
                this.curMarket = market.buy.coin + ' / ' + market.sell.coin;
                this.symbol = market.buy.coin + '_' + market.sell.coin;
                // this.getEntrust()
            }
        }
    }
</script>
