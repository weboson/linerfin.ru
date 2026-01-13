@extends('auth2.layout')

@section('content')

    <div id="form">
        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <header class="form-title">
                Восстановление пароля
            </header>

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

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

            <!-- Email Address -->
            <input name="email" type="hidden" value="{{ old('email', $request->email) }}">

            <div class="form-group">
                <label for="email">E-Mail</label>
                <input id="email" name="email" type="email" class="form-control"
                       value="{{ old('email', $request->email) }}" required disabled>
            </div>


            <!-- Password -->
            <div class="form-group">
                <label for="password">Новый пароль</label>
                <input id="password" name="password" type="password" class="form-control" required autofocus>
            </div>


            <!-- Confirm Password -->
            <div class="form-group">
                <label for="password_confirmation">Подтвердите пароль</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
            </div>


            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="btn btn-primary">
                    Сохранить
                </button>
            </div>
        </form>
    </div>
@endsection
