<template>
	<div class="ucontainer">
    	<bread-head></bread-head>
    	<div class="panel">
    		<!--用户信息-->
    		<div class="row mb20">
    			<div class="col-xs-4 u-info-box">
    				<div class="cl-box" v-if="account">
    					<div class="img-box"><img :src="account.avatar" alt="" /></div>
    					<div class="m-info">
    						<div class="m-info">
                                <div class="name fs-18">
                                    {{account.name}}
                                    <a class="fs-14 text-primary" data-toggle="modal" data-target="#info-dailog">{{$t('cmn.edit')}}</a>
                                </div>
                                <div class="msg">{{account.mobile || account.email}}</div>
                            </div>
    					</div>
    				</div>
    			</div>
    			<div class="col-xs-8">
    				<div class="pull-right user-asset">
    					<div class="clearfix">
    						<span class="text-gray-light fs-12 pull-left n">{{$t('cmn.account')}}{{$t('cmn.netAsset')}}{{$t('punctuation.colon')}}</span>
    						<span class="text-gray fs-14 pull-left">{{amount}}</span>
    						<div class="coin-type dropdown pull-left">
    							<a class="text-primary dropdown-header" data-toggle="dropdown" aria-haspopup="true">{{curCoin}} <i v-if="proportion" class="fa fa-angle-down"></i></a>
    							<ul class="dropdown-menu" v-if="proportion">
                                    <li v-for="act in proportion"><a :data-symbol="act.code" v-on:click="setCoin(act.code, act.proportion)">{{act.code}}</a></li>
                                </ul>
    						</div>
    					</div>
    				
    				</div>
    			</div>
    		</div>
    		<!--用户信息end-->
    		
    		<!--资产列表-->
			<div class="u-tb">
				<div class="row tb-hd">
                    <div class="col-xs-2">{{$t('cmn.codeType')}}</div>
					<div class="col-xs-2">{{$t('cmn.total')}}</div>
					<div class="col-xs-2">{{$t('cmn.netAsset')}}</div>
					<div class="col-xs-2">{{$t('financial.usable')}}</div>
					<div class="col-xs-2">{{$t('cmn.freeze')}}</div>
					<div class="col-xs-2">{{$t('cmn.operation')}}</div>
				</div>
				<div class="tb-ctn row"  v-loading="!balances.loaded">
					<ul class="tb-list" v-if="balances.list">
						<li class="clearfix" v-for="(item, index) in balances.list">
							<div class="col-xs-2"><span class="coin-lab"><img :src="item.logo" v-if="item.logo"/> {{item.coin}}</span></div>
							<div class="col-xs-2">{{item.totalStr}}</div>
							<div class="col-xs-2">{{item.totalStr}}</div>
                            <div class="col-xs-2">{{item.balanceStr}}</div>
							<div class="col-xs-2">{{item.lockedBalanceStr}}</div>
							<div class="col-xs-2 handler">
                                <router-link v-if="item.enableDeposit=='1'" :to="{ path:'/financial/recharge', query:{cointype:item.coin} }">{{$t('cmn.recharge')}}</router-link>
                                <router-link v-if="item.enableWithdraw=='1'" :to="{ path:'/financial/withdraw', query:{cointype:item.coin} }">{{$t('financial.extractCoin')}}</router-link>
                                <router-link :to="{ path:'/financial/bill', query:{cointype:item.coin}}">{{$t('cmn.bill')}}</router-link>
                            </div>
						</li>
					</ul>
                    <div v-if="!balances.list" class="no-data text-center">{{$t('cmn.noRecords')}}</div>
				</div>
				<!--分页-->
				<!--<pager :curnum="balances.paginate.currentPage" :lastPage="balances.paginate.lastPage" @skip="getBalanceData"></pager>-->
				<!--分页end-->
			</div>
		<!--资产列表end-->
    	</div>

        <!--模态框-->
        <dailog boxid="info" :boxtitle="$t('cmn.edit')+$t('cmn.account')" boxsize="sm" :params="account" @modalcallback="getUserInfo" ></dailog>
        <!--模态框end-->  
    	
    	
    </div>
</template>

<script>
//	import LeftMenu from './LeftMenu'
	import breadHead from '../common/breadHead'
    import dailog from '../common/dailog/dailog'
    import floatMath from '../common/floatMath.js'
    export default {
        components: {
			breadHead,
            dailog
        },
        data() {
          return {
            balances:{list:'', loaded:true, paginate:{currentPage:''}},
            account:'',
            curCoin:'USD',
            amount:'0.0000',
            coins:'',
            price:'',
            proportion:'',
          };
        },
        mounted(){
            var vm = this
             
             vm.getUserInfo();
             vm.getBalanceData();
        },
        methods:{
            setCoin:function(code, proportion){
                // var vm = this;
                // for (var i = vm.coins.length - 1; i >= 0; i--) {
                //     if(vm.coins[i]['code'] == coin) {
                //         vm.amount = vm.coins[i]['price'];
                //         vm.curCoin = vm.coins[i]['code'];
                //     } 
                //  }
                this.curCoin = code;
                // this.amount = this.price * proportion;
                if (code == 'USD') {
                    this.amount = floatMath.res(floatMath.mul(this.price, proportion),4)
                }else{
                    this.amount = floatMath.res(floatMath.mul(this.price, proportion),2)
                }
            },
            getBalanceData(num){
            	var vm = this,
            		page = num || 1;
            	vm.balances.loaded = false;
    			axios.get(vm.commonApi.api.balanceUrl, {params:{'page':page}}).then(function(response){
    				vm.balances.loaded = true;
					if(response.data.code == 200){
                		vm.$merge(vm.balances, response.data.data)
                        vm.balances.list = response.data.data.list;
                        vm.amount = response.data.data.price;
                        vm.price = response.data.data.price;
                        vm.proportion = response.data.data.proportion;
//                      console.log(vm.balances, response)
        			}else{
	                	vm.balances.list = '';
	                	vm.balances.paginate.lastPage = vm.balances.paginate.currentPage = 1;
        			}
            	})
            },
            getUserInfo(){
                var vm = this;
                axios.get(this.commonApi.api.getUserInfo).then(function(response){
                     vm.account = response.data.data;
                });
            }
        }
    }
</script>
