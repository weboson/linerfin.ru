@extends('auth2.layout')

@section('content')

    <div id="form">
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <header class="form-title">
                Восстановление пароля
            </header>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="validation-errors">
                    <p>Ошибка восстановления пароля</p>

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

            <button type="submit" class="btn btn-primary w-100">Восстановить</button>

            <footer>
                Вернуться ко <a href="{{ route('login') }}">Входу</a>
            </footer>
        </form>
    </div>

@endsection
