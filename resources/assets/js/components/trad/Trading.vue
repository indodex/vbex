<template>
	<div class="ucontainer nomenu">
		<bread-head></bread-head>
		<div class="clearfix row">
			<div class=" col-xs-8">
				<div class="panel trad-form clearfix">
					<div class="col-xs-6">
						<form method="post" autocomplete="off" action="javascript:;" v-loading="!buyForm.loaded">
							<div class="trad-form">
								<div class="form-group has-feedback clearfix">
		                        	<label class="control-label static pull-left">{{$t('trad.buying')}}{{account.mainBalance.code}}</label>
		                            <label class="control-label static pull-right">{{$t('financial.usable')}}{{$t('punctuation.colon')}}
		                                <b id="canUseMoney" class="text-increase">{{ account.exchangeBalance.balance }}</b> {{ account.exchangeBalance.code }}
		                            </label>
		                        </div>
		                        <div class="form-group has-label">
									<label class="control-label" for="buyUnitPrice">{{$t('trad.buying')}}{{$t('cmn.price')}}({{ account.exchangeBalance.code }})</label>
									<input id="buyUnitPrice" name="buyUnitPrice" type="text" class="form-control text-increase" placeholder="--" @keyup="amountInput" @focus="amountInput"/>
								</div>
								<div class="form-group has-label">
									<label class="control-label" for="buyNumber" >{{$t('trad.buying')}}{{$t('units.number')}}({{ account.mainBalance.code }})</label>
									<!--v-model="buyForm.amount"-->
									<input type="text" class="form-control text-increase"  id="buyNumber"  placeholder="--" name="buyNumber" @keyup="amountInput" />
								</div>
								<!-- 买单滑动杆 -->
	                            <div class="form-group">
									<div class="drag-box clearfix">
										<dragbar :percent.sync="buyForm.prcn" viewcolor="red" @drag="buydrag"></dragbar>
										<div class="u-text "><span>{{buyForm.prcnView}}%</span></div>
									</div>
								</div>
								<p>{{$t('trad.plan')}}{{$t('financial.transaction')}}{{$t('punctuation.colon')}}<span class="text-increase">{{buyForm.volume}}</span> {{ account.exchangeBalance.code }}</p>
								<div class="form-group">
									<!--<button class="btn btn-increase form-sub-btn" type="button" @click="tradSubmit">限价买入</button>-->
								    <button id="buyBtn" name="buyFormBtn" type="button" class="btn btn-increase form-sub-btn" @click="tradSubmit">{{$t('trad.atOnceBuy')}}</button>
	                            </div>
							</div>
						</form>
					</div>
					<div class="col-xs-6">
						<form method="post" autocomplete="off" action="javascript:;"  v-loading="!sellForm.loaded">
							<div class="trad-form">
								<div class="form-group has-feedback clearfix">
		                        	<label class="control-label static pull-left">{{$t('trad.sale')}}{{account.mainBalance.code}}</label>
		                            <label class="control-label static pull-right">{{$t('financial.usable')}}{{$t('punctuation.colon')}}
		                                <b id="canUseCoin" class="text-reduce">{{ account.mainBalance.balance }}</b> {{ account.mainBalance.code }}
		                            </label>
		                        </div>
		                        <div class="form-group has-label">
									<label class="control-label" for="buyUnitPrice">{{$t('trad.sale')}}{{$t('cmn.price')}}({{ account.exchangeBalance.code }})</label>
									<input type="text" class="form-control text-reduce" id="sellUnitPrice" name="sellUnitPrice" placeholder="--" @keyup="amountInput" @blur="amountInput" />
									<!-- <p>价格折合： <span class=""></span>USD</p> -->
								</div>
								<div class="form-group has-label">
									<label class="control-label" for="buyUnitPrice">{{$t('trad.sale')}}{{$t('units.quantity')}}({{ account.mainBalance.code }})</label>
									<!--v-model="sellForm.amount"-->
									<input type="text" class="form-control text-reduce" id="sellNumber"  placeholder="--" name="sellNumber" @keyup="amountInput"/>
								</div>
								<!--拖动条-->
								<div class="form-group">
									<div class="drag-box clearfix">
										<dragbar :percent.sync="sellForm.prcn" viewcolor="green" @drag="selldrag"></dragbar>
										<div class="u-text "><span>{{sellForm.prcnView}}%</span></div>
									</div>
								</div>
								<p>{{$t('trad.plan')}}{{$t('financial.transaction')}}{{$t('punctuation.colon')}}<span class="text-reduce">{{sellForm.volume}}</span> {{ account.exchangeBalance.code }}</p>
								<div class="form-group">
									<!--<button class="btn btn-reduce form-sub-btn" @click="tradesEntrust(0)">限价卖出</button>-->
								    <button id="sellBtn" name="sellFormBtn" type="button"  class="btn btn-reduce form-sub-btn" @click="tradSubmit">{{$t('trad.atOnceSale')}}</button>
		                        </div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="col-xs-4 pull-right">
				<div class="panel" style="margin-left:-10px;">
					<div class="trad-depth">
						<div class="price-box">
							<div class="prc clearfix">
								<span>{{ ticker.lastPrice }}</span>
								<em class="rate" :class="(parseFloat(ticker.riseRate) > 0)?'bg-increase':'bg-reduce'">{{ ticker.riseRate }}%</em>
							</div>
							<div class="mb10">
								<span class="text-increase">{{$t('units.hight')}}:{{ ticker.hightPrice }}</span>
								<span class="text-reduce">{{$t('units.low')}}:{{ ticker.lowPrice }}</span>
								<span>{{$t('units.quantity')}}:{{ ticker.volume }}</span>
							</div>
						</div>
						
						<div class="list">
							<div class="list-cell">
								<a class="text-gray-light" role="button">
									<span>{{$t('trad.gears')}}{{$t('trad.control')}}:{{depthList.curdepth}}{{$t('units.grade')}}</span>
									<i class="fa fa-angle-down"></i>
								</a>
								<ul>
									<li class="text-gray-light" v-for='(item, index) in depthList.dLength' @click="changeDepth(item)">{{item}}{{$t('units.grade')}}</li>
								</ul>
							</div>
							<div class="list-cell">
								<a class="text-gray-light" role="button">
									<span>{{$t('trad.merge')}}{{$t('trad.depth')}}:{{ a.str }}</span>
									<i class="fa fa-angle-down"></i>
								</a>
								<ul>
									<li  class="text-gray-light" v-for="l in list" @click="changeA(l)">{{ l[0]}}</li>
								</ul>
							</div>
						</div>
						<div class="ctn">
							<table>
								<thead>
									<thead>
										<th width="10%">{{$t('trad.gears')}}</th>
										<th width="23%">{{$t('cmn.price')}}({{ account.exchangeBalance.code }})</th>
										<th class="text-right" width="23%">{{$t('units.number')}}{{$t('units.quantity')}}({{ account.mainBalance.code }})/</th>
										<th width="21%">{{$t('trad.depth')}}</th>
									</thead>
								</thead>
							</table>
							<table class="sell">
								<tr v-for="(item, index) in depthList.sellList">
										<td class="text-reduce" width="16%">{{$t('trad.sell')}}{{index+1}}</td>
										<td width="23%">{{item.price}}</td>
										<td class="text-right" width="23%">{{item.num }}</td>
										<td width="21%"><div class="tbar" :style="'width:'+item.depth+'%'"></div></td>
								</tr>

							</table>
							<table class="buy">
								<tr v-for="(item, index) in depthList.buyList">
										<td class="text-increase" width="16%">{{$t('trad.buy')}}{{index+1}}</td>
										<td width="23%">{{item.price}}</td>
										<td class="text-right" width="23%">{{item.num}}</td>
										<td width="21%"><div class="tbar" :style="'width:'+item.depth+'%'"></div></td>
								</tr>
							</table>
						</div>
						
					</div>
				</div>
			</div>
			<div :class="(depthList.curdepth == 5)?'col-xs-12':'col-xs-8'">
				<div class="panel">
					<div class="trad-data-list">
						<!--<div class="trad-data-list-title"><span>正在进行的委托</span></div>-->
						<div class="p-title"><span class="fs-16">正在进行的委托</span></div>
						<div class="u-tb">
							<div class="row tb-hd text-center">
								<div class="col-xs-2">{{$t('cmn.entrust')}}{{$t('cmn.time')}}</div>
								<div class="col-xs-4">{{$t('cmn.entrust')}}{{$t('cmn.quantity')}}/{{$t('trad.traded')}}({{ account.mainBalance.code }})</div>
								<div class="col-xs-2">{{$t('cmn.entrust')}}{{$t('cmn.price')}}/{{$t('financial.priceOfToday')}}({{ account.exchangeBalance.code }})</div>
								<div class="col-xs-2">{{$t('cmn.bargain')}}{{$t('cmn.total')}}({{ account.exchangeBalance.code }})</div>
								<div class="col-xs-1">{{$t('cmn.state')}}</div>
								<div class="col-xs-1">{{$t('cmn.operation')}}</div>
							</div>
							<div class="tb-ctn row" v-loading="!entrustList.loaded">
								<ul class="tb-list text-center" v-if="entrustList.list.length">
									<li class="clearfix" v-for="t in entrustList.list">
										<div class="col-xs-2">{{ t.createdAt }}</div>
										<div class="col-xs-4">
											<span v-if="t.type == 'buy'" class="text-increase">{{$t('trad.buy')}}</span>
											<span v-if="t.type == 'sell'" class="text-reduce">{{$t('trad.sell')}}</span>
											{{ t.num }} / {{ t.successfulNum }}
										</div>
										<div class="col-xs-2">{{ t.price }} / {{ t.averagePrice }}</div>
			                            <div class="col-xs-2">{{ t.price * t.successfulNum }}</div>
										<div class="col-xs-1">{{ t.statusStr}}</div>
										<div class="col-xs-1" ><a href="javascript:;" @click="orderCancel(t.id)">{{$t('cmn.revocation')}}</a></div>
									</li>
								</ul>
								<div v-if="!entrustList.list.length" class="no-data col-xs-12 text-center">{{$t('cmn.revocation')}}</div>
							</div>
							<!--<pager :curnum="records.paginate.currentPage" :lastPage="records.paginate.lastPage" @skip="getChargeData"></pager>-->
						</div>
					</div>
				</div>
			</div>
			<div :class="(depthList.curdepth == 5)?'col-xs-12':'col-xs-8'">
				<div class="panel">
					<div class="trad-data-list">
						<!--<div class="trad-data-list-title"><span>历史委托</span></div>-->
						<div class="p-title"><span class="fs-16">{{$t('cmn.history')}}{{$t('cmn.entrust')}}</span></div>
						<div class="u-tb">
							<div class="row tb-hd text-center">
								<div class="col-xs-2">{{$t('cmn.entrust')}}{{$t('cmn.time')}}</div>
								<div class="col-xs-4">{{$t('cmn.entrust')}}{{$t('cmn.quantity')}}/{{$t('trad.traded')}}({{ account.exchangeBalance.code }})</div>
								<div class="col-xs-2">{{$t('cmn.entrust')}}{{$t('cmn.price')}}/{{$t('financial.priceOfToday')}}({{ account.mainBalance.code }})</div>
								<div class="col-xs-2">{{$t('cmn.bargain')}}{{$t('cmn.total')}}({{ account.mainBalance.code }})</div>
								<div class="col-xs-1">{{$t('cmn.state')}}</div>
								<div class="col-xs-1">{{$t('cmn.operation')}}</div>
							</div>
							<div class="tb-ctn row" v-loading="!historyList.loaded">
								<ul class="tb-list text-center" v-if="historyList.list.length">
									<li class="clearfix" v-for="c in historyList.list">
										<div class="col-xs-2">{{ c.createdAt }}</div>
										<div class="col-xs-4">
											<span v-if="c.type == 'buy'" class="text-increase">{{$t('trad.buy')}}</span>
											<span v-if="c.type == 'sell'" class="text-reduce">{{$t('trad.sell')}}</span>
											{{ c.num }} / {{ c.successfulNum }}
										</div>
										<div class="col-xs-2">{{ c.price }} / {{ c.averagePrice }}</div>
			                            <div class="col-xs-2">{{ c.price * c.successfulNum }}</div>
										<div class="col-xs-1">{{ c.statusStr}}</div>
										<div class="col-xs-1">
											<a href="javascript:;" @click="showDetail(c)">{{$t('cmn.lookOver')}}{{$t('cmn.detail')}}</a>
										</div>
									</li>
								</ul>
								<div v-if="!historyList.list.length" class="no-data col-xs-12 text-center">{{$t('cmn.noRecords')}}</div>
							</div>
							<!--<pager :curnum="records.paginate.currentPage" :lastPage="records.paginate.lastPage" @skip="getChargeData"></pager>-->
						</div>
					</div>
				</div>
			</div>
		</div>
		<dailog boxid="bills" :boxtitle="$t('trad.detail')" boxsize="lg" :params="tradeDetail"></dailog>
	</div>
