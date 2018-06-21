<div class="row">
	<div class="col-md-12">
		<div class="box">
			<div class="box-header">
				<h3>美元总额</h3>
			</div>
			<div class="table-responsive">
			  	<table class="table">
				    <tr >
				      	<th class="info col-md-3">活动总金额</th>
				      	<td class="warning">{{$balance}}</td>
				    </tr>
				    <tr >
				      	<th class="info col-md-3">冻结总金额</th>
				      	<td class="warning">{{$locked}}</td>
				    </tr>
			  	</table>
			</div>
			<div class="box-header">
				<h3>平台虚拟币总量</h3>
			</div>
			<div class="table-responsive">
			  	<table class="table">
			  		@foreach ($currencies as $val)
					    <tr >
					      	<th class="info col-md-3">{{$val['currency']}}</th>
					      	<td class="warning">{{$val['balance']+$val['locked']}}</td>
					    </tr>
					@endforeach
				    
			  	</table>
			</div>
			<div class="box-header">
				<h3>美元充值/提现概览</h3>
			</div>
			<div class="table-responsive">
			  	<table class="table">
				    <!-- <tr >
				      	<th class="info col-md-3">充值总金额</th>
				      	<td class="warning">9999</td>
				    </tr> -->
				    <tr >
				      	<th class="info col-md-3">提现总金额</th>
				      	<td class="warning">{{$withdraws}}</td>
				    </tr>
			  	</table>
			</div>

			<div class="box-header">
				<h3>美元最近24小时</h3>
			</div>
			<div class="table-responsive">
			  	<table class="table">
				    <!-- <tr >
				      	<th class="info col-md-3">充值总金额</th>
				      	<td class="warning">9999</td>
				    </tr> -->
				    <tr >
				      	<th class="info col-md-3">提现总金额</th>
				      	<td class="warning">{{$withdraws24}}</td>
				    </tr>
			  	</table>
			</div>
		</div>

		
	</div>
</div>
