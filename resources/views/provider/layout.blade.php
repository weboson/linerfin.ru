<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <title>Linerfin</title>
</head>
<body>

    <div id="linerfin">
        <div class="static-page" id="auth">
            <div id="top-bar">
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <a href="/" class="logo"></a>
                        </div>
                        <div class="col support">
                            <a href="tel:88002228595" class="phone">8-800-222-85-95</a>
                            <div class="subtitle">
                                Поддержка 24/7
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @yield('content')

        </div>
    </div>
    <script src="{{ asset('js/app-auth.js') }}"></script>

</body>
</html>
