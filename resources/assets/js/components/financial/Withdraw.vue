<template>
	<div class="ucontainer">
		<div class="panel">
    		<h3 class="p-title fs-16"><span class="n">{{$tc('financial.extractCoin')}}</span></h3>
    		<div class="charge-wrap row">
    			<div class="col-md-12">
    				<div class="coin-list clearfix">
    					<a v-for="coin in coins" class="item" :class="coin.coin == curCoin&&'active'" v-if="coin.enableWithdraw == 1" @click="coinCheck(coin.coin)">
    						<img :src="coin.logo" v-if="coin.logo" />{{coin.coin}}
    					</a>
    				</div>
    				<!--提币-->
    				<div class="withdraw-wrap">
    					<div class="step-box text-center fs-12 row">
    						<div class="item active" @click="nextStep(1)">
    							{{$tc('financial.stepTipsTitle', 1)}}
    						</div>
    						<div class="item" :class="(curstep == 2)&&'active'">
    							{{$tc('financial.stepTipsTitle', 2)}}
    						</div>
    					</div>
    					<div class="step-1 mb20" v-if="curstep == 1">
	    					<div class="u-tb mb20">
	    						<div class="row tb-hd text-center">
									<div class="col-xs-1">{{$tc('cmn.remark')}}</div>
									<div class="col-xs-3">{{$tc('cmn.remark')}}</div>
									<div class="col-xs-5">{{$tc('financial.extractCoin')}}{{$tc('cmn.address')}}</div>
									<div class="col-xs-2">{{$tc('cmn.operation')}}</div>
								</div>
								<div class="tb-ctn row" v-loading="!addresses.loaded">
									<ul class="tb-list text-center" v-for="(address, index) in addresses.list" v-if="addresses.list.length">
										<li class="clearfix" @click="checkAddress(address,index)">
											<div class="col-xs-1"><em class="adrs-check-em" :class="(addresses.cur === index)&&'active'"><span></span></em></div>
											<div class="col-xs-3">{{address.name}}</div>
											<div class="col-xs-5">{{address.address}}</div>
				                            <div class="col-xs-2"><a @click="deleteAddress(address.id)">{{$tc('cmn.delete')}}</a></div>
										</li>
									</ul>
									<div  v-if="!addresses.list.length"  class="no-data text-center">{{$tc('cmn.noRecords')}}</div>
									<div class="add-adrs text-center">
										<a @click="checkCanAddress()">
											<i class="fa fa-plus-circle fs-18"></i>
											{{$tc('financial.addWithdrawAddress')}}
										</a>
									</div>
								</div>
							</div>
							<a class="btn btn-primary submit-btn" @click="nextStep(2)">{{$tc('financial.inputWithdrawInfo')}}</a>
						</div>
						
						<div class="step-2" v-if="curstep == 2">
							<form action="javascript:;" :model="withdrawForm" ref="withdrawForm" v-if="user.tradeCode == 1 && user.googleSecret == 1 && user.cerBaseStatus == 1">
								<input type="hidden" v-model="withdrawForm.fee" />
								<input type="hidden" v-model="withdrawForm.id" />
								<div class="fillout">
									<div class="form-group">
										<p>{{$tc('financial.withdrawTo')}}</p>
										<input class="form-control" readonly="readonly" v-model="withdrawForm.address" />
									</div>
									<div class="form-group">
										<p>{{$tc('financial.withdrawNumber')}}({{$tc('financial.usable')}}{{$tc('punctuation.colon')}}{{walletData.balance}} {{curCoin}})</p>
										<div class="drag-box clearfix" v-if="walletData.balance">
											<dragbar :percent.sync="withDrawPrc" @drag="amountDrag"></dragbar>
											<div class="u-text ">
												<!--提取<span>{{withdrawForm.amount}}</span>个-->
												<span class="ha">{{$tc('cmn.extract')}}</span>
												<input class="form-control" type="text" v-model="withDrawAmount"/>
												<span class="unit">{{$tc('units.ge')}}</span>
											</div>
										</div>
									</div>
									<div class="form-group">
							    		<p>{{$tc('cmn.network')}}{{$tc('cmn.fee')}} {{withdrawForm.fee}}</p>
							    	</div>
							    	<div class="form-group">
										<input class="form-control" type="password" :placeholder="$tc('cmn.trad')+$tc('cmn.password')" v-model="withdrawForm.tradeCode"/>
									</div>

                                    <div class="form-group" v-if="user.withdrawalOption == 2 || user.withdrawalOption == 3">
                                        <div class="group-col">
                                            <input class="form-control" v-model="withdrawForm.code" name="code" :placeholder="$t('member.verificationCode')" />
                                            <a class="col-btn" @click="send" v-if="!vcount">{{$tc('member.getVerCode',1)}}</a>
                                            <span class="col-btn text-gray-light"  v-if="vcount">{{vcount}}{{$tc('member.reGet',1)}}</span>
                                        </div>
                                    </div>
                                    <div class="form-group" v-if="user.withdrawalOption == 1 || user.withdrawalOption == 3">
                                        <input class="form-control" v-model="withdrawForm.googleCode" name="google_code" :placeholder="$t('cmn.please')+$t('cmn.input')+$t('cmn.google')+$t('cmn.code')" />
                                    </div>

									<a class="btn btn-primary submit-btn" v-loading="!okloaded" @click="success">{{$tc('cmn.confirm')}}{{$tc('cmn.submit')}}</a>
								</div>
							</form>
                            <div class="modal-body" v-else>
                                <div class="row tb-hd text-center" v-if="user.tradeCode != 1"><a href="/user">{{$tc('cmn.set')}}{{$tc('cmn.trad')}}{{$tc('cmn.password')}}</a></div>
                                <div class="row tb-hd text-center" v-if="user.googleSecret != 1"><a href="/user">{{$tc('cmn.set')}}{{$tc('cmn.google')}}{{$tc('cmn.password')}}</a></div>
                                <!-- <div class="row tb-hd text-center" v-if="user.mobile == ''"><a href="/user">{{$tc('member.mustBindingMobile')}}</a></div> -->
                                <div class="row tb-hd text-center" v-if="user.cerBaseStatus != 1"><a href="/user">{{$tc('member.mustCertification')}}</a></div>
                            </div>
						</div>
    				</div>
    				<!--提币end-->
    			</div>
    		</div>
		</div>
		
		<!--充值记录-->
		<div class="panel">
			<h3 class="p-title fs-16">
				<span class="n">{{curCoin}}{{$tc('financial.extractCoin')}}{{$tc('cmn.record')}}</span>
				<span class="n ml10" style="cursor:pointer;" @click="getWithdrawData('')"><i class="fa fa-refresh text-gray-light"></i></span>
			</h3>
			<div class="u-tb" v-loading="!records.loaded">
				<div class="row tb-hd text-center">
					<div class="col-xs-1">{{$t('cmn.time')}}</div>
                    <div class="col-xs-3">{{$tc('cmn.receive')}}{{$tc('cmn.address')}}</div>
					<div class="col-xs-2">{{$tc('cmn.manage')}}{{$tc('cmn.time')}}</div>
					<div class="col-xs-1">{{$tc('cmn.state')}}</div>
					<div class="col-xs-2">{{$tc('cmn.money')}}</div>
					<div class="col-xs-2">{{$tc('cmn.actual')}}{{$tc('cmn.money')}}</div>
                    <div class="col-xs-1">{{$tc('cmn.operation')}}</div>
				</div> 
				<div class="tb-ctn row">
					<ul class="tb-list text-center" v-if="records.list.length">
						<li class="clearfix" v-for="(record, index) in records.list">
							<div class="col-xs-1">{{record.createdAt}}</div>
                            <div class="col-xs-3">
                                {{record.address}}<br/>
                                <span v-if="record.txid"><b style="font-weight:700;">txid:</b>{{record.txid}}</span>
                            </div>
							<div class="col-xs-2">{{record.doneAt}}</div>
                            <div class="col-xs-1">{{record.statusStr}}</div>
							<div class="col-xs-2">{{record.sumAmount}}</div>
							<div class="col-xs-2">{{record.amount}}</div>
                            <div class="col-xs-1 handler" v-if="record.txid"><a target="_blank" :href="record.url">{{$tc('cmn.lookOver')}}</a></div>
                            <div class="col-xs-1 handler" v-else>
                            	<!--秒后可重新发送-->
                            	
                            	<a @click="sendConfirmEmail($event, index)" v-if="!record.sended">{{$tc('cmn.send')}}{{$tc('cmn.email')}}</a>
                            	<span class="text-gray-light" v-if="record.sended">{{record.sended}}</span>
                            </div>
						</li>
					</ul>
					<div v-if="!records.list.length" class="no-data col-xs-12 text-center">{{$tc('cmn.noRecords')}}</div>
				</div>
				<pager :curnum="records.paginate.currentPage" :lastPage="records.paginate.lastPage" @skip="getWithdrawData"></pager>
			</div>
		</div>
		
		<dailog boxid="withdraw" :boxtitle="$tc('cmn.add')+$tc('financial.extractCoin')+$tc('cmn.address')" boxsize="sm" :params="user" @modalcallback="getAddresses"></dailog>
		<dailog boxid="withdrawdelete" :boxtitle="$tc('cmn.delete')+$tc('financial.extractCoin')+$tc('cmn.address')" boxsize="sm" :params="walletParams" @modalcallback="getAddresses"></dailog>
		
	</div>
