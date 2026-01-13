@extends('provider.layout')

@section('content')

    <div id="form">
        <form method="GET" action="{{ route('checking-account.save') }}">
            <input type="hidden" name="subdomain" value="{{$subdomain ?? null}}">
            <header class="form-title">
                Подключить счета
            </header>

            <!-- Validation Errors -->
            <div class="">
                <table class="table">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Номер счёта</th>
                        <th>Баланс</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($checkingAccounts as $checkingAccount)
                    <tr>
                        <th scope="row">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="id[{{$checkingAccount->provider_account_id}}]" value="true" class="custom-control-input" id="custom_{{$checkingAccount->provider_account_id}}" checked>
                                <label class="custom-control-label" for="custom_{{$checkingAccount->provider_account_id}}"></label>
                            </div>
                        </th>
                        <td>{{$checkingAccount->provider_account_id}}</td>
                        <td>{{$checkingAccount->balance}}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>


            <button type="submit" class="btn btn-primary w-100 ">Подключить</button>

        </form>
    </div>

@endsection
