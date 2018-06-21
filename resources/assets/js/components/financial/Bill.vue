<template>
	<div class="ucontainer">
    	<bread-head></bread-head>
    	<div class="panel">
    		<div class="p-title">
    			<span class="fs-16 n">{{$t('cmn.synthesize')}}{{$t('cmn.bill')}}</span>
    			<div class="added">
    				<div class="pull-left">
    					<div class="dropdown">
    						<div data-toggle="dropdown" aria-haspopup="true">
    							<em class="coin-lab"><img :src="coinCodeLogo" v-if="coinCodeLogo" alt=""/> {{coinCode}}</em>
    							<i class="fa fa-angle-down"></i>
    						</div>
    						<ul class="dropdown-menu">
                                <li><a v-on:click="getCoinType('')">{{$t('cmn.all')}}</a></li>
    							<li v-for="coin in coins"><a v-on:click="getCoinType(coin.coin, coin.logo)">
    								<span class="coin-lab"><img :src="coin.logo" v-if="coin.logo"/> {{coin.coin}}</span></a>
    							</li>
    						</ul>
    					</div>
    					<div class="dropdown">
    						<div data-toggle="dropdown" aria-haspopup="true">
    							{{$t('cmn.type')}}{{$t('punctuation.colon')}}<em>{{types[curType] || $t('cmn.all')}}</em>
    							<i class="fa fa-angle-down"></i>
    						</div>
    						<ul class="dropdown-menu">
    							<li v-for="list(name, index) in types"><a v-on:click="getBillType(index)">{{name}}</a></li>
    						</ul>
    					</div>
    				</div>
    			</div>
    		</div>
    		<!--资产列表-->
			<div class="u-tb">
				<div class="row tb-hd">
					<div class="col-xs-2">{{$t('cmn.codeType')}}</div>
					<div class="col-xs-2">{{$t('cmn.time')}}</div>
					<div class="col-xs-3">{{$t('cmn.operation')}}</div>
					<div class="col-xs-3">{{$t('cmn.change')}}</div>
					<div class="col-xs-2">{{$t('cmn.surplus')}}</div>
				</div>
				<div class="tb-ctn row" v-loading="!records.loaded">
					<ul class="tb-list" v-if="records.list.length">
						<li class="clearfix" v-for="record in records.list">
							<div class="col-xs-2"><span class="coin-lab"><img :src="record.logo" v-if="record.logo"/> {{record.coin}}</span></div>
							<div class="col-xs-2">{{record.createdAt}}</div>
							<div class="col-xs-3">{{record.statusStr}}</div>
							<div class="col-xs-3">{{record.changeBalance}} {{record.coin}}</div>
							<div class="col-xs-2">{{record.balance}} {{record.coin}}</div>
						</li>
					</ul>
                    <div v-if="!records.list.length" class="no-data text-center">{{$t('cmn.noRecords')}}</div>
				</div>
				<pager :curnum="records.paginate.currentPage" :lastPage="records.paginate.lastPage" @skip="getBills"></pager>
			</div>
    		<!--资产列表end-->
    		
    	</div>  
    	
    	
    </div>
</template>

<script>
    import breadHead from '../common/breadHead'
    export default {
        components: {
            breadHead
        },
        data() {
          return {
            coins:'',
            records:{list:'', loaded:false, paginate:{currentPage:''}},
//          curpage:'',
            types:'',
            coinCode:this.$t('cmn.all'),
            coinCodeLogo:'',
            curType:''
          };
        },
        mounted(){
            var vm = this

            // 所有数字货币
            axios.get(this.commonApi.api.marketCoins).then(function(response){
                 vm.coins = response.data.data.list;
             })

            // 交易类型
            axios.get(this.commonApi.api.tradeTypes).then(function(response){
                 vm.types = response.data.data.list;
            })
            
            if(!this.$route.query.cointype)
            vm.getBills()
            
        },
        activated(){
        	this.coinCode = this.$route.query.cointype || this.$t('cmn.all');
        	this.curType = ''
        },
        watch:{
        	coinCode: function(){
        		this.getBills();
        	},
        	curType: function(){
        		this.getBills();
        	}
        },
        methods: {
        	getBills: function(num){
        		var vm = this,
        			coinCode = vm.coinCode == vm.$t('cmn.all') ? '' : vm.coinCode,
        			curType = vm.curType,
        			page = num || 1;
        			
        		vm.records.loaded = false;
        		axios.get(this.commonApi.api.bill, {params:{'coinType':coinCode, 'type':curType,  'page':page}}).then(function(response){
        			vm.records.loaded = true;
	                if(response.data.code == 200) {
						vm.$merge(vm.records, response.data.data);
						vm.records.list = response.data.data.list;
	                }else{
//	                	console.log(response.data.code, vm.records )
	                	vm.records.list = '';
	                	vm.records.paginate.lastPage = vm.records.paginate.currentPage = 1;
	                }
	            })
        	},
            getCoinType: function(ctype, url){
                this.coinCode = ctype ? ctype : this.$t('cmn.all');
                this.coinCodeLogo = url ? url : '';
            },
            getBillType: function(i){
                this.curType = i ? i : '';
            }
        }
    }
</script>