</template>

<script>
    import breadHead from '../common/breadHead'
    import dailog from '../common/dailog/dailog'
    import floatMath from '../common/floatMath.js'
    function timeCount(star, end, count){
    	var timer = setInterval(function(){
    		if(count <= 0){
    			end(count);
    			clearInterval(timer);
    		}else{
    			count--;
    			star(count);
    		}
    	},1000)
    }
    
    export default {
        components: {
        	dailog,
            breadHead
        },
        data() {
          return {
            coins:'',
            records:{list:'', loaded:false, paginate:{currentPage:''}},		//交易记录
            curCoin:'',		//当前币
            curstep:1,		//当前步骤
            addresses:{list:'', cur:'', loaded:false},	//钱包地址
            walletParams:'',//删除提币地址参数
            withdrawForm:{
                id:'',
                address:'',
                amount:'',
                fee:'',
                tradeCode:'',
                googleCode:'',
                code:''
            },
            withDrawPrc:0,
            withDrawAmount:0,
            walletData:'',		//钱包信息
            withdrawFeeList:'',
            vcount:'',
            user:'',
            successOff:'',
            okloaded:true,
          };
        },
        mounted(){
            var vm = this;

            // 获取所有数字货币
            axios.get(this.commonApi.api.marketCoins).then(function(response){
             	var res = new Object(response.data.data);
                vm.coins = res.list;
            })

            vm.getUserInfo()
			
        },
        activated(){
        	this.curCoin = this.$route.query.cointype || 'BTC';
        },
        watch:{
        	curCoin(){
        		var vm = this;
        		//参数更新
        		vm.resetData();
        		// 钱包提币地址
	            vm.getAddresses();
	            
	            vm.getWithdrawData();
        	},
        	withDrawAmount(n){
        		var val = n;
        		if(parseFloat(val) > parseFloat(this.walletData.balance)){
        			this.withDrawAmount = this.walletData.balance;
        			return;
        		}
        		this.withDrawAmount = this.withdrawForm.amount = val.replace(/[^\d\.]/g,'').replace(/^\./g,'').replace('.','###').replace(/\./g,'').replace('###','.');
        		this.withDrawPrc = floatMath.res(floatMath.div(this.withDrawAmount,this.walletData.balance), 2)
        	}
        },
        methods:{
        	coinCheck(str){
        		this.curCoin = str;
                this.user.curCoin = str;
        	},
        	deleteAddress(id){
        		var vm = this;
        		axios.get(this.commonApi.api.getWithdrawAddress, {params: {'id':id}}).then(function(response){
        			var res = new Object(response.data);
        			if(res.code == 200) {
        				vm.walletParams = res.data;
                        vm.walletParams.coin = vm.curCoin;
                        $('#withdrawdelete-dailog').modal('show')
        			} else {
        				vm.$comfirmbox({ title:'', content:vm.message })
        			}
	            })
        	},
        	checkAddress(address,index){
        		this.addresses.cur = index;
        		this.withdrawForm.id = address.id;
        		this.withdrawForm.address = address.address;
        	},
        	nextStep(index){
        		var vm = this;
        		if(this.withdrawForm.address){
        			vm.curstep = index;
        			
        			//获取钱包资产
        			axios.get(this.commonApi.api.walletAccount, {params:{'coinType':vm.curCoin}}).then(function(response){
        				vm.walletData = response.data.data;
                        vm.withdrawForm.amount = response.data.data.balance * vm.withDrawPrc;
        				vm.withdrawForm.fee = response.data.data.coinFee;
        			})
        			
        			axios.get(this.commonApi.api.withdrawFee, {params:{'coinType':vm.curCoin}}).then(function(response){
        				vm.withdrawFeeList = response.data.data;
        			})
        		}else{
        			this.$comfirmbox({ title:'', content: vm.$t('cmn.select')+vm.$t('cmn.wallet')+vm.$t('cmn.address') })
        		}
        	},
            send:function(){
                var vm = this
                vm.vcount = 60;
                axios.get(this.commonApi.api.sendVerifyCode).then(function(response){
                    var res = new Object(response.data);
                    vm.$comfirmbox({ 
                        content:res.message,
                        status:res.code
                    });
                    setInterval(() => {
                        if(vm.vcount - 1 > 0)
                            vm.vcount--;
                        else
                            vm.vcount = 0;
                    },1000)
                })
                return false;
            },
        	success(){
        		var vm = this;
        		if(vm.successOff == true)
                    return false;
                vm.successOff = true
                vm.okloaded = false
        		axios.post(this.commonApi.api.withdrawApply, {
        			address:vm.withdrawForm.address, 
        			amount:vm.withdrawForm.amount, 
                    tradeCode:vm.withdrawForm.tradeCode, 
                    googleCode:vm.withdrawForm.googleCode, 
        			code:vm.withdrawForm.code, 
        			coinType:vm.curCoin 
        		}).then(function(response){
                    vm.successOff = false
                    vm.okloaded = true
                    var res = new Object(response.data);
                    vm.$comfirmbox({ content:res.message, status:res.code })
                    if(res.code == 200) {
                        vm.getWithdrawData()
                        vm.nextStep(1)
                    }
                    
        		})
        	},
        	getWithdrawData(num){
        		var vm = this,
        			page = num || 1
        		vm.records.loaded = false;
        		vm.records.list = '';
	            // 充值记录
	            axios.get(this.commonApi.api.withdrawRecords, {params: {'coinType':vm.curCoin, page:page}}).then(function(response){
	            	vm.records.loaded = true;
	            	if(response.data.code == 200){
						vm.$merge(vm.records, response.data.data);
						vm.records.list = response.data.data.list;
					}else{
						vm.records.list = '';
	                	vm.records.paginate.lastPage = vm.records.paginate.currentPage = 1;
					}
	            })
        	},
        	resetData(){
        		this.curstep = 1;
        		
        		for(let val in this.withdrawForm){
        			this.withdrawForm[val] = ''
        		}
        		
        		for(let val in this.addresses){
        			this.addresses[val] = ''
        		}
        		
        	},
        	feeCheck(fee){
                this.withdrawForm.fee = fee.fee;
        		this.withdrawForm.fid = fee.id;
        	},
            getAddresses(){
                var vm = this;
                vm.addresses.loaded = false;
                // 钱包提币地址
                axios.get(this.commonApi.api.withdrawAddresses, {params: {'coinType':vm.curCoin}}).then(function(response){
                    vm.addresses.list = response.data.data.list;
                    vm.addresses.loaded = true;
                });
            },
            sendConfirmEmail:function(e, index){
                var vm = this,
                	count = 60; 
                vm.$set(vm.records.list[index], 'sended', 'loading...')
                axios.post(this.commonApi.api.sendWithdrawConfirm, {
                    id:vm.records.list[index].id
                }).then(function(response){
                    var res = new Object(response.data);
                    if(res.code == 200){
	                    timeCount(
							function(c){
								vm.$set(vm.records.list[index], 'sended', c + vm.$t('cmn.second')+vm.$t('cmn.later')+vm.$t('cmn.reacquire'))
							},
							function(c){
								vm.$set(vm.records.list[index], 'sended', false)
							},  count);
                    }else{
                    	vm.$set(vm.records.list[index], 'sended', false);
                    	vm.$comfirmbox({  content:res.message });
                    }
                })
				
            },
            getUserInfo(){
                var vm = this;
                axios.get(this.commonApi.api.getUserInfo).then(function(response){
                     vm.user = response.data.data;
                     vm.user.curCoin = vm.curCoin;
                });
            },
            amountDrag(prc){
        		var vm = this;
        		vm.withDrawAmount = vm.withdrawForm.amount = floatMath.res(floatMath.mul(vm.walletData.balance, prc),6);
        	},
            checkCanAddress(){
                var vm = this;
                if(vm.user.tradeCode == 0 || 
                    vm.user.googleSecret == 0 || 
                    vm.user.cerBaseStatus != 1) {
                    vm.$comfirmbox({  
                        content:vm.$tc('financial.withdrawTips') 
                    }).then(function(){
                         vm.$router.push({path:'/user'});
                    })
                } else {
                    $('#withdraw-dailog').modal('show')
                }
            }
        }
    }
    
</script>
