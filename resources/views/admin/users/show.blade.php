@extends('auth2.layout')

@section('content')
    <main class="w-100 h-100 pt-5">
        <div class="container pt-5">
            <h3 class="mb-3">
                <a href="{{ route('admin.users.index') }}" class="btn">
                    <img src="/assets/images/icons/arrow-left.svg" alt="">
                </a>
                {{ $user->surname }} {{ $user->name }} {{ $user->patronymic }}
            </h3>


            @if($user->accounts->count())
                <hr>
                <h4 class="mb-2">Список компаний</h4>
                <table class="table bg-white">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Домен</th>
                        <th>Организация</th>
                        <th>Реквизиты</th>
                        <th>Дата создания</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($user->accounts as $account)
                        <tr>
                            <td>{{ $account->id }}</td>
                            <td>https://<strong>{{ $account->subdomain }}</strong>.linerapp.online</td>
                            <td>
                                {{ $account->name }}
                                <div class="text-sm text-secondary">
                                    {{ $account->address }}
                                </div>
                            </td>
                            <td>
                                 <div><string class="text-secondary">ИНН: </string> {{ $account->inn }}</div>
                                 <div><string class="text-secondary">КПП: </string> {{ $account->kpp }}</div>
                                 <div><string class="text-secondary">ОГРН: </string> {{ $account->kpp }}</div>
                                 @if($account->opf) <div><string class="text-secondary">ОПФ: </string> {{ $account->opf->short_name }}</div> @endif
                                 @if($account->nds_type) <div><string class="text-secondary">НДС: </string> {{ $account->nds_type->name }}</div> @endif
                                 @if($account->taxation_systems) <div><string class="text-secondary">Система налогообложения: </string> {{ $account->taxation_systems->name }}</div> @endif
                            </td>
                            <td>
                                {{ $account->created_at }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

            @if($user->amoCrmAccounts->count())
                <hr>
                <h4 class="mb-2">amoCRM аккаунты</h4>
                <div class="table-responsive">
                    <table class="table bg-white">
                        <thead>
                        <tr>
                            <th class="text-nowrap">Домен</th>
                            <th class="text-nowrap">ID клиента</th>
                            <th class="text-nowrap">E-mail</th>
                            <th class="text-nowrap" title="Дата первой установки виджета">Дата интеграции*</th>
                            <th class="text-nowrap">Тариф</th>
                            <th class="text-nowrap">Количество пользователей</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($user->amoCrmAccounts as $account)
                            <tr>
                                <td>https://<strong>{{ $account->subdomain }}</strong>.amocrm.ru</td>
                                <td>{{ $account->client_id }}</td>
                                <td>{{ $account->email }}</td>
                                <td>{{ $account->created_at }}</td>
                                <td>
                                    <div><span class="badge badge-primary">{{ $account->tariff_name }}</span></div>
                                    <div>{{ $account->paid_from }} - {{ $account->paid_till_date }}</div>
                                </td>
                                <td>{{ $account->users_count }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </main>
@endsection
