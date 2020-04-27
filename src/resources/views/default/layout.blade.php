<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>@yield('ja-lego-title', env('APP_NAME'))</title>

    @include(config('ja-lego.views.styles'))
</head>
<body class="@yield('ja-lego-body-class', 'layui-layout-body')">

@section('ja-lego-body-content')
@show

@include(config('ja-lego.views.scripts'))

</body>
</html>
