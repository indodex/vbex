<template>
<div class="ucenter">
    <div class="container">
    	<div>
    		<left-menu></left-menu>
    		<div class="col-xs-10 panel">
    			<div class="">
    				<div class="panel-header">API kyes</div>
    				<div class="panel-body ucenter-panel">
    					<div class="row">
    						<table class="table">
                                <thead>
                                    <tr>
                                        <th>API kyes</th>
                                        <th>启用</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
    										<div>Key：4647484839393933939393933a</div>
    										<div>Secret：*</div>
                                        </td>
                                        <td>
    										<div id="mySwitch" class="switch" data-on="success">
    										    <input type="checkbox" checked />
    										</div>
                                        </td>
                                        <td><a class="text-info" href="#">删除</a></td>
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

