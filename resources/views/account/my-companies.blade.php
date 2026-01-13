@extends('auth2.layout')


@section('content')

    <div id="form">
        <header class="form-title">
            Мои компании
            <div class="subtitle">
                Выберите компанию или создайте новую
            </div>
        </header>

        @if(!empty($companies))
            <ul class="account__company-list">
                @foreach($companies as $company)
                    <li>
                        @if(!$company['is_demo'])
                            <a href="{{ "http://" . $company['subdomain'] . "." . config('app.domain', 'linerfin.ru')}}"
                               class="position-relative">
                                {{ $company['name'] }}
                                <small class="subtitle position-absolute text-muted"
                                       style="top: 5px; right: 5px; font-size: 12px">
                                    {{ $company['subdomain'] . "." . config('app.domain', 'linerfin.ru')}}
                                </small>
                            </a>
                        @else
                            <a href="{{ "http://demo." . config('app.domain', 'linerfin.ru')}}"
                               class="position-relative">
                                {{ $company['name'] }}
                                <small class="subtitle position-absolute text-success"
                                       style="top: 5px; right: 5px; font-size: 12px">
                                    демо-режим
                                </small>
                            </a>
                        @endif

                    </li>
                @endforeach
            </ul>
        @else
            Нет ни-одной компании
        @endif
        <footer class="text-right">
            <a href="/new-company" class="d-inline-block">
                <b-icon-plus class="icon-left"></b-icon-plus>
                <span>Новая компания</span>
            </a>
        </footer>
    </div>

@endsection
