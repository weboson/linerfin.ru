@extends('provider.layout')

@section('content')

    <div id="form">
        <form method="GET" action="{{ route('checking-account.save') }}">
            <input type="hidden" name="subdomain" value="{{$subdomain ?? null}}">
            <header class="form-title">
                Демо-счёт не поддерживает интеграции с банками
            </header>

            <a href="https://demo.linerapp.online/settings/banks" class="btn btn-primary w-100 ">Вернуться в настройки</a>

        </form>
    </div>

@endsection
