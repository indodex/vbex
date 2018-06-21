<template>
<div class="col-xs-2">
    <ul class="left-nav">
        <router-link  to="/user" tag="li">
            <a><i class="icon-user"></i>账户概览</a>
        </router-link>
        <router-link  to="/user/hash" tag="li">
            <a><i class="icon-diamond"></i>我的哈希码</a>
        </router-link>
        <router-link  to="/user/" tag="li">
            <a><i class="icon-user-following"></i>我的邀请</a>
        </router-link>
        <router-link  to="/user/mining" tag="li">
            <a><i class="icon-trophy"></i>挖矿奖励</a>
        </router-link>
        <router-link  to="/user/lever" tag="li">
            <a><i class="icon-star"></i>会员等级</a>
        </router-link>
        <router-link  to="/user/api" tag="li">
            <a><i class="icon-drawer"></i>API kyes</a>
        </router-link>
    </ul>
</div>
</template>