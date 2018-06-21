<template>
	
<div class="ucenter">
	<div class="container">
		<div>
			<left-menu></left-menu>
			<div class="col-xs-10 panel">
				<div class="">
					<div class="theme-panel">
						<div class="theme-title text-center">CHBTC全新会员体系——越交易，越优惠</div>
						<div class="theme-body mt20">
							<div class="row">
								<span class="col-sm-3">当前用户等级：<span class="curren-lever">VIP3</span></span>                             
								<span class="col-sm-3">共有积分：<span class="text-primary">114,789</span></span>      
								<span class="col-sm-6 text-right"><a href="" class="text-primary">积分等级与费率标准>></a></span>
							</div>
						</div>
						<div class="panel-header noBorder">
							我的交易及提现费率
							<span class="pull-right">
		                		<a class="btn btn-primary copy-btn btn-br4" role="button">积分等级与费率标准</a>
		                	</span>
						</div>
						<div class="panel-body ucenter-panel">
							<div class="row">
								<table class="table table-bordered table-lever">
		                            <tbody class="bottom-border text-center">
		                             	<tr>
		                                    <th rowspan="2">人民币<br>提现费率</th>                                             
		                                    <th colspan="9">Maker交易费率</th>
		                                    <th colspan="9">Taker交易费率</th>
		                                </tr>
		                                <tr class="bitype">	                                                                                 
		                                    <td>BTC</td>
		                                    <td>LTC</td>
		                                    <td>ETH</td>
		                                    <td>ETC</td>
		                                    <td>BTS</td>
		                                    <td>EOS</td>
		                                    <td>BCC</td>
		                                    <td>QTUM</td>
		                                    <td>HSR</td>
		                                    <td>BTC</td>
		                                    <td>LTC</td>
		                                    <td>ETH</td>
		                                    <td>ETC</td>
		                                    <td>BTS</td>
		                                    <td>EOS</td>
		                                    <td>BCC</td>
		                                    <td>QTUM</td>
		                                    <td>HSR</td>
		                                </tr>
		                                <tr>	  
		                                <td>BTC</td>                                                                               
		                                    <td>0.20%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                    <td>0.17%</td>
		                                </tr>
		                            </tbody>
		                        </table>
								<div class="clearfix"></div>
								<div class="panel-header mt20">积分明细</div>
								<form class="form-inline mt20 form-lever" role="form">
									<div class="row">
									  	<div class="col-xs-3 form-group">
										    <label class="type" for="name">类型：</label>
										    <select class="form-control">
										      <option>--请选择--</option>
										      <option>2</option>
										      <option>3</option>
										      <option>4</option>
										      <option>5</option>
										    </select>
									  	</div>
									  	<div class="form-group col-xs-7 ">
										    <label class="from-time" for="name">时间：</label>
										    从
										    <input type="text" class="form-control" id="from-time" placeholder="">
										      到  
										    <input type="text" class="form-control" id="endTime" placeholder="">
									  	</div>
									  	<button type="submit" class="btn">提交</button>
									  	<button type="reset" class="btn">重置</button>
									</div>
								</form>
								<ul id="myTab" class="nav nav-tabs mt20 lever-tab">
								    <li class="active">
								        <a href="#all" data-toggle="tab">
								            所有
								        </a>
								    </li>
								    <li><a href="#in" data-toggle="tab">获得</a></li>
								    <li><a href="#out" data-toggle="tab">消费</a></li>
								</ul>
								<div id="myTabContent" class="tab-content lever-tab-content mt10">
								    <div class="tab-pane fade in active" id="all">
								       <table class="table text-center">
				                            <thead>	                 	          	              	                 	             
				                                <tr>
				                                    <th>时间</th>
				                                    <th>类型</th>
				                                    <th>积分</th>
				                                    <th>描述</th>
				                                </tr>
				                            </thead>
				                            <tbody class="bottom-border">
				                                <tr>	                                                                                 
				                                    <td>
														2017-10-09 02:05:55 
				                                    </td>
				                                    <td>
														lanminghai
				                                    </td>
				                                    <td>
														<span class="text-danger">+11222</span> 
				                                    </td>
				                                    <td>昨天净资产积分</td>
				                                </tr>
				                                <tr>	                                                                                 
				                                    <td>
														lanminghai@qq.com
				                                    </td>
				                                    <td>
														lanminghai
				                                    </td>
				                                    <td>
														<span class="text-success">+11222</span> 
				                                    </td>
				                                    <td>
														2017-10-09 02:05:55 
				                                    </td>
				                                </tr>
				                            </tbody>
				                        </table>
							    	</div>
								    <div class="tab-pane fade" id="in">
								       <table class="table text-center">
				                            <thead>	                 	          	              	                 	             
				                                <tr>
				                                    <th>时间</th>
				                                    <th>类型</th>
				                                    <th>积分</th>
				                                    <th>描述</th>
				                                </tr>
				                            </thead>
				                            <tbody class="bottom-border">
				                                <tr>	                                                                                 
				                                    <td>
														2017-10-09 02:05:55 
				                                    </td>
				                                    <td>
														lanminghai
				                                    </td>
				                                    <td>
														<span class="text-danger">+11222</span> 
				                                    </td>
				                                    <td>昨天净资产积分</td>
				                                </tr>
				                                <tr>	                                                                                 
				                                    <td>
														lanminghai@qq.com
				                                    </td>
				                                    <td>
														lanminghai
				                                    </td>
				                                    <td>
														<span class="text-success">+11222</span> 
				                                    </td>
				                                    <td>
														2017-10-09 02:05:55 
				                                    </td>
				                                </tr>
				                            </tbody>
					                    </table>
								    </div>
								    <div class="tab-pane fade" id="out">
								        <table class="table text-center">
				                            <thead>	                 	          	              	                 	             
				                                <tr>
				                                    <th>时间</th>
				                                    <th>类型</th>
				                                    <th>积分</th>
				                                    <th>描述</th>
				                                </tr>
				                            </thead>
				                            <tbody class="bottom-border">
				                                <tr>	                                                                                 
				                                    <td>
														2017-10-09 02:05:55 
				                                    </td>
				                                    <td>
														lanminghai
				                                    </td>
				                                    <td>
														<span class="text-danger">+11222</span> 
				                                    </td>
				                                    <td>昨天净资产积分</td>
				                                </tr>
				                                <tr>	                                                                                 
				                                    <td>
														lanminghai@qq.com
				                                    </td>
				                                    <td>
														lanminghai
				                                    </td>
				                                    <td>
														<span class="text-success">+11222</span> 
				                                    </td>
				                                    <td>
														2017-10-09 02:05:55 
				                                    </td>
				                                </tr>
				                            </tbody>
					                    </table>
								    </div>
								</div>	                      	                               	                                     
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</template>

<script>
    import LeftMenu from './LeftMenu'
    export default {
        components: {
            LeftMenu
        }
    }

</script>



<style>
  @import url('/css/hac-content.css');
</style>



