<template>
	<div class="ucontainer">
    	<bread-head></bread-head>
    	<div class="panel">
    		<h3 class="p-title fs-16"><span class="n">{{$tc('member.account',1)}}</span></h3>
    		<div class="row">
    			<div class="col-md-4 u-info-box">
    				<div class="cl-box" v-if="user"> 
    					<div class="img-box"><img :src="user.avatar" alt="" /></div>
    					<div class="m-info">
    						<div class="name fs-18">
    							{{user.name}}
    							<a class="fs-14 text-primary" data-toggle="modal" data-target="#info-dailog">{{$tc('member.edit',0)}}</a>
    						</div>
    						<div class="msg">{{user.mobile || user.email}}</div>
    					</div>
    				</div>
    			</div>
    		</div>
    	</div>
    	<div class="panel">
    		<h3 class="p-title fs-16"><span class="n">{{$tc('member.securitySettings',1)}}</span></h3>
    		<ul class="ctrl-list">
    			<li class="row">
    				<div class="col-sm-3">
    					<i class="fa fa-envelope"></i>
    					{{$tc('member.email',1)}}{{user.email}}
    				</div>
    				<div class="col-sm-8 fs-12">
                        <span class="text-gray-light" v-if="user.isEmail != 1">{{$tc('member.actionSecond',2)}}</span>
    					<span class="text-success" v-else>{{$tc('member.actionSecond',1)}}</span>
    				</div>
    				<div class="col-sm-1 fs-12">
    					<a class="ed text-primary" data-toggle="modal" data-target="#mail-dailog"  v-if="user.isEmail != 1">{{$tc('member.actionFirst',1)}}</a>
    				</div>
    			</li>
    			<li class="row">
    				<div class="col-sm-3">
    					<i class="fa fa-lock"></i>
    					{{$t('member.loginPassword')}}
    				</div>
    				<div class="col-sm-8 fs-12">
                        <span class="text-success" v-if="user">{{$tc('member.actionSecond',1)}}</span>
                        <span class="text-gray-light" v-else>{{$tc('member.actionSecond',2)}}</span>
    				</div>
    				<div class="col-sm-1 fs-12">
    					<a class="ed text-primary" data-toggle="modal" data-target="#pwd-dailog">{{$tc('member.actionFirst',1)}}</a>
    				</div>
    			</li>
                <li class="row">
                    <div class="col-sm-3">
                        <i class="fa fa-lock"></i>
                        {{$tc('member.tradCode',0)}}
                    </div>
                    <div class="col-sm-8 fs-12">
                        <span class="text-success" v-if="user.tradeCode == 1">{{$tc('member.actionSecond',1)}}</span>
                        <span class="text-gray-light" v-else>{{$tc('member.actionSecond',2)}}</span>
                    </div>
                    <div class="col-sm-1 fs-12">
                        <a class="ed text-primary" data-toggle="modal" data-target="#tradcode-dailog" v-if="user.tradeCode == 1">{{$tc('member.actionFirst',1)}}</a>
                        <a class="ed text-primary" data-toggle="modal" data-target="#forgettradcode-dailog" v-if="user.tradeCode == 1">{{$tc('member.actionFirst',2)}}</a>
                        <a class="ed text-primary" data-toggle="modal" data-target="#tradcode-dailog" v-else>{{$tc('member.actionSecond',0)}}</a>
                    </div>
                </li>
    			<li class="row">
    				<div class="col-sm-3">
    					<i class="fa fa-google"></i>
    					{{$tc('member.googleSecret',1)}}
    				</div>
    				<div class="col-sm-8 fs-12">
                        <span class="text-success" v-if="user.googleSecret == 1">{{$tc('member.actionSecond',1)}}</span>
    					<span class="text-gray-light" v-else>{{$tc('member.actionSecond',2)}}</span>
    				</div>
    				<div class="col-sm-1 fs-12">
    					<a class="ed text-primary" data-toggle="modal" data-target="#changegoogle-dailog" v-if="user.googleSecret == 1">{{$tc('member.actionFirst',1)}}</a>
                        <a class="ed text-primary" data-toggle="modal" data-target="#forgetgoogle-dailog" v-if="user.googleSecret == 1">{{$tc('member.actionFirst',2)}}</a>
                        <a class="ed text-primary" data-toggle="modal" data-target="#google-dailog" v-else>{{$tc('member.actionSecond',0)}}</a>
    				</div>
    			</li>
                <li class="row">
                    <div class="col-sm-3">
                        <i class="fa fa-phone"></i>
                        {{$t('member.phone')}}{{user.mobile}}
                    </div>
                    <div class="col-sm-8 fs-12">
                        <span class="text-success" v-if="user.mobile">{{$tc('member.actionSecond',1)}}</span>
                        <span class="text-gray-light" v-else>{{$tc('member.actionSecond',2)}}</span>
                    </div>
                    <div class="col-sm-1 fs-12">
                        <a class="ed text-primary" data-toggle="modal" data-target="#phone-dailog">{{$tc('member.actionFirst',1)}}</a>
                    </div>
                </li>
    			
    			<li class="row">
    				<div class="col-sm-3">
    					<i class="fa fa-check-circle"></i>
    					{{$tc('member.certification',0)}}
    				</div>
    				<div class="col-sm-8 fs-12">
                        <span class="text-gray-light" v-if="user.cerBaseStatus == 0">{{$tc('member.statusFirst',2)}}</span>
                        <span class="text-success" v-else-if="user.cerBaseStatus == 1 && user.cerAdvancedStatus == 2">{{$tc('member.statusFirst',0)}}</span>
                        <span class="text-increase" v-else-if="user.cerBaseStatus == -1">{{ user.cerRemark }}</span>
                        <span class="text-gray-light" v-else-if="user.cerBaseStatus == 2">{{$tc('member.actionThree',2)}}</span>
                        <span class="text-success" v-else-if="user.cerAdvancedStatus == 1">{{$tc('member.statusSecond',0)}}</span>
                        <span class="text-increase" v-else-if="user.cerAdvancedStatus == -1">{{ user.cerRemark }}</span>
                        <span class="text-gray-light" v-else-if="user.cerAdvancedStatus == 0">{{$tc('member.statusSecond',2)}}</span>
    				</div>
    				<div class="col-sm-1 fs-12">
    					<a class="ed text-primary" v-if="user.cerBaseStatus == 2 || user.cerBaseStatus == -1" data-toggle="modal" data-target="#indentify-dailog">{{$tc('member.actionThree',0)}}</a>
                        <a class="ed text-primary" v-if="user.cerBaseStatus == 1 && user.cerAdvancedStatus == 2" data-toggle="modal" data-target="#indentifyadv-dailog">{{$tc('member.actionThree',0)}}</a>
                        <a class="ed text-primary" v-if="user.cerAdvancedStatus == -1" data-toggle="modal" data-target="#indentifyadv-dailog">{{$tc('member.actionThree',0)}}</a>
    				</div>
    			</li>
                <li class="row">
                    <div class="col-sm-3">
                        <i class="fa fa-shield"></i>
                        {{$tc('member.safeOption',1)}}
                    </div>
                    <div class="col-sm-8 fs-12">
                        <span class="text-gray-light" v-if=" user.loginOption == 0">{{ $tc('member.actionSecond',2) }}</span>
                        <span class="text-gray-light" v-if=" user.loginOption == 1">{{ $tc('member.googleSecret',1) }}</span>
                        <span class="text-gray-light" v-if=" user.loginOption == 2 && user.mobile != ''">{{ $t('cmn.mobile') }}{{ $t('cmn.code') }}</span>
                        <span class="text-gray-light" v-if=" user.loginOption == 2 && user.mobile == ''">{{ $t('cmn.email') }}{{ $t('cmn.code') }}</span>
                        <span class="text-gray-light" v-if=" user.loginOption == 3 && user.mobile != ''">{{ $tc('member.googleSecret',1) }}+{{$t('cmn.mobile')}}{{ $t('cmn.code') }}</span>
                        <span class="text-gray-light" v-if=" user.loginOption == 3 && user.mobile == ''">{{ $tc('member.googleSecret',1) }}+{{$t('cmn.mobile')}}{{ $t('cmn.code') }}</span>
                    </div>
                    <div class="col-sm-1 fs-12">
                        <a class="ed text-primary" data-toggle="modal" data-target="#safe-dailog">{{$tc('member.actionSecond',0)}}</a>
                        
                    </div>
                </li>
                <li class="row">
                    <div class="col-sm-3">
                        <i class="fa fa-exchange"></i>
                        {{$tc('member.tradeOption',0)}}
                    </div>
                    <div class="col-sm-8 fs-12">
                        <span class="text-gray-light" v-if=" user.tradeOption == 0">{{ $tc('member.tradCodeOption',0) }}</span>
                        <span class="text-gray-light" v-if=" user.tradeOption == 1">{{ $tc('member.tradCodeOption',1) }}</span>
                        <span class="text-gray-light" v-if=" user.tradeOption == 2">{{ $tc('member.tradCodeOption',2) }}</span>
                    </div>
                    <div class="col-sm-1 fs-12">
                        <a class="ed text-primary" data-toggle="modal" data-target="#tradeoption-dailog">{{$tc('member.actionSecond',0)}}</a>
                        
                    </div>
                </li>
                <!-- <li class="row">
                    <div class="col-sm-3">
                        <i class="fa fa-arrow-circle-o-up"></i>
                        {{$t('member.withdrawalOption')}}
                    </div>
                    <div class="col-sm-8 fs-12">
                        <span class="text-gray-light" v-if=" user.withdrawalOption == 0">{{ $tc('member.actionSecond',2) }}</span>
                        <span class="text-gray-light" v-if=" user.withdrawalOption == 1">{{ $tc('member.googleSecret',1) }}</span>
                        <span class="text-gray-light" v-if=" user.withdrawalOption == 2 && user.mobile != ''">{{ $t('cmn.mobile') }}{{ $t('cmn.code') }}</span>
                        <span class="text-gray-light" v-if=" user.withdrawalOption == 2 && user.mobile == ''">{{ $t('cmn.email') }}{{ $t('cmn.code') }}</span>
                        <span class="text-gray-light" v-if=" user.withdrawalOption == 3 && user.mobile != ''">{{ $tc('member.googleSecret',1) }}+{{$t('cmn.mobile')}}{{ $t('cmn.code') }}</span>
                        <span class="text-gray-light" v-if=" user.withdrawalOption == 3 && user.mobile == ''">{{ $tc('member.googleSecret',1) }}+{{$t('cmn.email')}}{{ $t('cmn.code') }}</span>
                    </div>
                    <div class="col-sm-1 fs-12">
                        <a class="ed text-primary" data-toggle="modal" data-target="#withdrawaloption-dailog">{{$tc('member.actionSecond',0)}}</a>
                        
                    </div>
                </li> -->
    			<li class="row">
    				<div class="col-sm-3">
    					<i class="fa fa-money"></i>
    					{{$tc('member.isFee',1)}}
    				</div>
    				<div class="col-sm-8 fs-12">
    					<span class="text-gray-light">{{$tc('member.hacFree',1)}}</span>
    				</div>
    				<div class="col-sm-1">
    					<a class="checkswitch-box">
	    					<label class="checkswitch" >
	    						<input id="deductible" @click="deductible" type="checkbox" :checked="isDeduction == 1" />
	    						<i></i>
	    					</label>
    					</a>
    				</div>
    			</li>
    		</ul>
    	</div>
    	<!--模态框-->
    	<dailog boxid="pwd" :boxtitle="$tc('member.pwdTitle',1)" :params="user" boxsize="sm" ></dailog>
    	<dailog boxid="mail" :boxtitle="$tc('member.emailTitle',1)" boxsize="sm"  @modalcallback="getUserInfo" ></dailog>
    	<dailog boxid="phone" :boxtitle="$tc('member.mobileTitle',1)" boxsize="sm" :params="user" @modalcallback="getUserInfo"></dailog>
    	<dailog boxid="google" :boxtitle="$tc('member.googleTitle',1)" :params="user" boxsize="sm" @modalcallback="getUserInfo"></dailog>
    	<dailog boxid="tradcode" :boxtitle="$tc('member.tradCodeTitle',1)" :params="user" boxsize="sm" @modalcallback="getUserInfo"></dailog>
    	<dailog boxid="indentify" :boxtitle="$tc('member.indentifyTitle',1)" boxsize="sm" @modalcallback="getUserInfo"></dailog>
        <dailog boxid="indentifyadv" :boxtitle="$tc('member.indentifyAdvTitle',1)" boxsize="sm" @modalcallback="getUserInfo"></dailog>
        <dailog boxid="info" :boxtitle="$tc('member.nameTitle',1)" boxsize="sm" :params="user" @modalcallback="getUserInfo" ></dailog>
        <dailog boxid="safe" :boxtitle="$tc('member.safeTitle',1)" boxsize="sm" :params="user" @modalcallback="getUserInfo" ></dailog>
        <dailog boxid="forgettradcode" :boxtitle="$tc('member.forgetTradCodeTitle',1)" boxsize="sm" :params="user" @modalcallback="getUserInfo" ></dailog>
        <dailog boxid="changegoogle" :boxtitle="$tc('member.changegoogleTitle',1)" boxsize="sm" :params="user" @modalcallback="getUserInfo" ></dailog>
        <dailog boxid="forgetgoogle" :boxtitle="$tc('member.forgetgoogleTitle',1)" boxsize="sm" :params="user" @modalcallback="getUserInfo" ></dailog>
        <dailog boxid="tradeoption" :boxtitle="$tc('member.tradeOption',1)" boxsize="sm" :params="user" @modalcallback="getUserInfo" ></dailog>
        <dailog boxid="withdrawaloption" :boxtitle="$tc('member.withdrawalOption',1)" boxsize="sm" :params="user" @modalcallback="getUserInfo" ></dailog>
    	<!--模态框end-->
    	<!--<confirmbox :comfirmObj="cobj"></confirmbox>-->
    </div>
