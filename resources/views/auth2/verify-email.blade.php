@extends('auth2.layout')

@section('content')

    <div id="form">

        <div class="form-title">
            Подтверждение E-Mail
            <div class="subtitle" style="font-size: 16px; color: #242424">
                Для продолжения необходимо подтвердить ваш <span style="white-space: nowrap">E-Mail</span>
            </div>
        </div>

        <p class="text-success">
            На ваш E-Mail отправлено письмо с ссылкой для подтверждения.
        </p>


        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 text-success">
                Ссылка отправлена. Если письмо отсутствует - проверьте папку Спам.
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <div class="form-group">
                    <div class="input-group">
                        <input id="emailInput" ref="emailInput" value="{{ request()->user()->email }}" type="email" disabled="disabled"
                               class="form-control">
                        <div class="input-group-append" id="editBtnWrapper">
                            <button @click.prevent id="editBtn"
                                    class="btn btn-outline-secondary">Изменить E-Mail</button>
                        </div>
                    </div>

                </div>

                <div class="d-flex justify-content-between mt-4">
                    <div>
                        <a href="https://demo.{{ config('app.domain') }}" class="btn btn-link p-0 text-secondary">
                            <b-icon-arrow-left class="mr-1"></b-icon-arrow-left>
                            <span>Вернуться в демо-режим</span>
                        </a>
                    </div>
                    <div>
                        <button class="btn btn-link ml-auto d-block p-0" type="submit">
                            Отправить заново
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script type="application/javascript">
       /* let editBtn = document.getElementById('editBtn');
        let editBtnWrapper = document.getElementById('editBtnWrapper');
        let emailInput = document.getElementById('emailInput');*/

       document.addEventListener('DOMContentLoaded', function(){
           if(editBtn && emailInput){
               editBtn.addEventListener('click', function(e){
                   e.preventDefault();
                   emailInput.disabled = false;

                   if(editBtnWrapper)
                       editBtnWrapper.style = 'display: none';
               });
           }
       });
    </script>
@endsection
