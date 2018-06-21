<template>
  	<div class="modal-body">
        <form action="javascript:;" :model="phoneForm" ref="phoneForm" v-if="params.tradeCode == 1 && params.googleSecret == 1">
        	<div class="form-group">
        		<input class="form-control" type="text" v-model="phoneForm.mobile" :placeholder="$t('cmn.phone')" />
        	</div>
            <div class="form-group">
                <div class="group-col">
	                <input class="form-control" type="text" v-model="phoneForm.code" :placeholder="$tc('member.phoneCode',1)"/>
	                <a class="col-btn" @click="send" v-if="!vcount">{{$tc('member.getVerCode',1)}}</a>
	                <span class="col-btn text-gray-light"  v-if="vcount">{{vcount}}{{$tc('member.reGet',1)}}</span>
                </div>
            </div>
        	<div class="form-group">
        		<input class="form-control" type="password" v-model="phoneForm.tradeCode" :placeholder="$t('cmn.please')+$t('cmn.input')+$tc('member.tradCode',0)" />
        	</div>
            <div class="form-group">
                <input class="form-control" type="password" v-model="phoneForm.googleCode" :placeholder="$tc('member.googleCode',0)" />
            </div>
        	<div class="form-group" v-loading="!okloaded">
        		<button class="btn btn-primary form-sub-btn" @click="ok">{{$tc('member.ok',1)}}</button>
        	</div>
        </form>
        <div class="modal-body" v-else>
            <div class="row tb-hd text-center" v-if="params.tradeCode != 1">{{$t('member.googleTipsSix')}}</div>
            <div class="row tb-hd text-center" v-if="params.googleSecret != 1">{{$t('member.googleTipsSeven')}}</div>
        </div>
  	</div>
</template>

<script>
    export default{
        props:['params'],
    	mounted(){
    		var vm = this;
    	},
    	data(){
            return {
                phoneForm:{
                    mobile:'', 	//this.params.mobile
                    tradeCode:'',
                    googleCode:'',
                    code:'',
                },
                sendForm:{
                    mobile:'',
                    code:'',
                },
                okloaded:true,
                isVerify:'',
                vcount:0
            }
       },
        methods:{
            close(){
                this.phoneForm.mobile = '';
                this.phoneForm.tradeCode = '';
                this.phoneForm.googleCode = '';
                this.phoneForm.code = '';
                this.$emit('cm');
            },
            ok:function(){
                var vm = this;
                vm.okloaded = false;
                axios.post(this.commonApi.api.changeMobile, {
                    mobile:vm.phoneForm.mobile,
                    tradeCode:vm.phoneForm.tradeCode,
                    googleCode:vm.phoneForm.googleCode,
                    code:vm.phoneForm.code,
                }).then(function(response){
                    vm.okloaded = true;
                    var res = new Object(response.data);
                    if(res.code == 200) {
                        vm.close();
                    }
                    vm.$comfirmbox({ content:res.message, status:res.code })
                })
                return false;
            },
            send:function(){
                var vm = this;
                vm.vcount = 60;
                axios.post(this.commonApi.api.codeSend, {
                    mobile:vm.phoneForm.mobile
                }).then(function(response){
                    var res = new Object(response.data);
                    if(res.code != 200) {
                    	vm.vcount = 0;
                        vm.$comfirmbox({ content:res.message, status:res.code })
                    }else{
	                    setInterval(() => {
	                    	if(vm.vcount - 1 > 0)
	                    		vm.vcount--;
	                    	else
	                    		vm.vcount = 0;
	                    },1000)
                    }
                }).catch(function(err){
                	vm.$comfirmbox({ content:err })
                	vm.vcount = 0;
                })
                return false;
            },
            checkCode:function(){
                var vm = this
                axios.post(this.commonApi.api.checkCode, {
                    code:vm.sendForm.code
                }).then(function(response){
                    var res = new Object(response.data);
                    if(res.code == 200) {
                        vm.isVerify = 1;
                        vm.phoneForm.code = vm.sendForm.code;
                        vm.$comfirmbox({ content:res.message, status:res.code })
                    } else {
                        vm.$comfirmbox({ content:res.message, status:res.code })
                    }
                })
                return false;
            },
            sendCode:function(){
                var vm = this
                axios.get(this.commonApi.api.codeSend, {
                    params: {
                        'mobile':vm.phoneForm.mobile
                    }
                }).then(function(response){
                    var res = new Object(response.data);
                    vm.$comfirmbox({ content:res.message, status:res.code })
                })
                return false;
            },
            setVerify:function(){
                var vm = this
                vm.isVerify = 1;
            }
        },
    }
</script>