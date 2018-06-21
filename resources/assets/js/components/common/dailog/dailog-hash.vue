<template>
      	<div class="modal-body" v-if="account.googleSecret == 1 && account.tradeCode == 1">
        	<form action="javascript:;" :model="code" ref="code">
        		<div class="form-group" v-if="params.type == 'createhash'">
        			<div class="from-tips text-gray-light"><p>{{$tc('hash.TipsFirst',1)}}</p></div>
        		</div>
                <div class="form-group" v-if="params.type == 'createhash'">
            		<p >{{$tc('hash.amount',1)}}</p>
            		<label class="radio-inline" v-for="item in items">
            			<input type="radio" name="itemId" :value="item.id" v-model="code.amount"/>{{item.amount}}
            		</label>
            	</div>
            	<div class="form-group">
            		<input class="form-control" type="password" :placeholder="$tc('member.tradCode',0)" v-model="code.tradesCode"/>
            	</div>
            	<div class="form-group">
            		<input class="form-control" :placeholder="$tc('member.googleCode',1)" v-model="code.oneCode"/>
            	</div>
            	<div class="form-group" v-loading="!loaded">
            		<button class="btn btn-primary form-sub-btn" @click="formSubmit" >{{$tc('member.ok',1)}}</button>
            	</div>
            </form>
      	</div>
        <div class="modal-body" v-else-if="account.tradeCode == 1 && account.googleSecret == 0">
            <div class="single-item">
                <p class="fs-16 text-gray-light">{{$tc('member.googleTipsSeven',1)}}&hellip;</p>
            </div>
        </div>
        <div class="modal-body" v-else-if="account.tradeCode == 0">
            <div class="single-item">
                <p class="fs-16 text-gray-light">{{$tc('hash.TipsSecond',1)}}&hellip;</p>
            </div>
        </div>
</template>

<script>
export default{
    props:['params'],
    data() {
      return {
        items:[],
        code:{
            oneCode:'',
            tradesCode:'',
        },
        account:[],
        loaded:true
      };
    },
	methods:{
		close(obj){
            this.code.amount = '';
            this.code.oneCode = '';
            this.code.tradesCode = '';
			this.$emit('cm', obj);
		},
        formSubmit:function(){
            var vm = this;
            vm.loaded = false;
            if(vm.params.type == 'createhash'){
	            axios.post(vm.commonApi.api.createHashCode, {
	                itemId:vm.code.amount,
	                oneCode:vm.code.oneCode,
	                tradesCode:vm.code.tradesCode,
	                currencyCode:'usd',
	            }).then(function(response){
	            	vm.loaded = true;
	                var res = new Object(response.data);
	                vm.$comfirmbox({ content:res.message, status:res.code })
	                if(res.code == 200){
	                	vm.close({type:vm.params.type});
	                }
	            })
            }else if(vm.params.type == 'detailhash'){
            	axios.post(vm.commonApi.api.detailHashCode, {
	                cid:vm.params.dataid,
	                googleCode:vm.code.oneCode,
	                tradesCode:vm.code.tradesCode,
	            }).then(function(response){
	            	vm.loaded = true;
	            	var res = new Object(response.data);
	            	vm.$comfirmbox({ content:res.message, status:res.code })
	            	if(res.code == 200){
	                	vm.close({type:vm.params.type, id:vm.params.dataid, code:res.data.code});
	                }
	            })
            }
            return false;
        }
	},    
    mounted(){
        var vm = this;
         axios.get(this.commonApi.api.items).then(function(response){
             vm.items = response.data.data.data;
             vm.account  = response.data.data.user;
         })
    }
}
</script>