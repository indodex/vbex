<template>
  	<div class="modal-body" v-loading="!loaded">
        <form action="javascript:;" ref="tradingValidate">
        	<div class="form-group">
        		<input class="form-control" type="password" v-model="pwd" :placeholder="$t('cmn.please')+$t('cmn.input')+$t('cmn.trad')+$t('cmn.password')" />
        	</div>
        	
        	<div class="form-group">
        		<button class="btn btn-primary form-sub-btn" @click="ok">{{$t('cmn.confirm')}}</button>
        	</div>
        </form>
  	</div>
</template>

<script>
	import hacStorage from '../storage.js'
    export default{
    	mounted(){
    		var vm = this;
    	},
    	data(){
            return {
               	pwd:'',
               	loaded:true
            }
       },
        methods:{
            close(){
            	var curType = $('#tradingPwd-dailog').attr('tradingType'),
            		nowTime = new Date().getTime();
            	hacStorage.setItem('ts', nowTime);
                this.$emit('cm',{type_:curType, pwd:this.pwd});
            },
            ok:function(){
                var vm = this;
                vm.loaded = false;
                axios.post(this.commonApi.api.tradesPwd, {
                    tradeCode:vm.pwd,
                }).then(function(response){
//              	console.log(response)
                	vm.loaded = true;
                    var res = new Object(response.data);
                    if(res.code == 200) {
                        vm.close();
                    } else {
                        
						vm.$comfirmbox({ content:res.message, status:res.code })
                    }
                }).catch(function(){
                	vm.loaded = true;
                	vm.$comfirmbox({
                        content:vm.$t('cmn.timeOut')
                    })
                })
                return false;
            },
        }
    }
</script>