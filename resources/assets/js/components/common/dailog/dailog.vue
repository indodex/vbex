<template>
	<!--fade-->
<div class="modal" :class="'bs-example-modal-' + boxsize" :id="boxid + '-dailog'" role="dialog" >
	<div class="modal-dialog" :class="'modal-' + boxsize" >
		<div class="modal-content">
		    <div class="modal-header">
		        <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->
		        <a class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-close"></i></a>
		        <h4 class="modal-title fs-16">{{boxtitle}}</h4>
	      	</div>
	      	
	      	<!--modal content-->
			<dailog-pwd v-if="curview == 'pwd'" :params="params" @cm="closeModal"></dailog-pwd>
			<dailog-mail v-if="curview == 'mail'" :params="params" @cm="closeModal"></dailog-mail>
			<dailog-phone v-if="curview == 'phone'" :params="params" @cm="closeModal"></dailog-phone>
			<dailog-google v-if="curview == 'google'" :params="params" @cm="closeModal"></dailog-google>
			<dailog-trad-code v-if="curview == 'tradcode'" :params="params" @cm="closeModal"></dailog-trad-code>
			<dailog-withdraw v-if="curview == 'withdraw'" :params="params" @cm="closeModal"></dailog-withdraw>
			<dailog-withdraw-delete v-if="curview == 'withdrawdelete'" :params="params" @cm="closeModal"></dailog-withdraw-delete>
			<dailog-hash v-if="curview == 'hash'" :params="params" @cm="closeModal"></dailog-hash>
			<dailog-hash-check v-if="curview == 'hashcheck'" :params="params" @cm="closeModal"></dailog-hash-check>
			<dailog-hash-use v-if="curview == 'hashuse'" :params="params" @cm="closeModal"></dailog-hash-use>
			<dailog-indentify v-if="curview == 'indentify'" :params="params" @cm="closeModal"></dailog-indentify>
			<dailog-indentify-adv v-if="curview == 'indentifyadv'" :params="params" @cm="closeModal"></dailog-indentify-adv>
			<dailog-info v-if="curview == 'info'" :params="params" @cm="closeModal"></dailog-info>
			<dailog-bills v-if="curview == 'bills'" :params="params" @cm="closeModal"></dailog-bills>
			<dailog-safe v-if="curview == 'safe'" :params="params" @cm="closeModal"></dailog-safe>
			<dailog-forget-trad-code v-if="curview == 'forgettradcode'" :params="params" @cm="closeModal"></dailog-forget-trad-code>
			<dailog-trading v-if="curview == 'tradingPwd'" :params="params" @cm="closeModal"></dailog-trading>
			<dailog-change-google v-if="curview == 'changegoogle'" :params="params" @cm="closeModal"></dailog-change-google>
			<dailog-forget-google v-if="curview == 'forgetgoogle'" :params="params" @cm="closeModal"></dailog-forget-google>
			<dailog-trade-option v-if="curview == 'tradeoption'" :params="params" @cm="closeModal"></dailog-trade-option>
			<dailog-withdrawal-option v-if="curview == 'withdrawaloption'" :params="params" @cm="closeModal"></dailog-withdrawal-option>
			<!--modal content end-->
		</div>
	</div>
</div>
</template>


<script>
import dailogPwd from './dailog-pwd'
import dailogMail from './dailog-mail'
import dailogPhone from './dailog-phone'
import dailogTradCode from './dailog-trad-code'
import dailogGoogle from './dailog-google'
import dailogWithdraw from './dailog-withdraw'
import dailogWithdrawDelete from './dailog-withdraw-delete'
import dailogHash from './dailog-hash'
import dailogHashCheck from './dailog-hash-check'
import dailogHashUse from './dailog-hash-use'
import dailogIndentify from './dailog-indentify'
import dailogIndentifyAdv from './dailog-indentify-adv'
import dailogInfo from './dailog-info'
import dailogBills from './dailog-bills'
import dailogSafe from './dailog-safe'
import dailogForgetTradCode from './dailog-forget-trad-code'
import dailogTrading from './dailog-trading'
import dailogChangeGoogle from './dailog-change-google'
import dailogForgetGoogle from './dailog-forget-google'
import dailogTradeOption from './dailog-trade-option'
import dailogWithdrawalOption from './dailog-withdrawal-option'
export default{
	components:{
		dailogMail,
		dailogPwd,
		dailogPhone,
		dailogTradCode,
		dailogGoogle,
		dailogWithdraw,
		dailogWithdrawDelete,
		dailogHash,
		dailogHashCheck,
		dailogHashUse,
		dailogIndentify,
		dailogIndentifyAdv,
		dailogInfo,
		dailogBills,
		dailogSafe,
		dailogForgetTradCode,
		dailogTrading,
		dailogChangeGoogle,
		dailogForgetGoogle,
		dailogTradeOption,
		dailogWithdrawalOption
	},
	props:['boxid','boxtitle','boxsize','params'],
	data(){
		return{
			curview:''
		}
	},
	mounted(){
		var vm = this;
//		modal open
		$('#'+vm.boxid+'-dailog').on('shown.bs.modal',function(){
			vm.curview = vm.boxid
		}).on('hidden.bs.modal',function(){
			vm.curview = ''
		});
	},
	methods:{
		closeModal(val){
			var vm = this;
			$('#'+vm.boxid+'-dailog').modal('hide');
			this.$emit('modalcallback',val);
		}
	}
}
</script>
