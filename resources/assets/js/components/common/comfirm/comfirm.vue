<template>
		<div class="comfirmbox" v-show="show">
			
			<transition  name="comfirm-anm">
			<div class="comfirmbox-inner" v-if="show">
				<div class="comfirmbox-ctn">
					<div class="wrap">
						<em class="sta-logo" :class="(status == '200')?'success':'warning'"></em>
						<h4 class="title">{{title}}</h4>
						<p class="ctn" v-html="content"></p>
						<div class="btn-wrap">
							<!--{{$t('cmn.confirm')}}{{$t('cmn.confirm')}}-->
							<a class="btn" :class="(status == '200')?'text-success':'text-primary'" @click="success">{{applyBtnTxt}}</a>
							<a class="btn btn-default" v-if="comfirm" @click="fail" v-html="cancelBtnTxt"></a>
						</div>
						
					</div>
				</div>
			</div>
			
			</transition>
			<div class="comfirmMask" @click="close" ></div>
		</div>
</template>

<script>
import Cookie from 'js-cookie'
export default{
	data(){
		return{
			show:false,	//这个值要注意
			title:'',
			content:'',
			status:'',
			comfirm:false,
			applyBtnTxt:'',
			cancelBtnTxt:''
		}
	},
	methods:{
		close(){
			this.show = false;
		},
		success(){
			var vm = this;
			vm.close();
		},
		fail(){
			var vm = this;
			vm.close();
		}
	},
	mounted(){
		var l = Cookie.get('lang') || (navigator.language || navigator.browserLanguage).toLowerCase();
		switch(l){
			case 'zh-cn':
				this.applyBtnTxt = '确认';
				this.cancelBtnTxt = '取消';
				break;
			case 'zh-tw':
				this.applyBtnTxt = '確認';
				this.cancelBtnTxt = '取消';
				break;
			default:
				this.applyBtnTxt = 'apply';
				this.cancelBtnTxt = 'cancel';
				break;
		}
	}

}
</script>

<style>
.comfirm-anm-enter-active{
	animation:crubberBand 0.35s;
	-webkit-animation:crubberBand 0.35s;
}
@keyframes crubberBand{0%{-webkit-transform: scaleX(1);transform: scaleX(1)}30%{-webkit-transform: scale3d(1.25,.75,1);transform: scale3d(1.25,.75,1)}40%{-webkit-transform: scale3d(.75,1.25,1);transform: scale3d(.75,1.25,1)}50%{-webkit-transform: scale3d(1.15,.85,1);transform: scale3d(1.15,.85,1)}65%{-webkit-transform: scale3d(.95,1.05,1);transform: scale3d(.95,1.05,1)}75%{-webkit-transform: scale3d(1.05,.95,1);transform: scale3d(1.05,.95,1)}to{-webkit-transform: scaleX(1);transform: scaleX(1)}}@-webkit-keyframes crubberBand{0%{-webkit-transform: scaleX(1);transform: scaleX(1)}30%{-webkit-transform: scale3d(1.25,.75,1);transform: scale3d(1.25,.75,1)}40%{-webkit-transform: scale3d(.75,1.25,1);transform: scale3d(.75,1.25,1)}50%{-webkit-transform: scale3d(1.15,.85,1);transform: scale3d(1.15,.85,1)}65%{-webkit-transform: scale3d(.95,1.05,1);transform: scale3d(.95,1.05,1)}75%{-webkit-transform: scale3d(1.05,.95,1);transform: scale3d(1.05,.95,1)}to{-webkit-transform: scaleX(1);transform: scaleX(1)}}
</style>