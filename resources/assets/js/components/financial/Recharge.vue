<template>
	<div class="ucontainer">
		<div class="panel">
    		<h3 class="p-title fs-16"><span class="n" v-if="curCoin">{{$t('cmn.recharge')}}({{curCoin}})</span></h3>
    		<div class="charge-wrap row">
    			<div class="col-md-12" v-loading="!address.loaded">
    				<div class="coin-list clearfix" >
    					<a v-for="coin in coins" class="item" :class="coin.coin == curCoin&&'active'" @click="coinCheck(coin.coin)">
    						<img :src="coin.logo" v-if="coin.logo" />{{coin.coin}}
    					</a>
    				</div>
    				<div class="ads" >
    					<p class="fs-12 text-gray-light">{{address.data.coin}} {{$t('cmn.wallet')}}{{$t('cmn.address')}}</p>
    					<div class="num">
    						<span class="fs-18 text-gray" id="coin_adrs">{{address.data.address}}</span>
    						<a class="text-primary" @click="copystr(address.data.address)">{{$t('cmn.copy')}}</a>
    					</div>
    				</div>
    				<div class="ads-qrcode">
    					<canvas id="ads-qrcode" class="ads-qrcode-cvs"></canvas>
    				</div>
    				<div class="fs-12 mb20">
    					<p class="text-gray">{{$t('cmn.recharge')}}{{$t('cmn.explain')}}{{$t('punctuation.colon')}}</p>
    					<div v-html="address.data.explain"></div>
    				</div>
    			</div>
    		</div>
		</div>
		
		<!--充值记录-->
		<div class="panel" v-loading="!address.loaded">
			<h3 class="p-title fs-16"><span class="n">{{address.data.coin}}{{$t('cmn.recharge')}}{{$t('cmn.record')}}</span></h3>
			<div class="u-tb">
				<div class="row tb-hd text-center">
					<div class="col-xs-2">{{$t('cmn.time')}}</div>
					<div class="col-xs-4">{{$t('cmn.recharge')}}ID</div>
					<div class="col-xs-2">{{$t('cmn.money')}}</div>
					<div class="col-xs-1">{{$t('cmn.confirm')}}{{$t('cmn.times')}}</div>
                    <div class="col-xs-1">{{$t('cmn.state')}}</div>
                    <div class="col-xs-1">{{$t('cmn.operation')}}</div>
				</div>
				<div class="tb-ctn row">
					<ul class="tb-list text-center" v-if="records.list.length">
						<li class="clearfix" v-for="record in records.list">
							<div class="col-xs-2">{{record.createdAt}}</div>
							<div class="col-xs-4">{{address.data.address}}</div>
							<div class="col-xs-2">{{record.amount}}</div>
							<div class="col-xs-1">{{record.confirmations}}</div>
                            <div class="col-xs-1">{{record.statusStr}}</div>
							<div class="col-xs-1"><a target="_blank" :href="record.url">{{$t('cmn.lookOver')}}</a></div>
						</li>
					</ul>
					<div v-if="!records.list.length" class="no-data col-xs-12 text-center">{{$t('cmn.noRecords')}}</div>
				</div>
				<!--分页-->
				<pager :curnum="records.paginate.currentPage" :lastPage="records.paginate.lastPage" @skip="getChargeData"></pager>
				<!--分页end-->
			</div>
		</div>
		
	</div>
</template>

<script>
	import breadHead from '../common/breadHead'
	import Qrcode from 'qrcode'
    export default {
        components: {
			breadHead
        },
        data() {
          return {
            coins:'',
            address:{data:'', loaded:false},
            records:{list:'', loaded:false, paginate:{currentPage:''}},
            curCoin:'',
            curpage:''
          };
        },
        mounted(){
            var vm = this;
            // 获取所有数字货币
            axios.get(this.commonApi.api.marketCoins).then(function(response){
             	var res = new Object(response.data.data);
                vm.coins = res.list;
            })

        },
        activated(){
        	this.curCoin = this.$route.query.cointype || 'BTC';
        },
        watch:{
        	curCoin(){
        		var vm = this;
        		// 充值地址信息
        		vm.address.loaded = false;
	            axios.get(this.commonApi.api.depositsAddress, {params: {'coinType':vm.curCoin}}).then(function(response){
	                vm.address.data = response.data.data;
	                vm.address.loaded = true;
	                Qrcode.toCanvas(document.getElementById('ads-qrcode'), vm.address.data.address , (err, el) => {
						if(el){
							el.style.width = '100%';
							el.style.height = '100%';
						}
					})
	            });
	            
	            // 充值记录
	            vm.getChargeData()
        	}
        },
        methods:{
        	coinCheck(str){
        		this.curCoin = str;
        	},
        	copystr(str){
				var adrsDOM = document.getElementById('coin_adrs');
				if (document.body.createTextRange) {
		            var range = document.body.createTextRange();
		            range.moveToElementText(adrsDOM);
		            range.select();
		        } else if (window.getSelection) {
		            var selection = window.getSelection();
		            var range = document.createRange();
		            range.selectNodeContents(adrsDOM);
		            selection.removeAllRanges();
		            selection.addRange(range);
		        }
		        document.execCommand('Copy');
        	},
        	getChargeData(num){
        		var vm = this,
    				page = num || 1 ;
        		
        		axios.get(this.commonApi.api.depositsRecords, {params: {'coinType':vm.curCoin, page:page}}).then(function(response){
        			if(response.data.code == 200){
        				vm.$merge(vm.records, response.data.data);
        				vm.records.list = response.data.data.list;
        			}else{
	                	vm.records.list = '';
	                	vm.records.paginate.lastPage = vm.records.paginate.currentPage = 1;
        			}
	            	
	            })
        	}
        }
    }
</script>

<style>
</style>