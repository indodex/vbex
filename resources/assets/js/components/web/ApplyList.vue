<template>
	<div class="ucontainer nomenu">
		<div class="panel clearfix">
			<div class="col-lg-12">
				<div class="applyTitle text-center">
					<h3>上币申请</h3>
					<p>
						请填写以下表单，V网 Global团队将在收到申请后尽快回复您。
					</p>
					<p>
						<router-link to="/apply/recode"><button class="btn btn-primary">申请记录</button></router-link>
					</p>
				</div>
				<form class="trad-form">
	        		<div class="form-group">
	        			<input type="text" v-model="form.email" placeholder="邮箱地址(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.budget" placeholder="微信号(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.phone" placeholder="联系电话(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.position" placeholder="联系人姓名与职称(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.welfare" placeholder="项目名称(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.coinName" placeholder="数字货币名称(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.coinCode" placeholder="数字货币编码(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.coinUrl" placeholder="项目地址(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.issueTime" placeholder="发行时间(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.issueTotal" placeholder="发行总量和发行规则(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.jetton" placeholder="筹码分布(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.coinType" placeholder="这是什么类型的数字货币?(公有链、分叉币、ERC20代币，ICO币等, 必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.icoPrice" placeholder="成本价(早鸟阶段、公募等), 如果是ICO, 以什么币众筹? BTC, ETH还是其他? (必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.icoRecord" placeholder="众筹记录 (众筹总量、众筹所用时间, 必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<textarea type="text" v-model="form.purpose" placeholder="它们的主要用途是什么?请介绍您的项目和应用场景(必填)" class="form-control"></textarea>
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.userNumber" placeholder="社区用户量有多少?(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.issueCountry" placeholder="您数字货币是在哪个国家发行的?(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.bourse" placeholder="哪些交易所已上线该数字货币?(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<textarea type="text" v-model="form.team" placeholder="请谈谈您的团队(必填)" class="form-control"></textarea>
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.address" placeholder="团队办公地点(具体到地区、省、市, 必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.paperUrl" placeholder="白皮书链接(必填)" class="form-control">
	        		</div>
	        		<div class="form-group">
	        			<input type="text" v-model="form.codeUrl" placeholder="代码开源链接(例如：Github链接, 必填)" class="form-control">
	        		</div>
	        		<div class="text-center">
	        		<a class="btn btn-primary" @click="submit($event)">确认提交</a>
	        		</div>
		        </form>
	        </div>
		</div>
	</div>
</template>

<script>
export default {
	data(){
		return {
			form:{
				email:'',
				budget:'',
				phone:'',
				position:'',
				welfare:'',
				coinName:'',
				coinCode:'',
				coinUrl:'',
				issueTime:'',
				issueTotal:'',
				jetton:'',
				coinType:'',
				icoPrice:'',
				icoRecord:'',
				purpose:'',
				userNumber:'',
				issueCountry:'',
				bourse:'',
				team:'',
				address:'',
				paperUrl:'',
				codeUrl:''
			}
		}
	},
	methods:{
		submit($event){
			$event.preventDefault()
			let vm = this;
			let uncom = false;
			for(let i in vm.form){
				if(!vm.form[i]) {
					vm.$comfirmbox({ title:'', content:'请完成申请信息' });
					uncom = true;
					return false;
				}
			}
			
			if(!uncom){
				axios.post(this.commonApi.api.applyRequest, vm.form)
				 .then(function(response){
					var res = new Object(response.data.data);
	             	vm.$comfirmbox({ title:'', content:response.data.message, type:'success' })
	           	});
	       	}
		}
	}
}
</script>
