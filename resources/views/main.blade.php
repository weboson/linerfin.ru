<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/SF UI Display/sf_ui_display.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <title>
        @if(!empty($account) && !empty($account->name))
            {{ $account->name }} -
        @endif
        Linerfin
    </title>
</head>
<body>
<div id="linerfin">
    <application></application>
</div>

<script>
    var APPDATA = {
        DOMAIN: "{{ config('app.domain', 'linerfin.ru') }}"
    };
</script>
<script src="{{ asset('js/app.js') }}"></script>

</body>
</html>
