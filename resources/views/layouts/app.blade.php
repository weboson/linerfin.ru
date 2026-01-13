<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <title>Document</title>
</head>
<body>

<div class="static-page">
    <div id="top-bar">
        <div class="container">
            <div class="row">
                <div class="col">
                    <a href="https://linerapp.online" class="logo"></a>
                </div>
<!--                <div class="col support">
                    <a href="tel:88002228595" class="phone">8-800-222-85-95</a>
                    <div class="subtitle">
                        Поддержка 24/7
                    </div>
                </div>-->
            </div>
        </div>
    </div>

    @yield('content')

</div>

</body>
</html>
