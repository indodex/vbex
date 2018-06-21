<template>
	<div class="ucontainer">
		<div class="panel">
			<h3 class="p-title fs-16"><span class="n">{{$tc('referrer.referrer')}}</span></h3>
			<p class="fs-12">{{$tc('referrer.description')}}</p>
			<div><span id="user_adrs" class="fs-18 text-gray" style="margin-right:10px;" >{{inviteUrl}}</span><a class="text-primary" style="cursor:pointer;" @click="copystr">{{$tc('referrer.copy')}}</a></div>
			<div class="clearfix ref-rule">
				<div class="pull-left mr20 img-box qrcode">
					<canvas id="ads-qrcode" class="ads-qrcode-cvs"></canvas>
				</div>
				<div class="pull-left mr10 rule-txt">
					<h5 class="fs-14">{{$tc('referrer.explain1', 0)}}</h5>
					<p>{{$tc('referrer.explain1', 1)}}</p>
					<p>{{$tc('referrer.explain1', 2)}}</p>
					<p>{{$tc('referrer.explain2', 0)}}</p>
					<p>{{$tc('referrer.explain2', 1)}}</p>
					<p>{{$tc('referrer.explain2', 2)}}</p>
				</div>
				<div class="pull-left mr10 rule-txt">
					<h5 class="fs-14">{{$tc('referrer.award1', 0)}}</h5>
					<p>{{$tc('referrer.award1', 1)}}</p>
					<p>{{$tc('referrer.award1', 2)}}</p>
					<p>{{$tc('referrer.award2', 0)}}</p>
					<p>{{$tc('referrer.award2', 1)}}</p>
					<p>{{$tc('referrer.award2', 2)}}</p>
				</div>
			</div>
		</div>
		
		<div class="panel">
			<h3 class="p-title fs-16 p-col-title">
				<span class="n" :class="(step==1)&&'active'" @click="step = 1">{{$t('cmn.user')}}{{$t('cmn.income')}}</span>
				<span class="n" :class="(step==2)&&'active'" @click="step = 2">{{$tc('referrer.referrer')}}</span>
			</h3>
			<div class="u-tb" v-show="step == 1" v-loading="!inviteRewardList.loaded">
				<div class="row tb-hd">
					<div class="col-xs-4">{{$t('cmn.income')}}{{$t('cmn.source')}}</div>
					<div class="col-xs-4 text-center">{{$t('cmn.income')}}{{$t('cmn.codeType')}}</div>
					<div class="col-xs-4 text-right">{{$t('cmn.income')}}{{$t('cmn.quantity')}}</div>
				</div>
				<div class="tb-ctn row">
					<ul class="tb-list " v-if="inviteRewardList.list.length">
						<li class="clearfix" v-for="item in inviteRewardList.list">
							<div class="col-xs-4">{{item.fname}}</div>
							<div class="col-xs-4 text-center">{{item.currency}}</div>
							<div class="col-xs-4 text-right">{{item.val}}</div>
						</li>
					</ul>
					<div v-if="!inviteRewardList.list.length" class="no-data col-xs-12 text-center">{{$tc('cmn.noRecords')}}</div>
				</div>
				<pager :curnum="inviteRewardList.currentPage" :lastPage="inviteRewardList.lastPage" @skip="getInviteRewardsList"></pager>
			</div>
			<div class="u-tb text-center" v-show="step == 2" v-loading="!inviteList.loaded">
				<div class="row tb-hd">
					<div class="col-xs-4">{{$tc('referrer.username')}}</div>
					<div class="col-xs-4">{{$tc('cmn.regist')}}{{$tc('cmn.time')}}</div>
					<div class="col-xs-4">{{$tc('referrer.validity')}}</div>
					<!--<div class="col-xs-3">{{$tc('cmn.state')}}</div>-->
				</div>
				<div class="tb-ctn row">
					<ul class="tb-list text-center" v-if="inviteList.list.length">
						<li class="clearfix" v-for="item in inviteList.list">
							<div class="col-xs-4">{{item.name}}</div>
							<div class="col-xs-4">{{item.createdAt}}</div>
							<div class="col-xs-4">{{item.updateAt}}</div>
							<!--<div class="col-xs-3">{{item.isVerified}}</div>-->
						</li>
					</ul>
					<div v-if="!inviteList.list.length" class="no-data col-xs-12 text-center">{{$tc('cmn.noRecords')}}</div>
				</div>
				<pager :curnum="inviteList.currentPage" :lastPage="inviteList.lastPage" @skip="getInviteList"></pager>
			</div>
		</div>
		
	</div>
</template>

<script>
//  import {mapState} from 'vuex'
    import { mapGetters } from 'vuex'
	import Qrcode from 'qrcode'
    export default {
        computed:{
            ...mapGetters([
            	'user_state',
            ]),
            inviteUrl(){
            	return this.user_state.inviteUrl;
            }
        },
        data(){
        	return {
        		inviteList:{list:'', loaded:false, currentPage:1},
        		inviteRewardList:{list:'', loaded:false, currentPage:1},
        		step:1
        	}
        },
        watch:{
        	inviteUrl(){
            	var vm = this;
            	vm.buildCode();
        	}
        },
        mounted(){
        	if(this.user_state.inviteUrl){
				this.buildCode()
        	}
			this.getInviteRewardsList();
        	this.getInviteList();
    	},
        methods:{
        	getInviteList(num){
	        	var vm = this,
        			page = num || 1;
        			
        		vm.inviteList.loaded = false;
	        	axios.get(vm.commonApi.api.inviteList, {params:{page:page}}).then((response) => {
	        		vm.inviteList.loaded = true;
	        		if(response.data.code == 200){
						vm.$merge(vm.inviteList, response.data.data);
						vm.inviteList.list = response.data.data.list;
					}else{
						vm.inviteList.list = '';
						vm.inviteList.currentPage = vm.inviteList.lastPage = 1;
					}
	        	})
        	},
        	getInviteRewardsList(num){
        		var vm = this,
        			page = num || 1;
        			
        		vm.inviteRewardList.loaded = false;
        		axios.get(vm.commonApi.api.inviteRewradsList, {params:{page:page}}).then((response) => {
        			vm.inviteRewardList.loaded = true;
        			if(response.data.code == 200){
        				vm.$merge(vm.inviteRewardList, response.data.data);
						vm.inviteRewardList.list = response.data.data.list;
        			}else{
						vm.inviteRewardList.list = '';
						vm.inviteRewardList.currentPage = vm.inviteRewardList.lastPage = 1;
					}
        			
        		})
        	},
        	copystr(){
				var adrsDOM = document.getElementById('user_adrs');
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
        	buildCode(){
        		var vm = this;
        		Qrcode.toCanvas(document.getElementById('ads-qrcode'), vm.user_state.inviteUrl , (err, el) => {
					if(el){
						el.style.width = '100%';
						el.style.height = '100%';
					}
				})
        	},
        	
        }
    }
</script>

<style>
</style>