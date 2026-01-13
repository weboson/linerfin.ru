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

                            @if(\Illuminate\Support\Facades\Auth::check())
                                <div class="account mt-3">
                                    <?php
                                        $user = \Illuminate\Support\Facades\Auth::user();
                                        echo $user->name;
                                        if($user->surname)
                                            echo " ".mb_substr($user->surname, 0, 1).".";
                                        if($user->patronymic)
                                            echo " ".mb_substr($user->patronymic, 0, 1).".";
                                    ?>
                                    <a href="/logout" title="Выйти">
                                        <b-icon-box-arrow-in-right/>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @yield('content')

        </div>
    </div>

    <?php
        // old data
        $old = request()->session()->get('_old_input');

        // company types and VATs
        $companyTypes = \App\Models\OPFType::all()->toArray();
        $companyNDS = \App\Models\NDSType::all()->toArray();
        $taxationSystems = \App\Models\TaxationSystem::all()->toArray();
    ?>

    <script>
        window.oldFormData     = {!! !empty($old) ? json_encode($old) : '{}' !!};
        window.formErrors      = {!! $errors->any() ? json_encode($errors->toArray()) : '{}' !!};
        window.companyTypes    = {!! !empty($companyTypes) ? json_encode($companyTypes) : '[]' !!};
        window.companyNDS    = {!! !empty($companyNDS) ? json_encode($companyNDS) : '[]' !!};
        window.taxationSystems    = {!! !empty($taxationSystems) ? json_encode($taxationSystems) : '[]' !!};
        window.DOMAIN          = '{!! config('app.domain', 'linerfin.ru') !!}';
    </script>
    <script src="{{ asset('js/app-auth.js') }}"></script>

</body>
</html>
