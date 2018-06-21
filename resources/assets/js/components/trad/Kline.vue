<template>
<div class="tradcontainer">
	<div id="TradInner" class="kline-inner">
		<iframe id="Kframe" class="kframe" :src="'/kline?symbol=' + $route.query.market" frameborder="0" border="0" marginwidth="0" marginheight="0" scrolling="no"allowtransparency="yes"/>
		
		<!--menu-->
		<div class="bk-kMarket">
		    <div class="trad-form">
		        <div class="bk-table">
		            <div class="bk-cell list">
		                <div class="bk-tabList" id="bkEntrustTab">
		                    <div class="bk-tabList-hd clearfix">
		                        <div class="btn-group bk-btn-group" role="group">
		                            <a class="btn" :class="(entrustToggle == 1)&&'active'" role="button" tab-target="1" @click="entrustTabClick">{{$t('cmn.limitedPrice')}}{{$t('cmn.entrust')}}</a>
		                            <a class="btn " :class="(entrustToggle == 2)&&'active'" role="button" tab-target="2" @click="entrustTabClick">{{$t('cmn.history')}}{{$t('cmn.entrust')}}</a>
		                        </div>
		                        <!--$route.query.market-->
	                        	<router-link class="pull-right text-primary" :to="{path:'/financial/entrust', query:{market:market}}"><i class="fa fa-calendar fa-fw"></i>{{$t('cmn.more')}}{{$t('cmn.record')}}</router-link>
		                    </div>
		                    <div class="bk-tabList-bd" v-if="user.authenticated">
		                        <div class="bk-entrust"  v-loading="!entrustList.loaded"  bg="rgba(0,0,0,0.5)" v-show="entrustToggle == 1">
		                            <table class="table table-striped table-bordered table-hover">
		                                <thead>
		                                    <tr>
		                                        <th style="">{{$t('cmn.entrust')}}{{$t('cmn.time')}}</th>
		                                        <th style="text-align:left;">{{$t('cmn.entrust')}}{{$t('cmn.quantity')}}/({{account.mainBalance.code}})</th>
		                                        <th style="text-align:left;">{{$t('cmn.entrust')}}{{$t('cmn.price')}}/{{$t('financial.priceOfToday')}}({{account.exchangeBalance.code}})</th>
		                                        <th style="">{{$t('cmn.bargain')}}{{$t('cmn.total')}}({{account.exchangeBalance.code}})</th>
		                                        <th style="">{{$t('cmn.state')}}</th>
		                                        <!--<th style="">订单来源</th>-->
		                                        <th >{{$t('cmn.operation')}}</th><!--<a role="button" id="batchCancel">[批量撤单]</a>-->
		                                    </tr>
		                                </thead>
		                                <tbody id="entrustRecord">
		                                	<tr v-for="item in entrustList.list">
		                                		<td>{{item.createdAt}}</td>
		                                		<td>
		                                			<span class="text-increase" v-if="item.type == 'buy'">{{$t('trad.buy')}}</span>
		                                			<span class="text-reduce" v-else>{{$t('trad.sell')}}</span>
		                                			{{item.num}} / {{item.successfulNum}}
		                                		</td>
		                                		<td>{{item.price}} / {{item.successfulPrice}}</td>
		                                		<td>{{item.volume}}</td>
		                                		<td>{{item.statusStr}}</td>
		                                		<td><a class="text-primary" @click="orderCancel(item.id)">{{$t('cmn.revocation')}}</a></td>
		                                	</tr>
		                                </tbody>
		                            </table>
		                        </div>
		                        
		                        <div class="bk-entrust"  v-loading="!historyList.loaded"  bg="rgba(0,0,0,0.5)" v-show="entrustToggle == 2">
		                            <table class="table table-striped table-bordered table-hover">
		                                <thead>
		                                    <tr>
		                                        <th style="">{{$t('cmn.entrust')}}{{$t('cmn.time')}}</th>
		                                        <th style="text-align:left;">{{$t('cmn.entrust')}}{{$t('cmn.quantity')}}/({{account.mainBalance.code}})</th>
		                                        <th style="text-align:left;">{{$t('cmn.entrust')}}{{$t('cmn.price')}}/{{$t('financial.priceOfToday')}}({{account.exchangeBalance.code}})</th>
		                                        <th style="">{{$t('cmn.bargain')}}{{$t('cmn.total')}}({{account.exchangeBalance.code}})</th>
		                                        <th style="">{{$t('cmn.state')}}</th>
		                                    </tr>
		                                </thead>
		                                <tbody id="entrustRecord">
		                                	<tr v-for="item in historyList.list">
		                                		<td>{{item.createdAt}}</td>
		                                		<td>
		                                			<span class="text-increase" v-if="item.type == 'buy'">{{$t('trad.buy')}}</span>
		                                			<span class="text-reduce" v-else>{{$t('trad.sell')}}</span>
		                                			{{item.num}} / {{item.successfulNum}}
		                                		</td>
		                                		<td>{{item.price}} / {{item.successfulPrice}}</td>
		                                		<td>{{item.volume}}</td>
		                                		<td>{{item.statusStr}}</td>
		                                	</tr>
		                                </tbody>
		                            </table>
		                        </div>
		                    </div>
		                    <div class="bk-tabList-bd" v-else>
		                    	<div class="no-data text-center">
									{{$t('trad.afterYou')}} <router-link class="text-primary" to="/login">{{$t('cmn.login')}}</router-link> {{$t('trad.or')}} <router-link class="text-primary" to="/regist">{{$t('cmn.regist')}}</router-link>
		                    	</div>
		                    </div>
		                </div>
		            </div>
		            <div class="bk-cell item" v-loading="!buyForm.loaded"  bg="rgba(0,0,0,0.5)">
		                <div class="bk-buy-form" >
		                    <form role="form" id="buyForm" class="form-horizontal" method="post" action="" autocomplete="off">
		                        <input type="hidden" name="buyType" id="buyType" value="0">
		                        <input type="hidden" name="moneyType" id="moneyType" value="">
		                        <input type="hidden" name="coinType" id="coinType" value="1">
		                        <div class="form-hd has-feedback clearfix">
		                        	<label class="control-label static pull-left">{{$t('trad.buying')}}{{account.mainBalance.code}}</label>
		                            <label class="control-label static pull-right">{{$t('financial.usable')}}{{$t('punctuation.colon')}}
		                                <span id="canUseMoney" @click="formBalanceClick('buy')" class="text-increase" style="cursor:pointer;">{{ account.exchangeBalance.balance }}</span> {{ account.exchangeBalance.code }}
		                            </label>
		                        </div>
		                        <div id="buyDefaultForm">
		                            <div class="form-group has-feedback">
		                                <label class="control-label" for="buyUnitPrice">{{$t('trad.buying')}}{{$t('cmn.price')}}{{ account.exchangeBalance.code }}</label>
		                                <div class="input-group">
		                                    <input type="text" class="form-control form-second" id="buyUnitPrice" name="buyUnitPrice" placeholder="--" @keyup="amountInput" @focus="amountInput">
		                                </div>
		                            </div>
		                            <div class="form-group has-feedback">
		                                <label class="control-label" for="buyNumber">{{$t('trad.buying')}}{{$t('units.quantity')}}{{ account.mainBalance.code }}</label>
		                                <div class="input-group">
		                                    <input type="text" class="form-control form-second" id="buyNumber" placeholder="--" name="buyNumber" @keyup="amountInput">
		                                </div>
		                            </div>
		                            <!-- 买单滑动杆 -->
		                            <div class="form-group">
										<div class="drag-box clearfix">
											<dragbar :percent.sync="buyForm.prcn" viewcolor="red" @drag="buydrag"></dragbar>
											<div class="u-text "><span>{{buyForm.prcnView}}%</span></div>
										</div>
									</div>
		                            <div class="form-group has-feedback">
		                                <label class="control-label static" for="realBuyAccount">{{$t('trad.plan')}}{{$t('trad.volume')}}{{$t('punctuation.colon')}}
		                                    <span class="text-increase" id="realBuyAccount">{{ buyForm.volume }}</span> {{ account.exchangeBalance.code }}
	                                    </label>
		                            </div>
		                            <div class="form-group">
		                                <button id="buyBtn" name="buyFormBtn" type="button" class="btn btn-increase btn-block btn-hg" @click="tradComfirm" v-if="user.authenticated">{{$t('trad.atOnceBuy')}}</button>
		                                <router-link id="buyBtn" name="buyFormBtn" type="button" class="btn btn-increase btn-block btn-hg" to="/login" v-else>{{$t('cmn.please')}}{{$t('cmn.login')}}</router-link>
		                            </div>
		                        </div>
		                    </form>
		                </div>
		            </div>
		            <div class="bk-cell item" v-loading="!sellForm.loaded" bg="rgba(0,0,0,0.5)">
		                <div class="bk-sell-form">
		                    <form role="form" id="sellForm" class="form-horizontal" method="post" action="" autocomplete="off">
		                        <div class="form-hd has-feedback clearfix">
		                        	<label class="control-label static pull-left">{{$t('trad.sale')}}{{account.mainBalance.code}}</label>
		                            <label class="control-label static pull-right">{{$t('financial.usable')}}{{$t('punctuation.colon')}}
		                                <span id="canUseCoin" @click="formBalanceClick('sell')" class="text-reduce" style="cursor:pointer;">{{ account.mainBalance.balance }}</span> {{ account.mainBalance.code }}
		                            </label>
		                        </div>
		                        <div id="sellDefaultForm">
		                            <div class="form-group has-feedback">
		                                <label class="control-label" for="sellUnitPrice">{{$t('trad.sale')}}{{$t('cmn.price')}}{{ account.exchangeBalance.code }}</label>
		                                <div class="input-group">
		                                    <input type="text" class="form-control form-second" id="sellUnitPrice" name="sellUnitPrice" placeholder="--" @keyup="amountInput" @blur="amountInput"/>
		                                </div>
		                            </div>
		                            <div class="form-group has-feedback">
		                                <label class="control-label" for="sellNumber">{{$t('trad.sale')}}{{$t('units.quantity')}}{{ account.mainBalance.code }}</label>
		                                <div class="input-group">
		                                    <input type="text" class="form-control form-second" id="sellNumber" placeholder="--" name="sellNumber" @keyup="amountInput" />
		                                </div>
		                            </div>
		                            <div class="form-group">
										<div class="drag-box clearfix">
											<dragbar :percent.sync="sellForm.prcn" viewcolor="green" @drag="selldrag"></dragbar>
											<div class="u-text "><span>{{sellForm.prcnView}}%</span></div>
										</div>
									</div>
		                            <div class="form-group has-feedback">
		                                <label class="control-label static" for="realSellAccount">{{$t('trad.plan')}}{{$t('trad.volume')}}{{$t('punctuation.colon')}}
		                                    <span class="text-reduce" id="realSellAccount">{{ sellForm.volume }}</span> {{ account.exchangeBalance.code }}
	                                    </label>
		                            </div>
		                            <div class="form-group">
		                                <button id="sellBtn" name="sellFormBtn" type="button"  class="btn btn-reduce btn-block btn-hg" @click="tradComfirm" v-if="user.authenticated">{{$t('trad.atOnceSale')}}</button>
		                                <router-link id="sellBtn" name="sellFormBtn" type="button"  class="btn btn-reduce btn-block btn-hg" to="/login" v-else>{{$t('cmn.please')}}{{$t('cmn.login')}}</router-link>
		                            </div>
		                        </div>
		                    </form>
		                </div>
		            </div>
		        </div>
		    </div>
		</div>
	
	<!--menu end-->
	</div>
	<!--:params="user"-->
	<dailog boxid="tradingPwd" :boxtitle="$t('cmn.trad')+$t('cmn.password')" boxsize="sm" @modalcallback="tradSubmit" tradingType=""></dailog>
