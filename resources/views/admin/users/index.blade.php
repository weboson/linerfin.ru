@extends('auth2.layout')

@section('content')
    <main class="w-100 h-100 pt-5">
        <div class="container pt-5">
            <h3 class="mb-3">
                Пользователи
            </h3>

            <table class="table bg-white">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Контакты</th>
                    <th>Дата регистрации</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)

                <!-- main info -->
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->surname }} {{ $user->name }} {{ $user->patronymic }}</td>
                    <td>
                        @if($user->email)
                            <a href="mailto:{{$user->email}}">{{$user->email}}</a>
                        @endif
                        @if($user->phone)
                            <a href="tel:{{$user->phone}}">{{$user->phone}}</a>
                        @endif
                    </td>
                    <td>
                        {{ $user->created_at }}
                    </td>
                    <td>
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-primary btn-sm">
                            Просмотр
                        </a>
                    </td>
                </tr>

                @endforeach
                </tbody>
            </table>
        </div>
    </main>
@endsection
