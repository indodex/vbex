<template>
	<div>
	  	<div class="modal-body">
	  		<form action="javascript:;" :model="myForm" ref="myForm">
	  		<!--<p class="fs-20">高级验证</p>-->
	  		<div class="form-group">
	    		<div class="updata-box row">
	    			<div class="col-sm-6 ">
	    				<div class="inner">
	    					<span class="plchold text-primary id-hold"></span>
	    					<label class="cover" :style="'background-image:url('+ myForm.advanced_path +')'">
	    						<input type="file" @change="getFile($event,'advanced')"/>
	    					</label>
	    				</div>
	    			</div>
	    			<div class="col-sm-6 text-gray-light fs-12">
	    					{{$tc('member.cerTips',2)}}
	    			</div>
	    		</div>
	    	</div>
	    	<div class="form-group" v-if="myForm.advanced_status == -1">
	    		<div class="from-tips " style="color:red;">
					{{$tc('member.checkTips',1)}}{{ myForm.remark }}
	    		</div>
	    	</div>
	    	<div class="form-group">
	    		<button class="btn btn-primary form-sub-btn" @click="ok">{{$tc('member.ok',1)}}</button>
	    	</div>
	    	</form>
	  	</div>
  	</div>
</template>

<script>
export default{
	data(){
		return {
			certype:'',
			myForm:{
				// id:'',
				advanced:'',
				advanced_path:'',
				advanced_status:0,
				remark:'',
			}
		}
	},
	mounted(){
        var vm = this
        // alert(1);
        // 审核信息
		axios.get(this.commonApi.api.getCerInfo).then(function(response){
			// var data = response.data.data;
			var res = new Object(response.data);
			if (res.code == 200) {
				vm.myForm.advanced = res.data.advanced;
	        	vm.myForm.advanced_path = 'http://' + window.location.host + '/' + res.data.advancedPath;
				vm.myForm.advanced_status = res.data.advanced_status;
				vm.myForm.remark = res.data.remark;
			}
		})
    },
	methods:{
		certypecheck(i){
			
		},
		close(){
			this.$emit('cm');
		},
		getFile(e,s){
			// this.myForm[s] = e.target.files[0];
			var vm = this;
			var formdata = new FormData();
			formdata.append(s,e.target.files[0]);
			formdata.append('name',s);
			axios.post(this.commonApi.api.uploadIamge, 
            	formdata).then(function(response){
                var res = new Object(response.data);
                vm.myForm[s] = res.data.id;
                vm.myForm[s+'_path'] = 'http://' + window.location.host + '/' + res.data.path;
            })
		},
		ok:function(){
			var vm = this;
			// alert(vm.myForm.papers_before);return false;
            axios.post(this.commonApi.api.advancedCertification, {
            	advanced:vm.myForm.advanced,
            }).then(function(response){
                var res = new Object(response.data);
                // vm.close();
                vm.$comfirmbox({ content:res.message, status:res.code })
                vm.close();
            })
            return false;
		}
	}
}
</script>