</div>	
</template>

<script>
import {mapState} from 'vuex'
import dragbar from '../common/dragbar'
import floatMath from '../common/floatMath.js'
import hacStorage from '../common/storage.js'
import dailog from '../common/dailog/dailog'
export default{
	components:{
		dailog
	},
	data(){
		return{
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
			buyForm:{
				isBuy:1,
				amount:0,
				volume:0,
				loaded:true,
				prcn:0,
				prcnView:'0.00'
			},
			sellForm:{
				isBuy:0,
				amount:0,
				volume:0,
				loaded:true,
				prcn:0,
				prcnView:'0.00'
			},
			entrustList:{
				list:'',
				loaded:false
			},
			historyList:{
				list:'',
				loaded:false
			},
			entrustToggle:1,
			market:'',
			timecount:null
		}
	},
	mounted(){
		var vm = this;
		var _Wrap_ = document.getElementById('TradInner');
		var _Frame_ = document.getElementById('Kframe');
		vm.market = this.$route.query.market;
		vm.getAccount();
		vm.getEntrustTrading();		//委托列表
		vm.getEntrustCompleted();	//历史列表
		vm.timecount = setInterval(function(){
			vm.getEntrustTrading();
			vm.getEntrustCompleted();
		}, 10000)
		
		_Frame_.style.height = _Wrap_.offsetHeight - $('.bk-kMarket').height() - 4 + 'px';
		$(window).on('resize',function(){
			_Frame_.style.height = _Wrap_.offsetHeight - $('.bk-kMarket').height() - 4 + 'px';
		});
		
		window.hacWebSocket = {
			wsUrl: '/websocket',	//websocket接口
			openWebSocket: false,	//推送状态变量
			openDishUpdata: true,	//开启盆口增量更新
			isZip:	false,			//是否开启压缩
			isBinary: false,		//是否开启二进制
			sendMsgInterval: null,	//消息定时器
			channelManage: null		//频道管理
		}
		
		hacWebSocket.init = function(){
			if(vm.$route.path !== 'trad'){
				return false;
			}
			var _this = this;
	        //清空频道管理器
	        _this.channelManage = {};
	        //清除消息定时器
	        window.clearInterval(_this.sendMsgInterval);
				//建立握手
	        if ('WebSocket' in window) {
	            _this.websocket = new WebSocket(_this.wsUrl);
	        } else if ('MozWebSocket' in window) {
	            _this.websocket = new MozWebSocket(_this.wsUrl);
	        } else {
	            _this.openWebSocket = false;
	            console.log('Your browser does not support websocket.')
	            return false;
	        }
	        
	        //当Browser与WebSocketServer链接成功后,会触发onopen消息;
	        _this.websocket.onopen = function(event) {
	            _this.openWebSocket = true;
	            _this.onOpen && _this.onOpen(event);
	            window.onbeforeunload =function(){
	                _this.websocket && _this.websocket.close();
	            }
	        };
	        //当Browser接收到WebSocketServer发送过来的数据时,会触发onmessage
	        _this.websocket.onmessage = function(event) {
	            _this.onMessage && _this.onMessage(event);
	        };
	        //当连接失败，发送、接收数据失败或者处理数据出现错误时,会触发onerror
	        _this.websocket.onerror = function(event) {
	            _this.openWebSocket = false;
	            _this.onError && _this.onError(event);
	        };
	        //当Browser接收到WebSocketServer端发送的关闭连接请求时,会触发onclose
	        _this.websocket.onclose = function(event) {
	            _this.openWebSocket = false;
	            _this.onClose && _this.onClose(event);
	        };
	        
	        //处理二进制数据
		    hacWebSocket.unBinary = function (datas, callback) {
		        var doCallback = function (result) {
		            //直接处理为json对象
		            if(result.indexOf("(") != 0){
		                result = eval("("+result+")");
		            }else{
		                result = eval(result);
		            }
		            if(typeof callback == 'function'){
		                return callback(result);
		            }else{
		                return result;
		            }
		        }
		        if (datas instanceof Blob) {
		            var reader = new FileReader();
		            reader.readAsText(datas);
		            reader.onloadend  = function(evt){
		                if(evt.target.readyState == FileReader.DONE){
		                    doCallback(evt.target.result);
		                }else{
		                    doCallback(datas);
		                }
		            }
		        }else{
		            doCallback(datas);
		        }
		    }
		    
		    //发送消息队列
		    hacWebSocket.sendMessage = function () {
		        var _this = this;
		        //console.log(_this.websocket.readyState, _this.websocket.OPEN, _this.openWebSocket)
		
	            for(var key in _this.channelManage){
	                var channel = _this.channelManage[key];
	                //如果消息不为空且未发送过的频道才推送
	                if(channel.message != "" && !channel.sended){
	                    //加入自定义参数
	                    channel.message.isZip = _this.isZip;
	                    channel.message.binary = _this.isBinary;
	                    //发送消息
	                    _this.websocket.send(JSON.stringify(channel.message));
	                    //console.log('send message succses ' + JSON.stringify(channel.message));
	                    //标识为已处理
	                    channel.sended = true;
	                }
	            }
		        if(!_this.openWebSocket){
		            console.log('reconnect websocke.', _this.openWebSocket)
		            _this.init();
		        }
		    }
		    
		    //处理返回数据的方法
		    hacWebSocket.dealMessage = function (json) {
		        var _this = this.channelManage;
		        var result = json;
		        var channel = result.channel;//推送返回频道处理
		        if(!channel){
		            channel = result[0].channel;
		        }
		        if(channel.indexOf("_cny_lasttrades") != -1){
		            //console.log(channel);
		            _this['dealRecord'].method && _this['dealRecord'].method(result);
		        }
		        if(channel.indexOf("_cny_depth") != -1){
		            //console.log(channel);
		            _this['dishData'].method && _this['dishData'].method(result);
		        }
		        if(channel.indexOf("_kline_") != -1){
		            var ifr = document.getElementById('Kframe');
		            var win = ifr.window || ifr.contentWindow;
		            win.updateKlineData(result); // 调用iframe中的a函数
		            //console.log(channel);
		            //_this['klineData'].method && _this['klineData'].method(result);
		        }
		    }
		    
		    hacWebSocket.onOpen = function (event) {
		        var _this = this ;
		        console.log('websocket init success.')
		        //处理消息队列的定时器
		        _this.sendMsgInterval = setInterval(function () {
		            _this.sendMessage();
		        },1000);
		    };
		
		    hacWebSocket.onMessage = function (event) {
		        //处理收到的数据
		        //console.log(event.data);
		        var datas = event.data;
		        var _this = this;
		        _this.unBinary(datas, function (json) {
		            //console.log(json);
		            _this.dealMessage(json);
		        })
		
		    }
		    //初始化
//		    hacWebSocket.init(); 
		}
		
	},
	computed:{
		...mapState({
            user: state => state.AuthUser
        }),
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
			vm.buyForm.prcnView = (floatMath.mul(rate, 100)).toFixed(2);
		},
		sellFormPrcn(rate){
			var vm = this;
			vm.sellForm.prcnView = (floatMath.mul(rate, 100)).toFixed(2);
		}
	},
	methods:{
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
				//拖动条计算
				
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
       			formObj = vm[ftype+'Form'],
       			nowTime = new Date().getTime(),
       			pswstorage = hacStorage.getItem('ts');
       			console.log(document.querySelector("#"+ftype+"Number").value, vm.account.mainBalance.decimals)
       		if(parseFloat(formObj.volume).toFixed(vm.account.exchangeBalance.decimals) <= 0 || parseFloat(document.querySelector("#"+ftype+"Number").value).toFixed(vm.account.mainBalance.decimals) <= 0 ){
            	vm.$comfirmbox({ content: vm.$t('trad.formalError') });
            	return false;
           	}
       		if(vm.user.tradeOption == "0"){
       			vm.tradSubmit({type_:ftype});
       			return false;
       		}else if(vm.user.tradeOption == "1"){
       			var judage = (!pswstorage || ((nowTime - pswstorage)/(1000*3600)).toFixed(0) > 1);
       		}else{
       			var judage = true;
       		}
//     		
       		if(judage){
 				$('#tradingPwd-dailog').attr({'tradingType':ftype}).modal('show');
       		}else{
       			vm.tradSubmit({type_:ftype})
       		}
       		
       },
        tradSubmit(obj){	//表单提交
            let vm = this,
            	ftype = obj.type_,
            	pwd = obj.pwd,
            	formObj = vm[ftype+'Form'];
            vm[ftype+'Form'].loaded = false;
            axios.post(vm.commonApi.api.tradesEntrust, {
            	market:vm.$route.query.market,
            	isBuy:formObj.isBuy,
            	unitPrice:document.querySelector("#"+ftype+"UnitPrice").value,	
            	number:document.querySelector("#"+ftype+"Number").value,
            	tradeCode:pwd
            }).then(function(response){
                var _response = response.data;
                if(_response.code == 200) {
//               	vm.$comfirmbox({ content:_response.message });
                 	vm.getAccount(function(){
                 		vm[ftype+'Form'].loaded = true;
                 	});	//更新余额
					vm.getEntrustTrading();	//更新订单列表
				}else if(_response.code == 403){
					$('#tradingPwd-dailog').attr({'tradingType':ftype}).modal('show');
                 		vm[ftype+'Form'].loaded = true;
				}else{
					vm.$comfirmbox({ content:_response.message });
                 		vm[ftype+'Form'].loaded = true;
				}
				
				vm.depthAndTradesReflash();	//测试用事件
				
            }).catch(function(response){
            	vm[ftype+'Form'].loaded = true;
            });
       	},
       	depthAndTradesReflash(){
       		var vm = this;
       		var i = document.getElementById("Kframe");
	        var g = i.window || i.contentWindow;
	        var symbol_ = vm.$route.query.market;
	        symbol_ = symbol_.replace('_','').toLowerCase();
        	axios.get('/depth?symbol='+symbol_+'&lastTime=0&length=10&depth=0').then(function(a){
        		var  r = a.data;
        		if(a.status == 200){
        			g.kline.updateDepth(r["return"])	//挂单数据
        		}
        	})
//	            g.kline.pushTrades(a.data);
//	            g.kline.klineTradeInit = true;
//	            g.clear_refresh_counter();
//	            
       	},
       	getEntrustTrading(market){	//委托列表
			var vm = this;
			axios.get(this.commonApi.api.getEntrust, {params: {'symbol':vm.$route.query.market,'status':'trading'}}).then(function(response){
               var res = response.data;
                if (res.code == 200) {
                	// vm.entrustList.loaded = true;
                	vm.entrustList.list = res.data.list;
                	vm.getAccount();	//更新余额
                }
                vm.entrustList.loaded = true;
            })
		},
		getEntrustCompleted(){	//历史列表
			var vm = this;
			axios.get(this.commonApi.api.getEntrust, {params: {'symbol':vm.$route.query.market,'status':'past'}}).then(function(response){
                var res = response.data;
                if (response.data.code == 200) {
                	// vm.historyList.loaded = true;
                	vm.historyList.list = response.data.data.list;
                }
                vm.historyList.loaded = true;
            })
		},
		orderCancel(id){	//取消订单
        	var vm = this;
        	vm.$comfirmbox({
        		content: vm.$t('trad.cancelOrder'),
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
	            
	            vm.depthAndTradesReflash();	//测试用事件
	            
            }).catch(function(){
            	return;
            })
    	},
    	entrustTabClick(e){
    		let vm = this;
    		let tag = e.target.getAttribute('tab-target');
    		let timer;
    		switch(tag){
    			case "1":
    				this.entrustToggle = 1;
//					vm.getEntrustTrading()
    				break;
    			case "2":
    				this.entrustToggle = 2;
    				clearInterval(timer)
//					vm.getEntrustCompleted()
    				break;
    		}
    		
    	},
    	formBalanceClick(type_){
    		var vm = this;
       		if(type_ == 'buy'){
       			vm.buydrag(1);
				vm.buyForm.prcn = 1
       		}else{
       			vm.selldrag(1);
       			vm.sellForm.prcn = 1
       		}
    	},
//  	getUserInfo(){
//          var vm = this;
//          axios.get(this.commonApi.api.getUserInfo).then(function(response){
//               vm.user = response.data.data;
//          });
//      },
   	},
   	destroyed(){
   		var vm = this;
   		if(vm.timecount){
   			clearInterval(vm.timecount);
   		}
   	}
}
</script>