</template>

<script>
//	import LeftMenu from './LeftMenu'
	import breadHead from '../common/breadHead'
	import dailog from '../common/dailog/dailog'
//	import Qrcode from 'qrcode'
	
    export default {
        components: {
			breadHead,
			dailog
        },
        data(){
        	return {
        		cobj:{
	        		title:'这里是标题',
	        		ctn:'这里是内容'
	        	},
        		pvs:false,
        		gvs:false,
                user:'',
                isDeduction:'',
                loginOptionString:''
        	}
        },
        mounted(){
        	//登录拿token
//      	axios.post('/api/auth',{
//      		email:'9450561672@qq.com',
//      		password: 888888,
//      	}).then(function(res){
//      		console.log(res);
//      	})

        	//调用API接口
//			console.log(this.commonApi.api)
            this.getUserInfo();
            
            //comfirm Demo
			// this.$comfirmbox({
			// 	title:'标题',
			// 	content:'这里是内容'
			//}).then(function(){
			// 	alert(123)
			//})
			
//			Qrcode.toCanvas(document.getElementById('testQrcode'),'hahahahah', (err, url) => {
//				console.log(url)
//			})
			
			
			
       },
       	methods:{
            getUserInfo(){
                var vm = this;
                axios.get(this.commonApi.api.getUserInfo).then(function(response){
                     vm.user = response.data.data;
                     vm.isDeduction = vm.user.isDeduction;	//???不存在改变量
                });
            },
            deductible(){
                var vm = this;
                axios.post(this.commonApi.api.deductible).then(function(response){
                     if(response.data.code == 200) {
                        vm.getUserInfo();
//                      vm.$comfirmbox({ content:response.data.message })
                     } else {
//                      vm.$comfirmbox({ content:response.data.error })
                     }
                });
            }
       	}
       	
       
    }
</script>
