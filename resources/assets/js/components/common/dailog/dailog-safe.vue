<template>
	<div>
	  	<div class="modal-body">
	  		<form action="javascript:;" ref="myForm">

	    	<div class="form-group">
	    		<div class="form-control dropdown" >
	    			<a data-toggle="dropdown"><span v-if="safe == 0">{{$t('member.close')}}</span><span v-else>{{$tc('member.loginOption', safe-1)}}</span></a>
	    			<ul class="dropdown-menu">
	    				<li>
	    					<a @click="checkType(safe_option[0])">{{ $t('member.close') }}</a>
	    				</li>
	    				<li v-for="(l, i) in safe_option">
	    					<a @click="checkType(l)" v-if="i != 0">{{ $tc('member.loginOption',i-1) }}</a>
	    				</li>
	    			</ul>
	    		</div>
	    	</div>
	    	<div class="form-group" v-if="params.googleSecret == 1">
        		<input class="form-control" v-model="googleCode" :placeholder="$tc('member.googleCode',1)"/>
        	</div>	
			<div class="form-group">
                <div class="group-col">
                    <a class="col-btn" @click="sendVerifyCode" v-if="countdown == 0">{{$tc('member.getVerCode',1)}}</a>
                    <span class="col-btn" v-if="countdown > 0">{{ countdown }}{{$tc('member.reGet',1)}}</span>
                    <span class="col-btn" v-if="countdown < 0">{{$tc('member.sendingVerCode',1)}}</span>
                    <!-- <input class="form-control" type="text" v-model="emailCode" :placeholder="$t('cmn.code')+'('+$t('cmn.mobile')+'/'+$t('cmn.email')+')'"/> -->
                	<input class="form-control" type="text" v-if="params.mobile" v-model="emailCode" :placeholder=" $t('cmn.mobile') + $t('cmn.code')"/>
                    <input class="form-control" type="text" v-else v-model="emailCode" :placeholder=" $t('cmn.email') + $t('cmn.code')"/>

                </div>
            </div>

	    	<div class="form-group" v-loading="!okloaded">
	    		<button class="btn btn-primary form-sub-btn" @click="ok">{{$tc('member.ok',1)}}</button>
	    	</div>
	    	</form>
	  	</div>
	  	
  	</div>
</template>

<script>
export default{
	props:['params'],
	data(){
		return {
			certype:'',
			safe_option:[['0',this.$i18n.t('cmn.select')]],
			tips_select:this.$i18n.t('cmn.select'),
			safe:'',
			googleCode:'',
			emailCode:'',
			countdown:0,
            interval:'',
            okloaded:true,
		}
	},
	mounted(){
        var vm = this;
        // 审核信息
		axios.get(this.commonApi.api.getSafeOption).then(function(response){
			var res = response.data;
			if (res.code == 200) {
				vm.safe_option = res.data.config;
				if (vm.params.loginOption > 0) {
					vm.safe = vm.params.loginOption;
					vm.tips_select = vm.safe_option[vm.safe][1];
				}
				
			}
		});
		// 检测邮件验证码lock
		this.checkEmailLock();
    },
	methods:{
		certypecheck(i){
			
		},
		close(){
			this.$emit('cm');
		},
		checkType(i){
			console.log(i)
			this.safe = i[0];
			this.tips_select = i[1];
		},
		ok:function(){
			var vm = this;
			vm.okloaded = false;
            axios.post(this.commonApi.api.changeLoginSafeOption, {
            	safe:vm.safe,
            	googleCode:vm.googleCode,
            	emailCode:vm.emailCode
            }).then(function(response){
            	vm.okloaded = true;
                var res = new Object(response.data);
				vm.$comfirmbox({ content:res.message, status:res.code })
				if(res.code == 200){
					vm.close();
				}
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
	}
}
</script>