<template>
  	<div class="modal-body">
        <form action="javascript:;" :model="infoForm" ref="infoForm">
    		<div class="form-group">
        		<input class="form-control" type="text" v-model="infoForm.name" :placeholder="$t('cmn.please')+$t('cmn.input')+$t('cmn.name')"/>
        	</div>
        	<div class="form-group">
        		<button class="btn btn-primary form-sub-btn" @click="ok">{{$t('cmn.confirm')}}</button>
        	</div>
        </form>
  	</div>
</template>

<script>
    export default{
        props:['params'],
    	mounted(){
    		var vm = this;
    	},
        methods:{
            close(){
                this.infoForm.name = '';
                this.$emit('cm');
            },
            ok:function(){
                var vm = this
                axios.post(this.commonApi.api.changeName, {
                    name:vm.infoForm.name,
                }).then(function(response){
                    var res = new Object(response.data);
                    if(res.code == 200) {
                    	vm.verifyShow=true;
                        vm.close();
                    } else {
                    	vm.$comfirmbox({ content:res.message, status:res.code })
                    }
                })
                return false;
            }
        },
        data(){
            return {
                infoForm:{
                    name:this.params.name,
                }
            }
        }
    }
</script>