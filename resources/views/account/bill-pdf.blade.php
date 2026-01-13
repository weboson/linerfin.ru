<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    @if(isset($_GET['print']) || isset($_GET['download']))
        <style>
            .b-icon.bi{display:inline-block;overflow:visible;vertical-align:-.15em}.btn .b-icon.bi,.dropdown-item .b-icon.bi,.dropdown-toggle .b-icon.bi,.input-group-text .b-icon.bi,.nav-link .b-icon.bi{font-size:125%;vertical-align:text-bottom}:root{--blue:#0662C1;--indigo:#6610f2;--purple:#6f42c1;--pink:#e83e8c;--red:#dc3545;--orange:#fd7e14;--yellow:#ffc107;--green:#28a745;--teal:#20c997;--cyan:#17a2b8;--white:#fff;--gray:#6c757d;--gray-dark:#343a40;--primary:#0662C1;--secondary:#828282;--success:#28a745;--info:#17a2b8;--warning:#ffc107;--danger:#dc3545;--light:#f8f9fa;--dark:#343a40;--breakpoint-xs:0;--breakpoint-sm:576px;--breakpoint-md:768px;--breakpoint-lg:992px;--breakpoint-xl:1200px;--font-family-sans-serif:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";--font-family-monospace:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace}*,::after,::before{box-sizing:border-box}html{line-height:1.15}article,aside,figcaption,figure,footer,header,hgroup,main,nav,section{display:block}body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";font-size:1rem;font-weight:400;line-height:1.5;color:#212529;text-align:left;background-color:#fff}h1,h2,h3,h4,h5,h6{margin-top:0;margin-bottom:.5rem}p{margin-top:0;margin-bottom:1rem}a{color:#0662c1;text-decoration:none;background-color:transparent}a:hover{color:#043c77;text-decoration:underline}a:not([href]):not([class]){color:inherit;text-decoration:none}table{border-collapse:collapse}th{text-align:inherit;text-align:-webkit-match-parent}.h1,.h2,.h3,.h4,.h5,.h6,h1,h2,h3,h4,h5,h6{margin-bottom:.5rem;font-weight:500;line-height:1.2}.h3,h3{font-size:1.75rem}hr{margin-top:1rem;margin-bottom:1rem;border:0;border-top:1px solid rgba(0,0,0,.1)}.container,.container-fluid,.container-lg,.container-md,.container-sm,.container-xl{width:100%;padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto}.row{display:flex;flex-wrap:wrap;margin-right:-15px;margin-left:-15px}.col,.col-1,.col-10,.col-11,.col-12,.col-2,.col-3,.col-4,.col-5,.col-6,.col-7,.col-8,.col-9,.col-auto,.col-lg,.col-lg-1,.col-lg-10,.col-lg-11,.col-lg-12,.col-lg-2,.col-lg-3,.col-lg-4,.col-lg-5,.col-lg-6,.col-lg-7,.col-lg-8,.col-lg-9,.col-lg-auto,.col-md,.col-md-1,.col-md-10,.col-md-11,.col-md-12,.col-md-2,.col-md-3,.col-md-4,.col-md-5,.col-md-6,.col-md-7,.col-md-8,.col-md-9,.col-md-auto,.col-sm,.col-sm-1,.col-sm-10,.col-sm-11,.col-sm-12,.col-sm-2,.col-sm-3,.col-sm-4,.col-sm-5,.col-sm-6,.col-sm-7,.col-sm-8,.col-sm-9,.col-sm-auto,.col-xl,.col-xl-1,.col-xl-10,.col-xl-11,.col-xl-12,.col-xl-2,.col-xl-3,.col-xl-4,.col-xl-5,.col-xl-6,.col-xl-7,.col-xl-8,.col-xl-9,.col-xl-auto{position:relative;width:100%;padding-right:15px;padding-left:15px}.col{flex-basis:0;flex-grow:1;max-width:100%}.btn{display:inline-block;font-weight:400;color:#212529;text-align:center;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;background-color:transparent;border:1px solid transparent;padding:.375rem .75rem;font-size:1rem;line-height:1.5;transition:color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out}.btn:not(:disabled):not(.disabled){cursor:pointer}.btn-outline-secondary{color:#828282;border-color:#828282}.w-auto{width:auto!important}.mb-1,.my-1{margin-bottom:.25rem!important}.mt-2,.my-2{margin-top:.5rem!important}.mr-2,.mx-2{margin-right:.5rem!important}.mb-3,.my-3{margin-bottom:1rem!important}.ml-3,.mx-3{margin-left:1rem!important}.mt-5,.my-5{margin-top:3rem!important}.mb-5,.my-5{margin-bottom:3rem!important}.pr-3,.px-3{padding-right:1rem!important}.pb-3,.py-3{padding-bottom:1rem!important}.pr-4,.px-4{padding-right:1.5rem!important}.text-right{text-align:right!important}.btn{padding:12px 20px;font-weight:500}.btn-group-sm>.btn,.btn.btn-sm{padding:7px 20px;font-size:16px;font-weight:400}.btn.btn-outline-secondary{background-color:transparent;color:#828282;border-color:#b5b5b5}table.table-classic{width:100%;border-spacing:0;border:1px solid #000}table.table-classic.no-border,table.table-classic.no-border td,table.table-classic.no-border th{border:none}table.table-classic td,table.table-classic th{border:1px solid #000;padding:3px;vertical-align:top}table.table-classic td.tar,table.table-classic th.tar{text-align:right}table.table-classic td.tac,table.table-classic th.tac{text-align:center}table.table-classic td.with-title,table.table-classic th.with-title{position:relative;padding-bottom:30px}table.table-classic td.with-title>.title,table.table-classic th.with-title>.title{position:absolute;bottom:3px;left:3px;font-size:13px}.icon-left{margin-right:3px}body{font-size:16px;color:#2f2f2f;font-family:"SF UI Display",Roboto,sans-serif}.static-page #top-bar{width:100%;padding-top:20px;padding-bottom:20px}.static-page #top-bar .logo{display:inline-block;height:26px;width:136px;background:url(/assets/images/linerfin.svg) center/contain no-repeat}.container{max-width:1200px}
            .ml-auto{margin-left:auto}.btn:hover{text-decoration: none}

            /* signature list */
            .bill-view-page .bill-view .bill-view__signature{display:table;margin-top:30px;margin-bottom:40px}.bill-view-page .bill-view .bill-view__signature .bill-signature{display:table-row}.bill-view-page .bill-view .bill-view__signature .bill-signature+*>*{padding-top:15px}.bill-view-page .bill-view .bill-view__signature .bill-signature>*{display:table-cell;padding-right:15px}.bill-view-page .bill-view .bill-view__signature .bill-signature>*+*{padding-left:15px}.bill-view-page .bill-view .bill-view__signature .bill-signature .bill-signature__full-name,.bill-view-page .bill-view .bill-view__signature .bill-signature .bill-signature__position{text-transform:capitalize}.bill-view-page .bill-view .bill-view__signature .bill-signature .bill-signature__position{color:#828282}.bill-view-page .bill-view .bill-view__signature .bill-signature .bill-signature__images{border-bottom:1px solid #2f2f2f;width:300px;position:relative}.bill-view-page .bill-view .bill-view__signature .bill-signature .bill-signature__images>img{position:absolute;width:auto;height:auto;top:50%;left:40%;-webkit-transform:translate(-50%, -50%);transform:translate(-50%,-50%)}.bill-view-page .bill-view .bill-view__signature .bill-signature .bill-signature__images>img.signature{height:60px}.bill-view-page .bill-view .bill-view__signature .bill-signature .bill-signature__images>img.stamp{max-height:150px;left:60%}
        </style>
    @else
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @endif
    <title>Счет</title>
</head>
<body style="background: transparent !important;">

<div class="static-page">
    <div id="top-bar">
        <div class="container">
            <div class="row">
                <div class="col">
                    <a href="https://linerapp.online" class="logo"></a>
                </div>
                <!--                <div class="col support">
                                    <a href="tel:88002228595" class="phone">8-800-222-85-95</a>
                                    <div class="subtitle">
                                        Поддержка 24/7
                                    </div>
                                </div>-->
            </div>
        </div>
    </div>


    <div class="bill-view-page bill-namespace" style="padding-bottom: 50px">
        <div class="container {{ $bill->status === 'rejected' ? 'rejected-bill' : '' }}">


            @if(!isset($_GET['print']) && !isset($_GET['hide-actions']))
            <div class="toolbar text-right mb-5">
                <a href="/bill-download-{{ $bill->link }}" class="btn btn-sm btn-outline-secondary">
                    <svg viewBox="0 0 16 16" width="1em" height="1em" focusable="false" role="img" aria-label="download" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi-download icon-left b-icon bi"><g><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"></path><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"></path></g></svg>
                    <span>Скачать</span>
                </a>
                <a href="/bill-{{ $bill->link }}?print" class="btn btn-sm btn-outline-secondary ml-3">
                    <svg viewBox="0 0 16 16" width="1em" height="1em" focusable="false" role="img" aria-label="printer" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi-printer icon-left b-icon bi"><g><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"></path></g></svg>
                    <span>Печать</span>
                </a>
            </div>
            @endif

            @if($bill->status === 'rejected')
                <h4 style="color: darkred; margin: 20px 0;">Счет был отозван</h4>
            @endif


            <!-- content -->
            @if($bill->checking_account && $bill->checking_account->bank_name)
                <table class="table-classic">
                    <tbody>
                    <tr>
                        <td rowspan="2" colspan="2" class="with-title">
                            @if($bill->checking_account->bank_name)
                                {{ $bill->checking_account->bank_name }}
                            @endif
                            <div class="title">
                                Банк получателя
                            </div>
                        </td>
                        <td>
                            БИК
                        </td>
                        <td rowspan="2">
                            <div>{{ $bill->checking_account->bank_bik }}</div>
                            <div>{{ $bill->checking_account->bank_correspondent }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>Сч. №</td>
                    </tr>
                    <tr>
                        <td>
                            <span class="mr-2">ИНН</span> {{ $bill->account->inn }}
                        </td>
                        <td>
                            <span class="mr-2">КПП</span> {{ $bill->account->kpp }}
                        </td>
                        <td rowspan="2">
                            Сч. №
                        </td>
                        <td rowspan="2">{{ $bill->checking_account->num }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="with-title">
                            {{ $bill->account->name }}
                            <div class="title">
                                Получатель
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            @endif


            <h3 class="mt-5">
                Счет на оплату
                @if($bill->num)
                    №{{ $bill->num }}
                    @if($bill->issued_at)
                        от {{ $bill->issued_at->format('d.m.Y') }}
                    @endif
                @endif
            </h3>


            <hr style="border-width: 3px; border-color: #000;">


            <table class="table-classic no-border w-auto mb-3">
                <tbody>
                <tr>
                    <td class="pr-3 pb-3">
                        Поставщик:
                    </td>
                    <th class="pb-3">
                        <?php
                            $companyData = [];
                            if(!empty($bill) && !empty($bill->account)){
                                $company = $bill->account;

                                if(!empty($company->name))
                                    $companyData[] = $company->name;

                                if(!empty($company->inn))
                                    $companyData[] = "ИНН $company->inn";

                                if(!empty($company->kpp))
                                    $companyData[] = "КПП $company->kpp";

                                if(!empty($company->legal_address))
                                    $companyData[] = $company->legal_address;

                                echo implode(', ', $companyData);
                            }
                        ?>
                    </th>
                </tr>
                <tr>
                    <td class="pr-3 pb-3">
                        Покупатель:
                    </td>
                    <th class="pb-3">
                        <?php
                            $partyData = [];
                            if(!empty($bill->counterparty)){
                                $counterparty = $bill->counterparty;
                                if($counterparty->name) $partyData[] = $counterparty->name;
                                if($counterparty->inn) $partyData[] = 'ИНН '.$counterparty->inn;
                                if($counterparty->kpp) $partyData[] = 'КПП '.$counterparty->kpp;
                                if($counterparty->address) $partyData[] = $counterparty->address;
                            }

                            echo implode(', ', $partyData);
                        ?>
                    </th>
                </tr>
                </tbody>
            </table>

            <table class="table-classic" style="border-width: 2px">
                <thead>
                <tr>
                    <th>№</th>
                    <th class="tac">Товары (работы, услуги)</th>
                    <th class="tac">Кол-во</th>
                    <th class="tac">Ед.</th>
                    <th class="tac">НДС</th>
                    <th class="tac">Цена</th>
                    <th class="tar">Сумма</th>
                </tr>
                </thead>
                <tbody>

                @foreach($bill->positions as $i => $position)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $position->name }}</td>
                        <td class="tac">{{ $position->count }}</td>
                        <td>{{ $position->units }}</td>
                        <td class="tar">{{ $position->nds_type && $position->nds_type->percentage ? $position->nds_type->name : ''}}</td>
                        <td class="tar">{{ number_format($position->unit_price, 2, ',', ' ') }}</td>
                        <td class="tar">{{ number_format($position->count * $position->unit_price, 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>


            <table class="table-classic no-border w-auto ml-auto mt-2">
                <tr>
                    <th class="pr-4 tar">Итого:</th>
                    <th class="tar">{{ number_format($bill->sum, 2, ',', ' ') }}</th>
                </tr>
                @if($bill->nds_type)
                    <tr>
                        <th class="pr-4 tar">НДС {{ $bill->nds_type->name }}:</th>
                        <th class="tar">{{ number_format($bill->sum - $bill->sum_without_vat, 2, ',', ' ') }}</th>
                    </tr>
                @endif
                <tr>
                    <th class="pr-4 tar">Всего к оплате:</th>
                    <th class="tar">{{ number_format($bill->sum, 2, ',', ' ') }}</th>
                </tr>
            </table>


            <p class="mb-1">
                Всего наименований {{ count($bill->positions) }}, на сумму
                {{ number_format($bill->sum, 2, ',', ' ') }} руб.
            </p>
            <p style="font-weight: bold;">
                <?php
                $value = explode('.', number_format($bill->sum, 2, '.', ''));

                $f = new NumberFormatter('ru', NumberFormatter::SPELLOUT);
                $str = $f->format($value[0]);

                // first letter to uppercase
                $str = mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1, mb_strlen($str));

                // Склонение слова "рубль".
                $num = $value[0] % 100;
                if ($num > 19) {
                    $num = $num % 10;
                }
                switch ($num) {
                    case 1: $rub = 'рубль'; break;
                    case 2:
                    case 3:
                    case 4: $rub = 'рубля'; break;
                    default: $rub = 'рублей';
                }

                echo $str . ' ' . $rub . ' ' . $value[1] . ' копеек.';
                ?>
            </p>

            <hr style="border-width: 3px; border-color: #000;">


            <div class="bill-view">

                <!-- bill signature -->
                <section class="bill-view__signature">
                    @foreach($bill->signature_list as $signature)
                        <div class="bill-signature">
                            <div class="bill-signature__position">{{ $signature->position }}</div>

                            @if($signature->signature_attachment || $bill->stamp_attachment)
                                <div class="bill-signature__images">
                                    @if($signature->signature_attachment)
                                        <img class="signature" src="http://{{ !empty($subdomain) ? "$subdomain." : ''  }}{{ config('app.domain') }}{{ "/ui/attachments/" . $signature->signature_attachment->uuid }}">
                                    @endif
                                    @if($bill->stamp_attachment)
                                        <img class="stamp" src="http://{{ !empty($subdomain) ? "$subdomain." : '' }}{{ config('app.domain') }}{{ "/ui/attachments/" . $bill->stamp_attachment->uuid }}">
                                    @endif
                                </div>
                            @endif
                            <div class="bill-signature__full-name">{{ $signature->full_name }}</div>
                        </div>
                    @endforeach
                </section>

            </div>


            @if($bill->comment)
                <hr>
                <div class="text-secondary">
                    {{ $bill->comment }}
                </div>
            @endif
            <!-- end content -->

        </div>
    </div>

    <!-- for printing -->
    @if(isset($_GET['print']))
    <script>
        window.print();
    </script>
    @endif


</div>

</body>
</html>
