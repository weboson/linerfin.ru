@extends('auth2.layout')

@section('content')

    <div id="form">
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <header class="form-title">
                Авторизация
            </header>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="validation-errors">
                    <p>Не удалось авторизоваться</p>

                    <ul class="text-danger">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('status'))
                <div class="text-success mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="form-group">
                <input-ui type="email" value="{{ old('email') }}" name="email" placeholder="Электронная почта" required autofocus/>
            </div>
            <div class="form-group">
                <input-ui type="password" name="password" placeholder="Пароль" required/>
            </div>

            <p class="d-flex justify-content-between">
                <a href="{{ route('password.request') }}">
                    <small>Забыли пароль</small>
                </a>
                <label>
                    <small class="text-secondary">
                        Запомнить меня
                    </small>
                    <input class="ml-1" type="checkbox" name="remember" value="1" checked>
                </label>
            </p>

            <button type="submit" class="btn btn-primary w-100 disabled">Войти</button>

            <footer>
                Я здесь впервые. <a href="{{ route('register') }}">Регистрация</a>
            </footer>
        </form>
    </div>

@endsection
