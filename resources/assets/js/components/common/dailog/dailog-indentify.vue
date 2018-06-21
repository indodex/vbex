<template>
	<div>
	  	<div class="modal-body">
	  		<form action="javascript:;" :model="myForm" ref="myForm">
	    	<div class="form-group">
	    		<div class="from-tips text-center">{{$tc('member.cerTips',0)}}<br />{{$tc('member.cerTips',1)}}</div>
	    	</div>
	    	<div class="form-group">
	    		<!--<p>姓名</p>-->
	    		<input class="form-control" v-model="myForm.name" :placeholder="$tc('member.name',1)"/>
	    	</div>
	    	<!-- <div class="form-group">
	    		<p>国家/地区</p>
	    		<div class="form-control dropdown" >
	    			<a data-toggle="dropdown">请选择</a>
	    			<ul class="dropdown-menu">
	    				<li><a>请选择</a></li>
	    				<li><a>123123131</a></li>
	    				<li><a>123123131</a></li>
	    				<li><a>123123131</a></li>
	    			</ul>
	    		</div>
	    	</div> -->
	    	<div class="form-group">
	    		<input class="form-control" v-model="myForm.birthday" :placeholder="$tc('member.birthday',1)"/>
	    	</div>
	    	<div class="form-group">
	    		<div class="form-control dropdown" >
	    			<a data-toggle="dropdown">{{tips_select}}</a>
	    			<ul class="dropdown-menu">
	    				<li v-for="l in list"list><a @click="checkType(l)">{{ l[1] }}</a></li>
	    			</ul>
	    		</div>
	    	</div>
	    	
	    	<div class="form-group">
	    		<input class="form-control" v-model="myForm.papers_number" :placeholder="$tc('member.papersNumber',1)"/>
	    	</div>
	    	
	    	<div class="form-group">
	    		<p>{{$t('member.photoTips')}}</p>
	    		<div class="updata-box row">
	    			<div class="col-sm-6 ">
	    				<div class="inner" v-loading='!myForm.papers_before_img_loaded'>
	    					<span class="plchold text-primary id-front"></span>
	    					<label class="cover" :style="'background-image:url('+ myForm.papers_before_path +')'">
	    						<input type="file" @change="getFile($event,'papers_before')"/>
	    					</label>
	    				</div>
	    			</div>
	    			<div class="col-sm-6">
	    				<div class="inner" v-loading='!myForm.papers_after_img_loaded'>
	    					<span class="plchold text-primary id-back"></span>
	    					<label class="cover" :style="'background-image:url('+ myForm.papers_after_path +')'">
	    						<input type="file" @change="getFile($event,'papers_after')"/>
	    					</label>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    	
	    	<div class="form-group">
	    		<label class="radio-inline" v-for="s in sexs">
	    			<input type="radio" name="sex" :value="s[0]" v-model="myForm.sex"/>{{s[1]}}
	    		</label>
	    	</div>
	    	
	    	<!-- <div class="form-group">
	    		<p>出生日月</p>
	    		<input class="form-control"/>
	    	</div> -->
	    	
	    	<div class="form-group">
	    		<input class="form-control" v-model="myForm.profession" :placeholder="$tc('member.profession',1)" />
	    	</div>
	    	
	    	<div class="form-group">
	    		<input class="form-control" v-model="myForm.address" :placeholder="$tc('member.address',1)" />
	    	</div>

	    	<div class="form-group" v-if="myForm.status == -1">
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
			list:[['0','请选择'],['1','身份证'],['2','护照']],
			sexs:[['1','男'],['2','女']],
			tips_select:'请选择',
			myForm:{
				name:'',
				birthday:'',
				papers_type:'',
				papers_number:'',
				papers_before:'',
				papers_after:'',
				sex:'',
				profession:'',
				address:'',
				advanced:'',
				papers_after_path:'',
				papers_before_path:'',
				papers_after_img_loaded:true,
				papers_before_img_loaded:true,
				status: 0,
				remark:''
			}
		}
	},
	mounted(){
        var vm = this
        // 审核信息
		axios.get(this.commonApi.api.getCerInfo).then(function(response){
			// var data = response.data.data;
			var res = new Object(response.data);
			if (res.code == 200) {
				// vm.myForm.id = res.data.id;
				vm.myForm.name = res.data.name;
	        	vm.myForm.birthday = res.data.birthday;
	        	vm.myForm.papers_type = res.data.papersType;
	        	vm.tips_select = vm.list[res.data.papersType][1];
	        	vm.myForm.papers_number = res.data.papersNumber;
	        	vm.myForm.papers_before = res.data.papersBefore;
	        	vm.myForm.papers_after = res.data.papersAfter;
	        	vm.myForm.sex = res.data.sex;
	        	vm.myForm.profession = res.data.profession;
	        	vm.myForm.address = res.data.address;
	        	vm.myForm.papers_after_path = 'http://' + window.location.host + '/' + res.data.papersAfterPath;
	        	vm.myForm.papers_before_path = 'http://' + window.location.host + '/' + res.data.papersBeforePath;
	        	vm.myForm.status = res.data.status;
	        	vm.myForm.remark = res.data.remark;
			}
		});
    },
	methods:{
		certypecheck(i){
			
		},
		close(){
			this.$emit('cm');
		},
		checkType(i){
			this.myForm.papers_type = i[0];
			this.tips_select = i[1];
		},
		getFile(e,s){
			// this.myForm[s] = e.target.files[0];
			var vm = this;
			var formdata = new FormData();
			formdata.append(s,e.target.files[0]);
			formdata.append('name',s);
			vm.myForm[s+'_img_loaded'] = false;
			axios.post(this.commonApi.api.uploadIamge, formdata).then(function(response){
				var res = response.data;
				if (res.code == 200) {
	                vm.myForm[s] = res.data.id;
	                vm.myForm[s+'_path'] = 'http://' + window.location.host + '/' + res.data.path;
				}else{
					vm.$comfirmbox({ content:res.message, status:res.code })
				}
				vm.myForm[s+'_img_loaded'] = true;
            })
		},
		ok:function(){
			var vm = this;
			// alert(vm.myForm.papers_before);return false;
            axios.post(this.commonApi.api.baseCertification, {
            	name:vm.myForm.name,
            	birthday:vm.myForm.birthday,
            	papers_type:vm.myForm.papers_type,
            	papers_number:vm.myForm.papers_number,
            	papers_before:vm.myForm.papers_before,
            	papers_after:vm.myForm.papers_after,
            	sex:vm.myForm.sex,
            	profession:vm.myForm.profession,
            	address:vm.myForm.address,
            }).then(function(response){
                var res = new Object(response.data);
				vm.$comfirmbox({ content:res.message, status:res.code })
				if(res.code == 200){
					vm.close();
				}
            })
            return false;
		}
	}
}
</script>