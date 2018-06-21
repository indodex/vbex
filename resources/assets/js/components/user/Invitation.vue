<template>
<div class="ucenter">
    <div class="container">
        <div>
            <left-menu></left-menu>
            <div class="col-xs-10 panel">
                <div class="">
                    <div class="panel-header">
                        我的邀请
                        <span class="fs-16 text-primary">
                            <i class="icon-question"></i>
                            邀请奖励规则
                        </span>
                    </div>
                    <div class="panel-body ucenter-panel invi-panel">
                        <div class="row">
                            <div class="item-list">
                                <div class="col-xs-12 col-sm-6 col-md-4">
                                    <div class="item in-count">
                                        <div class="t">累计邀请人数：</div>
                                        <div class="b">100</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-6 col-md-4">
                                    <div class="item in-jl">
                                        <div class="t">累计邀请奖励：</div>
                                        <div class="b">100HAC</div>
                                        <a class="btn btn-default" role="button">查看明细</a>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-6 col-md-4">
                                    <div class="item in-code">
                                        <div class="t">我的推荐码：</div>
                                        <div class="b">E23rFt</div>
                                        <a class="btn btn-default" role="button">复制推荐码</a>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="panel-header mt20">邀请好友</div>
                            <div class="mt20">推荐V网给他人，即可享受被推荐者交易手续费的反利奖励</div>
                            <div class="item-list">
                                <div class="col-xs-12 col-sm-6">
                                    <div class="item in-link">
                                        <div class="media">
                                            <div class="media-left">
                                                方法一：
                                            </div>
                                            <div class="media-body">
                                                <div class="">复制您的专属推荐地址给好友</div>
                                                <div class="text-primary mt10">http://i.hashtoken.org/cn/register/1</div>
                                                <div class="mt20">
                                                    <a class="btn btn-primary copy-btn btn-br4" role="button">复制链接</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <div class="item in-link">
                                        <div class="media">
                                            <div class="media-left">
                                                方法二：
                                            </div>
                                            <div class="media-body">
                                                <div class="">分享下方二维码给好友，完成注册</div>
                                                <div class="mt10">
                                                    <img src="http://cc.cocimg.com/api/uploads/171030/0635fe7763ec2800d9ae54e8dc2543b6.jpg" width="120px" height="120px">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="panel-header mt20">累计邀请明细</div>
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
                                            lanminghai@qq.com
                                        </td>
                                        <td>
                                            2017-10-09 02:05:55 
                                        </td>
                                        <td>2019-09-28</td>
                                        <td>yingying</td>
                                    </tr>
                                    <tr>                                                                                     
                                        <td>
                                            lanminghai@qq.com
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