</template>

<script>
import breadHead from '../common/breadHead'
import dailog from '../common/dailog/dailog'
import floatMath from '../common/floatMath.js'
export default{
	components:{
		breadHead,
		dailog
	},
	data(){
		return{
			symbol:this.$route.query.market,
			list:[['0.1','1'],['0.01','2'],['0.001','3'],['0.0001','4']],
			a:{'str':'0.0001','n':4},
			account:{
				"mainBalance": {
			      "balance": "--",
			      "code": "",
			      "decimals": null,
			      "minTradingVal":0
			    },
			    "exchangeBalance": {
			      "balance": "--",
			      "code": "",
			      "decimals": null,
			      "minTradingVal":0
			    },
			    exchangeRate:0
			},
			ticker:{
				lastPrice:"--",
				riseRate:"--",
				hightPrice:"--",
				lowPrice:"--",
				volume:"--"
			},
			withBuyPrc:0,
			showBuyPrc:0,
			withSellPrc:0,
			showSellPrc:0,	//百分比
			buyForm:{
				isBuy:1,
				amount:0,
				volume:0,
				loaded:true,
				prcn:0,
				prcnView:0
			},
			sellForm:{
				isBuy:0,
				amount:0,
				volume:0,
				loaded:true,
				prcn:0,
				prcnView:0
			},
			entrustList:{
				list:'',
				loaded:false
			},
			historyList:{
				list:'',
				loaded:false
			},
			depthList:{
				dLength:[5,10,20,50],
				buyList:'',
				sellList:'',
				curdepth:5,
				loaded:false
			},
			tradeDetail:'',
		}
	},
    mounted(){
        var vm = this;
        
        axios.get(this.commonApi.api.getUserInfo).then(function(response){
            vm.init();
			setInterval(function(){
				vm.init()
			},10000);
        }).catch(function(res){
        	console.log(res)
        	vm.$comfirmbox({content:$t('cmn.please')+$t('cmn.login')}).then(function(){
        		vm.$router.push({path:'/login'});
        	})
        })
        
    },
    computed:{
		buyFormPrcn(val){
			return this.buyForm.prcn;
		},
		sellFormPrcn(val){
			return this.sellForm.prcn;
		}
	},
	watch:{
		buyFormPrcn(rate){
			var vm = this;
			vm.buyForm.prcnView = floatMath.res(floatMath.mul(rate, 100), 2);
		},
		sellFormPrcn(rate){
			var vm = this;
			vm.sellForm.prcnView = floatMath.res(floatMath.mul(rate, 100), 2);
		}
	},
	methods:{
        showDetail: function(e){
        	e.market = this.$route.query.market;
        	this.tradeDetail = e;
        	$('#bills-dailog').modal('show')
        },
        init(){
	        this.getAccount();
			this.getEntrustTrading();
			this.getEntrustCompleted();
			this.marketTicker();
			this.getLengthDepth(this.depthList.curdepth);
        },
        getAccount(callback){
            var vm = this;
            axios.get(this.commonApi.api.marketAccount, {params:{'market':vm.$route.query.market}}).then(function(response){
                 var _response = response.data;
                 if(typeof callback == 'function') callback();
                 if(_response.code == 200) {
                 	vm.account = _response.data;
                 }
            }).then(function(){
            	if(typeof callback == 'function') callback();
            });
       	},
        amountInput(e){
        	let vm = this;
        	let	ename = e.target.getAttribute('name');
			if(e.target.value == '') e.target.value = 0;
			//输入格式限制
			vm.amountRewrite(e.target);
			//最大购买量限制
			vm.amountCompute(e.target, ename);
			//输入最小量限制
			vm.amountLimit(e.target, ename);
		},
       	amountRewrite(elemt){
       		elemt.value = elemt.value.replace(/[^\d\.]/g,'');
			elemt.value = elemt.value.replace(/^\./g,'');
			elemt.value = elemt.value.replace('.','###').replace(/\./g,'').replace('###','.');
       	},
       	amountCompute(elemt, ename){
       		var vm = this,
       			sum = '',
       			type_ = (ename.indexOf('buy') != -1)?'buy':'sell',
       			tag_arr = (ename.indexOf('buy') != -1)?'exchangeBalance':'mainBalance',
       			tag_price = document.getElementById(type_+'UnitPrice'),
       			tag_amount = document.getElementById(type_+'Number');
       			
       			if(type_ == 'buy'){
	   				sum = floatMath.mul(tag_price.value, parseFloat(tag_amount.value));
	   				if(sum > parseFloat(vm.account[tag_arr].balance)){
	   					tag_amount.value = floatMath.res(floatMath.div(parseFloat(vm.account[tag_arr].balance), tag_price.value), vm.account.mainBalance.decimals);
	   				}
	   				vm[type_+'Form'].volume = floatMath.res(floatMath.mul(tag_price.value, tag_amount.value), vm.account.exchangeBalance.decimals);
	   				vm.prcnCompute(type_, vm[type_+'Form'].volume, vm.account[tag_arr].balance);
				}else{
					tag_amount.value = (parseFloat(tag_amount.value) > parseFloat(vm.account[tag_arr].balance))? vm.account[tag_arr].balance:tag_amount.value;
					vm[type_+'Form'].volume = floatMath.res(floatMath.mul(tag_price.value, tag_amount.value), vm.account.exchangeBalance.decimals);
					vm.prcnCompute(type_, tag_amount.value, vm.account[tag_arr].balance);
				}
       	},
       	amountLimit(elemt, ename){
       		var vm = this,
       			type_ = (ename.indexOf('buy') != -1)?'buy':'sell';
       		if(ename.indexOf('Price') != -1){
       			elemt.value = floatMath.res(elemt.value, vm.account.exchangeBalance.decimals);
       		}else if(ename.indexOf('Number') != -1){
       			elemt.value = floatMath.res(elemt.value, vm.account.mainBalance.decimals);
       		}
       	},
       	prcnCompute(type, val, balance){
       		var vm = this;
       		var prcn = 0;
       		if(parseFloat(balance) != 0){
       			var prcn = floatMath.div(val, balance);
       		}
       		vm[type+'Form'].prcn = prcn;
       	},
       	buydrag(prc){
       		var vm = this,
				tag_price = document.getElementById('buyUnitPrice'),
				tag_amount = document.getElementById('buyNumber');
			if(tag_price.value == 0 || tag_price.value == ''){
				this.buyForm.prcn = 0;
				tag_amount = 0;
				return;
			}
			vm.buyForm.volume = floatMath.res(floatMath.mul(vm.account.exchangeBalance.balance, prc), vm.account.exchangeBalance.decimals);
			tag_amount.value = floatMath.res(floatMath.div(vm.buyForm.volume, tag_price.value), vm.account.mainBalance.decimals);
			if(tag_amount.value <= 0){ 
				this.buyForm.prcn = 0;
			}
       	},
       	selldrag(prc){
       		var vm = this,
				tag_price = document.getElementById('sellUnitPrice'),
				tag_amount = document.getElementById('sellNumber');
			if(tag_price.value == 0 || tag_price.value == ''){
				this.sellForm.prcn = 0;
				tag_amount = 0;
				return;
			}
			tag_amount.value = floatMath.res(floatMath.mul(vm.account.mainBalance.balance, prc), vm.account.mainBalance.decimals);
			vm.sellForm.volume = floatMath.res(floatMath.mul(tag_amount.value, tag_price.value), vm.account.exchangeBalance.decimals);
			
			if(tag_amount.value <= 0){ 
				this.sellForm.prcn = 0;
			}
       	},
       	tradComfirm(e){
       		var vm = this,
       			objname = e.target.getAttribute('name'),
       			ftype = (objname.indexOf('buy') != -1)?"buy":"sell",
       			tiptxt = (objname.indexOf('buy') != -1)?vm.$t('trad.purchase'):vm.$t('trad.sold'),
       			formObj = vm[ftype+'Form'];
       			
       		if(parseFloat(formObj.volume).toFixed(vm.account.exchangeBalance.decimals) <= 0 || parseFloat(document.querySelector("#"+ftype+"Number").value).toFixed(vm.account.mainBalance.decimals) <= 0 ){
            	vm.$comfirmbox({ content:vm.$t('trad.formalError') });
            	return false;
            }
       		
       		vm.$comfirmbox({ content: vm.$tc('cmn.confirm') + tiptxt, comfirm:true }).then(function(){
					vm.tradSubmit(e)
       		}).catch(function(){
					return;
       		})
       	},
        tradSubmit(e){	//表单提交
            let vm = this,
            	objname = e.target.getAttribute('name'),
            	ftype = (objname.indexOf('buy') != -1)?"buy":"sell",
            	formObj = vm[ftype+'Form'];
            
            vm[ftype+'Form'].loaded = false;
            axios.post(vm.commonApi.api.tradesEntrust, {
            	market:vm.$route.query.market,
            	isBuy:formObj.isBuy,
            	unitPrice:document.querySelector("#"+ftype+"UnitPrice").value,	
            	number:document.querySelector("#"+ftype+"Number").value,
            }).then(function(response){
                var _response = response.data;
                if(_response.code == 200) {
                 	vm.$comfirmbox({ content:_response.message });
                 	vm.getAccount(function(){
                 		vm[ftype+'Form'].loaded = true;
                 	});	//更新余额
					vm.getEntrustTrading();	//更新订单列表
				}else{
					vm.$comfirmbox({ content:_response.message });
                 		vm[ftype+'Form'].loaded = true;
				}
				
//				vm.depthAndTradesReflash();	//测试用事件
				
            }).catch(function(response){
            	vm[ftype+'Form'].loaded = true;
            });
       	},
       	getEntrustTrading(market){	//委托列表
			var vm = this;
			axios.get(this.commonApi.api.getEntrust, {params: {'symbol':vm.symbol,'status':'trading'}}).then(function(response){
               var res = response.data;
               vm.entrustList.loaded = true;
                if (res.code == 200) {
                	vm.entrustList.list = res.data.list;
                	vm.getAccount();	//更新余额
                }
            })
		},
		getEntrustCompleted(){	//历史列表
			var vm = this;
			axios.get(this.commonApi.api.getEntrust, {params: {'symbol':vm.$route.query.market,'status':'past'}}).then(function(response){
               var res = response.data;
                if (res.code == 200) {
                	// vm.entrustList.loaded = true;
                	vm.historyList.list = res.data.list;
                	vm.getAccount();	//更新余额
                }
                vm.historyList.loaded = true;
            })
		},
		orderCancel(id){	//取消订单
        	var vm = this;
        	vm.$comfirmbox({
        		content:"确定取消订单？",
        		 comfirm:true 
    		}).then(function(){
                axios.post(vm.commonApi.api.tradesEntrustCancel, {
	            	id:id
	            }).then(function(response){
	                 var _response = response.data;
	                 if(_response.code == 200) {
	                 	vm.getAccount();	//跟新余额
	                 	vm.getEntrustTrading();	//更新委托列表
	                 }else{
	                 	vm.$comfirmbox({ content:_response.message });
	                 }
	            });
	            
//	            vm.depthAndTradesReflash();	//测试用事件
	            
            }).catch(function(){
            	return;
            })
    	},
    	marketTicker(){	//获取最新价格
            var vm = this;
            axios.get(this.commonApi.api.marketTicker, {params:{'market':vm.$route.query.market}}).then(function(response){
                vm.ticker = response.data.data.ticker;
                if(document.getElementById('buyUnitPrice').value == '') {
                	document.getElementById('buyUnitPrice').value = vm.ticker.lastPrice;
                }
                if(document.getElementById('sellUnitPrice').value == '') {
                	document.getElementById('sellUnitPrice').value = vm.ticker.lastPrice;
                }
            });
       	},
        getLengthDepth(){
        	var vm = this;
        	axios.get(this.commonApi.api.getLengthDepth, {params: {'length':vm.depthList.curdepth,'symbol':vm.symbol,'a':vm.a.n}}).then(function(response){
		        var res = response.data;
		        if(res.code == 200){
		        	vm.depthList.buyList = res.data.buy;
		        	vm.depthList.sellList = res.data.sell;
		        }
		    })
        },
        changeDepth(i){
        	this.depthList.curdepth = i || this.depthList.dLength[0];
        	this.getLengthDepth()
        },
        changeA(l){
        	this.a.str = l[0] || this.list[1][0];
        	this.a.n = l[1] || this.list[1][1];
        	this.getLengthDepth()

        }
	}
}
</script>

<style>
</style>