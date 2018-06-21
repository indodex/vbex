<template>
	<div>
	  	<div class="modal-body" v-if="params.tradeCode == 1">
	  		<form action="javascript:;" ref="myForm">

	    	<div class="form-group">
	    		<div class="form-control dropdown" >
	    			<a data-toggle="dropdown">{{ $tc('member.tradCodeOption',safe) }}</a>
	    			<ul class="dropdown-menu">
	    				<li v-for="l in safe_option">
	    					<a @click="checkType(l)">{{ $tc('member.tradCodeOption',l[0]) }}</a>
	    				</li>
	    			</ul>
	    		</div>
	    	</div>
	    	<div class="form-group">
        		<input class="form-control" type="password" v-model="tradeCode" :placeholder="$tc('member.tradCode',0)"/>
        	</div>
	    	<div class="form-group" v-loading="!okloaded">
	    		<button class="btn btn-primary form-sub-btn" @click="ok">{{$tc('member.ok',1)}}</button>
	    	</div>
	    	</form>
	  	</div>

	  	<div class="modal-body" v-else>
	  		<div class="single-item">
            	<p class="fs-16 text-gray-light">{{$tc('member.googleTipsSix',1)}}&hellip;</p>
           	</div>
	  	</div>
	  	
  	</div>
</template>

<script>
export default{
	props:['params'],
	data(){
		return {
			tradeCode:'',
			safe:0,
			safe_option:[['0',this.$i18n.t('cmn.select')]],
			tips_select:this.$i18n.t('cmn.select'),
			okloaded:true,
		}
	},
	mounted(){
        var vm = this
        // 审核信息
		axios.get(this.commonApi.api.getTradeOption).then(function(response){
			var res = response.data;
			if (res.code == 200) {
				vm.safe_option = res.data.config;
				vm.safe = vm.params.tradeOption;
				vm.tips_select = vm.safe_option[vm.safe][1];
				
			}
		});
    },
	methods:{
		close(){
			this.$emit('cm');
		},
		checkType(i){
			this.safe = i[0];
			this.tips_select = i[1];
		},
		ok:function(){
			var vm = this;
			vm.okloaded = false;
            axios.post(this.commonApi.api.changeTradCodeOption, {
            	safe:vm.safe,
            	tradeCode:vm.tradeCode
            }).then(function(response){
            	vm.okloaded = true;
                var res = new Object(response.data);
				
				if(res.code == 200){
					vm.close();
					vm.$comfirmbox({ content:res.message, status:res.code })
				}else{
					vm.$comfirmbox({ content:res.message, status:res.code })
				}
            })
            return false;
		}
	}
}
</script>