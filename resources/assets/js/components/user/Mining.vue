<template>
    <div class="ucenter">
    <div class="container">
        <div>
            <left-menu></left-menu>
            <div class="col-xs-10 panel">
                <div class="">
                    <div class="panel-header">交易证明机制(POT)挖矿奖励机制</div>
                    <div class="panel-body ucenter-panel">
                        <div class="row">
                            <div class="text-muted lh-30 mt20">
                            创世区块挖矿难度：全站数字资产交易额每达到等值的1000USD出一个区块、每个区块产出100个币、根据用户对此区块的交易额贡献大小、按交易额比例获得HAC奖励每个区块产量每年减半、区块挖矿难度每个月难度增加30%
                            </div>
                            <div class="row my-wkjl">
                            <div class="col-xs-12 col-md-3">
                                我的挖矿奖励：10000HAC
                            </div>
                            <div class="col-xs-12 col-md-4">
                                当前挖矿难度：1000USD
                            </div>
                            <div class="col-xs-12 col-md-5">
                                当前区块奖励：1000HAC
                            </div>                                                                                 
                            </div>
                            <div class="panel-header">交易返利明细<span class="pull-right">累计交易返利：300HAC</span></div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>被邀请人账号</th>
                                        <th>注册时间</th>
                                        <th>有效期</th>
                                        <th>状态</th>
                                    </tr>
                                </thead>
                                <tbody class="bottom-border">
                                    <tr>                                                                                     
                                        <td>
                                            <div>lanminghai@qq.com</div>
                                        </td>
                                        <td>
                                            2017-10-09 02:05:55 
                                        </td>
                                        <td>2019-09-28</td>
                                        <td>yingying</td>
                                    </tr>
                                    <tr>                                                                                     
                                        <td>
                                            <div>lanminghai@qq.com</div>
                                        </td>
                                        <td>
                                            2017-10-09 02:05:55 
                                        </td>
                                        <td>2019-09-28</td>
                                        <td>yingying</td>
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



