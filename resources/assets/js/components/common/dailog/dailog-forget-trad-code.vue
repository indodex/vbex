<template>
	<div class="modal-body">
    	<form action="javascript:;" ref="tradeForm">
        	
            <div class="form-group">
                <input class="form-control" type="password" v-model="tradeCode" :placeholder="$tc('member.tradCode',1)"/>
            </div>
            <div class="form-group">
                <input class="form-control" type="password" v-model="confirmTradeCode" :placeholder="$tc('member.tradCode',2)" />
            </div>
            <div class="form-group">
        		<input class="form-control" type="password" v-model="pwd" :placeholder="$tc('member.loginPassword',1)"/>
        	</div>
            <div class="form-group">
                <div class="group-col">
                    <a class="col-btn" @click="sendVerifyCode" v-if="countdown == 0">{{$tc('member.getVerCode',1)}}</a>
                    <span class="col-btn" v-if="countdown > 0">{{ countdown }}{{$tc('member.reGet',1)}}</span>
                    <span class="col-btn" v-if="countdown < 0">{{$tc('member.sendingVerCode',1)}}</span>
                    <!-- <input class="form-control" type="text" v-model="verifyCode" :placeholder="$t('cmn.code')+'('+$t('cmn.mobile')+'/'+$t('cmn.email')+')'"/> -->
                    <input class="form-control" type="text" v-if="params.mobile" v-model="verifyCode" :placeholder=" $t('cmn.mobile') + $t('cmn.code')"/>
                    <input class="form-control" type="text" v-else v-model="verifyCode" :placeholder=" $t('cmn.email') + $t('cmn.code')"/>
                </div>
            </div>
            <div class="form-group" v-if="params.googleSecret == 1">
                <input class="form-control" type="password" v-model="googleCode" :placeholder="$tc('member.googleCode',1)"/>
            </div>
        	<!-- <div class="form-group">
        		<input class="form-control" style="width:200px; float:left;" type="text" v-model="code" placeholder="验证码" />
                <img :src="src" style="height:40px; width:122px; float:left; margin-left:16px;" alt="" v-on:click="getVerifyCode()">
                <div style="clear:both;"></div>
        	</div> -->
        	<div class="form-group" v-loading="!okloaded">
        		<button class="btn btn-primary form-sub-btn" @click="ok">{{$tc('member.ok',1)}}</button>
        	</div>
        </form>
  	</div>

</template>

<script>
export default{
	props:['params'],
    data(){
        return {
            src:'',
            pwd:'',
            verifyCode:'',
            googleCode:'',
            tradeCode:'',
            confirmTradeCode:'',
            countdown:0,
            interval:'',
            okloaded:true,
        }
    },
	mounted(){
		this.checkEmailLock();
        if (this.countdown > 0) {
            this.doneInterval();
        }
	},
	methods:{
		close(){
			this.$emit('cm');
		},
        checkEmailLock:function(){
            var vm = this;
            axios.get(this.commonApi.api.checkEmailLock).then(function(response){
                var res = new Object(response.data);
                if (res.code == 200) {
                    vm.countdown = res.data;
                    this.doneInterval();
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
        },
        ok:function(){
            var vm = this
            vm.okloaded = false;
            axios.post(vm.commonApi.api.retrieveTradcode, {
            	pwd:vm.pwd,
                googleCode:vm.googleCode,
                verifyCode:vm.verifyCode,
                tradeCode:vm.tradeCode,
                confirmTradeCode:vm.confirmTradeCode
            }).then(function(response){
                 vm.okloaded = true;
            	var res = new Object(response.data);
                if (res.code == 200) {
                    vm.close();
                }
                vm.$comfirmbox({ content:res.message, status:res.code })
            })
            return false;
        }
	},
    
}
</script>