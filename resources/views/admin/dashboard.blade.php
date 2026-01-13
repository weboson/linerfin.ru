@extends('auth2.layout')

@section('content')
    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">Пользователи</a>
@endsection
