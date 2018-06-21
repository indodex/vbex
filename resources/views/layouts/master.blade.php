<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html> <!--<![endif]-->
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv = "X-UA-Compatible" content = "IE=edge,chrome=1" />
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <title>{{ __('public.site_title') }}</title>
    <meta name="keywords" content="{{ __('public.site_keywords') }}">
    <meta name="description" content="{{ __('public.site_description') }}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <script type="text/javascript">
    	const hostUrl = '{{ env("APP_URL") }}';
    	const rechargeCodeSwitch = '{{ get_config("recharge_code_switch") }}';
    </script>
</head>
<body>

<div id="app">
    <app></app>
</div>

<script src="{{ asset('js/app.js') }}?v=2018052301"></script>
</body>
</html>
