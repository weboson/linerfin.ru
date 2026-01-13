<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <style>
        /* Общие стили для PDF и просмотра */
        .b-icon.bi { display: inline-block; overflow: visible; vertical-align: -.15em }
        :root {
            --primary: #0662C1;
            --secondary: #828282;
            --font-family-sans-serif: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        *, ::after, ::before { box-sizing: border-box }
        html { line-height: 1.15 }
        body {
            margin: 0;
            font-family: "Roboto", -apple-system, sans-serif;
            font-size: 14px; /* Немного уменьшил для PDF */
            line-height: 1.5;
            color: #2f2f2f;
            background-color: #fff;
        }
        h3 { font-size: 1.75rem; margin-bottom: .5rem; font-weight: 500; }
        table { border-collapse: collapse; width: 100%; }
        th { text-align: left; }
        hr { margin-top: 1rem; margin-bottom: 1rem; border: 0; border-top: 1px solid rgba(0, 0, 0, .1); }

        .container { width: 100%; padding: 0 15px; margin: 0 auto; max-width: 1200px; }
        .text-right { text-align: right !important; }
        .text-secondary { color: #828282; }
        .mb-5 { margin-bottom: 3rem !important; }
        .mt-5 { margin-top: 3rem !important; }
        .mt-2 { margin-top: .5rem !important; }
        .ml-auto { margin-left: auto; }
        .tar { text-align: right; }
        .tac { text-align: center; }

        /* Таблица счета */
        table.table-classic { border: 1px solid #000; }
        table.table-classic td, table.table-classic th { border: 1px solid #000; padding: 5px; vertical-align: top; }
        table.table-classic.no-border, table.table-classic.no-border td, table.table-classic.no-border th { border: none; }
        
        table.table-classic td.with-title { position: relative; padding-bottom: 25px; }
        table.table-classic td.with-title .title { position: absolute; bottom: 3px; left: 5px; font-size: 11px; color: #555; }

        /* Шапка страницы */
        #top-bar { width: 100%; padding: 20px 0; }
        #top-bar .logo { 
            display: inline-block; 
            height: 26px; 
            width: 136px; 
            background: url(https://linerfin.ru/assets/images/linerfin.svg) center/contain no-repeat; /* Внешняя ссылка для лого */
        }

        /* Блок подписей и печатей */
        .bill-view__signature { display: table; width: 100%; margin-top: 30px; }
        .bill-signature { display: table-row; }
        .bill-signature > div { display: table-cell; vertical-align: bottom; padding: 10px 15px 10px 0; }
        
        .bill-signature__position { text-transform: capitalize; color: #828282; width: 25%; }
        .bill-signature__full-name { text-transform: capitalize; width: 25%; border-bottom: none; }
        
        .bill-signature__images { 
            border-bottom: 1px solid #2f2f2f; 
            width: 300px; 
            position: relative; 
            height: 80px; /* Фиксированная высота для стабильности */
        }
        
        .bill-signature__images img.signature {
            position: absolute;
            height: 70px;
            bottom: 5px;
            left: 20%;
            z-index: 2;
        }
        
        .bill-signature__images img.stamp {
            position: absolute;
            height: 140px;
            bottom: -30px;
            left: 40%;
            z-index: 1;
        }

        /* Toolbar для веба */
        .btn { padding: 7px 20px; border: 1px solid #b5b5b5; color: #828282; text-decoration: none; border-radius: 4px; font-size: 14px; }
        
        @media print {
            .toolbar, #top-bar { display: none !important; }
            body { padding: 0; }
        }
    </style>
    <title>Счет №{{ $bill->num }}</title>
</head>
<body style="background: transparent !important;">
    <div class="static-page">
        {{-- Скрываем шапку в PDF --}}
        @if(!isset($isPdf))
        <div id="top-bar">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <a href="https://linerfin.ru" class="logo"></a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="bill-view-page" style="padding-bottom: 50px">
            <div class="container">

                {{-- Кнопки действий (только в браузере) --}}
                @if(!isset($isPdf) && !isset($_GET['print']))
                <div class="toolbar text-right mb-5">
                    <a href="/bill-download-{{ $bill->link }}" class="btn">
                        <span>Скачать PDF</span>
                    </a>
                    <a href="/bill-{{ $bill->link }}?print" class="btn" style="margin-left: 10px;">
                        <span>Печать</span>
                    </a>
                </div>
                @endif

                @if($bill->status === 'rejected')
                    <h4 style="color: darkred; margin: 20px 0;">Счет был отозван</h4>
                @endif

                {{-- Банковские реквизиты --}}
                @if($bill->checking_account)
                <table class="table-classic">
                    <tbody>
                        <tr>
                            <td rowspan="2" colspan="2" class="with-title">
                                {{ $bill->checking_account->bank_name }}
                                <div class="title">Банк получателя</div>
                            </td>
                            <td style="width: 60px;">БИК</td>
                            <td rowspan="2" style="width: 200px;">
                                <div>{{ $bill->checking_account->bank_bik }}</div>
                                <div>{{ $bill->checking_account->bank_correspondent }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Сч. №</td>
                        </tr>
                        <tr>
                            <td style="width: 150px;">ИНН {{ $bill->account->inn }}</td>
                            <td style="width: 150px;">КПП {{ $bill->account->kpp }}</td>
                            <td rowspan="2">Сч. №</td>
                            <td rowspan="2">{{ $bill->checking_account->num }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="with-title">
                                {{ $bill->account->name }}
                                <div class="title">Получатель</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                @endif

                <h3 class="mt-5">
                    Счет на оплату №{{ $bill->num }} от {{ optional($bill->issued_at)->format('d.m.Y') }}
                </h3>

                <hr style="border-top: 2px solid #000;">

                <table class="table-classic no-border mb-3">
                    <tr>
                        <td style="width: 100px; padding-bottom: 10px;">Поставщик:</td>
                        <th style="padding-bottom: 10px;">
                            {{ $bill->account->name }}, ИНН {{ $bill->account->inn }}, {{ $bill->account->legal_address }}
                        </th>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Покупатель:</td>
                        <th style="padding-bottom: 10px;">
                            {{ $bill->counterparty->name }}, ИНН {{ $bill->counterparty->inn }}, {{ $bill->counterparty->address }}
                        </th>
                    </tr>
                </table>

                {{-- Таблица позиций --}}
                <table class="table-classic">
                    <thead>
                        <tr>
                            <th style="width: 30px;">№</th>
                            <th class="tac">Товары (работы, услуги)</th>
                            <th class="tac">Кол-во</th>
                            <th class="tac">Ед.</th>
                            <th class="tac">Цена</th>
                            <th class="tar">Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bill->positions as $i => $position)
                        <tr>
                            <td class="tac">{{ $i+1 }}</td>
                            <td>{{ $position->name }}</td>
                            <td class="tac">{{ $position->count }}</td>
                            <td class="tac">{{ $position->units }}</td>
                            <td class="tar">{{ number_format($position->unit_price, 2, ',', ' ') }}</td>
                            <td class="tar">{{ number_format($position->count * $position->unit_price, 2, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <table class="table-classic no-border ml-auto mt-2" style="width: 300px;">
                    <tr>
                        <th class="tar">Итого:</th>
                        <th class="tar">{{ number_format($bill->sum, 2, ',', ' ') }}</th>
                    </tr>
                    @if($bill->nds_type)
                    <tr>
                        <th class="tar">НДС {{ $bill->nds_type->name }}:</th>
                        <th class="tar">{{ number_format($bill->sum - $bill->sum_without_vat, 2, ',', ' ') }}</th>
                    </tr>
                    @endif
                    <tr>
                        <th class="tar">Всего к оплате:</th>
                        <th class="tar">{{ number_format($bill->sum, 2, ',', ' ') }}</th>
                    </tr>
                </table>

                <p class="mt-2">
                    Всего наименований {{ count($bill->positions) }}, на сумму {{ number_format($bill->sum, 2, ',', ' ') }} руб.
                </p>
                <p style="font-weight: bold;">
                    <?php
                        $f = new NumberFormatter('ru', NumberFormatter::SPELLOUT);
                        $whole = floor($bill->sum);
                        $fraction = round(($bill->sum - $whole) * 100);
                        $str = $f->format($whole);
                        $str = mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
                        echo "{$str} руб. {$fraction} коп.";
                    ?>
                </p>

                <hr style="border-top: 2px solid #000;">

                {{-- Блок подписей --}}
                <div class="bill-view__signature">
                    @foreach($bill->signature_list_with_attachments as $index => $sig)
                    <div class="bill-signature">
                        <div class="bill-signature__position">{{ $sig->position }}</div>
                        <div class="bill-signature__images">
                            {{-- Подпись --}}
                            @if(isset($sig->signature_base64))
                                <img class="signature" src="{{ $sig->signature_base64 }}">
                            @endif

                            {{-- Печать (ставим только один раз у первой подписи или если задано условие) --}}
                            @if($index === 0 && isset($bill->stamp_base64))
                                <img class="stamp" src="{{ $bill->stamp_base64 }}">
                            @endif
                        </div>
                        <div class="bill-signature__full-name">{{ $sig->full_name }}</div>
                    </div>
                    @endforeach
                </div>

                @if($bill->comment)
                <div class="text-secondary mt-5">
                    <strong>Комментарий:</strong><br>
                    {{ $bill->comment }}
                </div>
                @endif
            </div>
        </div>

        @if(isset($_GET['print']))
        <script>
            window.onload = function() { window.print(); }
        </script>
        @endif
    </div>
</body>
</html>