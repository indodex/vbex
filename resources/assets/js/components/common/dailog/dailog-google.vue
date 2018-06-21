<template>
      	<div class="modal-body" v-if="params.tradeCode == 1">
            <form action="javascript:;" :model="googleForm" ref="googleForm">
            	<div class="form-group clearfix">
	                <div class="text-center img-box" style="width:150px; height:150px; margin:0 auto 20px;">
	                	<!--<canvas id="ads-qrcode" class="ads-qrcode-cvs" style=""></canvas>-->
	                    <img :src="googleInfo.qrCodeUrl" alt="" />
	                </div>
	                <div class="from-tips text-gray-light">
                        <p>{{$tc('member.googleTipsFirst',1)}}{{googleInfo.googleSecret}}</p>
                        <p>{{$tc('member.googleTipsSecond',1)}}</p>
                        <p>{{$tc('member.googleTipsThree',1)}}</p>
                        <p>{{$tc('member.googleTipsFour',1)}}</p>
                        <p>{{$tc('member.googleTipsFive',1)}}{{googleInfo.googleSecret}}</p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-control" >{{googleInfo.googleSecret}}</div>
                </div>
            	<div class="form-group">
            		<input class="form-control" v-model="googleForm.googleCode" :placeholder="$tc('member.googleCode',1)"/>
            	</div>
            	<div class="form-group">
            		<input class="form-control" type="password" v-model="googleForm.tradeCode" :placeholder="$tc('member.tradCode',0)"/>
            	</div>
                <div class="form-group">
                    <div class="group-col">
                        <a class="col-btn" @click="sendVerifyCode" v-if="countdown == 0">{{$tc('member.getVerCode',1)}}</a>
                        <span class="col-btn" v-if="countdown > 0">{{ countdown }}{{$tc('member.reGet',1)}}</span>
                        <span class="col-btn" v-if="countdown < 0">{{$tc('member.sendingVerCode',1)}}</span>
                        <input class="form-control" type="text" v-if="params.mobile" v-model="googleForm.verifyCode" :placeholder=" $t('cmn.mobile') + $t('cmn.code')"/>
                        <input class="form-control" type="text" v-else v-model="googleForm.verifyCode" :placeholder=" $t('cmn.email') + $t('cmn.code')"/>
                    </div>
                </div>
            	<div class="form-group" v-loading="!okloaded">
            		<button class="btn btn-primary form-sub-btn" @click="bind">{{$tc('member.ok',1)}}</button>
            	</div>
            </form>
      	</div>
        <div class="modal-body" v-else>
        	
        	<div class="single-item">
            	<p class="fs-16 text-gray-light">{{$tc('member.googleTipsSix',1)}}&hellip;</p>
           	</div>
        </div>
</template>

<script>
//import Qrcode from 'qrcode'
export default{
    props:['boxid','boxtitle','boxsize','params'],
	mounted(){
		var vm = this;
        axios.get(this.commonApi.api.getGoogleSecret).then(function(response){
             vm.googleInfo = response.data.data;
             vm.googleInfo.qrCodeUrl = "http://qr.liantu.com/api.php?text="+vm.googleInfo.qrCodeUrl;
             // console.log(vm.items);
             vm.secret = vm.googleInfo.googleSecret;
             
//          Qrcode.toCanvas(document.getElementById('ads-qrcode'), vm.googleInfo.qrCodeUrl , (err, el) => {
//				if(el){
//					el.style.width = '100%';
//					el.style.height = '100%';
//				}
//			})
         })
	},
	methods:{
		close(){
			this.$emit('cm');
            this.googleForm.googleCode = '';
            this.googleForm.tradeCode = '';
		},
        bind:function(){
            var vm = this
            vm.okloaded = false;
            axios.post(this.commonApi.api.bindGoogleSecret, {
                googleSecret:vm.secret,
                verifyCode:vm.googleForm.verifyCode,
                googleCode:vm.googleForm.googleCode,
                tradeCode:vm.googleForm.tradeCode,
            }).then(function(response){
                vm.okloaded = true;
                var res = new Object(response.data);
                if (res.code == 200) {
                    vm.close();
                }
//              alert(res.message);
                vm.$comfirmbox({ content:res.message, status:res.code })
            })
            return false;
        },

        checkEmailLock:function(){
            var vm = this;
            axios.get(this.commonApi.api.checkEmailLock).then(function(response){
                var res = new Object(response.data);
                if (res.code == 200) {
                    vm.countdown = res.data;
                    vm.doneInterval();
                }
            });
        },
        sendVerifyCode:function(){
            var vm = this;
            vm.countdown = -1;
            axios.get(this.commonApi.api.sendVerifyCode).then(function(response){
                var res = new Object(response.data);
                if (res.code == 200) {
                    vm.countdown = 60;
                    vm.doneInterval()
                }
            });
        },
        doneInterval:function(){
            var vm = this;
            this.interval = setInterval(function(){
                vm.countdown -- ;

                if (vm.countdown == 0) {
                    clearInterval(vm.interval);
                }
            },1000);
        }
	},
    data(){
        return {
            googleInfo:[],
            secret:'',
            googleForm:{
                googleCode:'',
                tradeCode:'',
                verifyCode:''
            },
            countdown:0,
            interval:'',
            okloaded:true,
        }
    }
}
</script>