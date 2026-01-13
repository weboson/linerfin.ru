@extends('auth2.layout')


@section('content')

    <div id="form">
        <header class="form-title">
            Доступ к amoCRM
            <div class="subtitle">
                Подтвердите предоставление доступа для amoCRM
            </div>
        </header>

        <p>
            {{ $user->name }}, подтвердите предоставление доступа к аккаунту
            {{ "https://$subdomain.amocrm.ru/" }}
        </p>

        <form method="POST">
            @csrf
            <input type="hidden" name="account" value="{{ $subdomain }}">
            <input type="hidden" name="hash" value="{{ $hash }}">
            <footer>
                <button class="btn btn-primary w-100" type="submit">Разрешить</button>
                <br>
                <a href="#" class="text-secondary btn p-0 mt-2">Блокировать</a>
            </footer>
        </form>
    </div>

@endsection
