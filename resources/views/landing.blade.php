<!DOCTYPE html>

<html class="no-touch">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--metatextblock-->
    <title>LinerFin</title>

    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

    <meta name="description" content="Сервис управления финансами">
    <meta property="og:title" content="LinerFin">
    <meta property="og:description" content="Сервис управления финансами">
    <meta property="og:type" content="website">

    <!--/metatextblock-->

    <style>
        .t-popup__container .t702__wrapper iframe.amoforms_iframe{
            height: 100% !important;
        }
    </style>

    <!-- Assets -->
    <link rel="stylesheet" href="{{ asset('assets/landing/grid-3.0.min.css') }}" type="text/css" media="all">
    <link rel="stylesheet" href="{{ asset('assets/landing/blocks-2.12.css') }}" type="text/css" media="all">
    <link rel="stylesheet" href="{{ asset('assets/landing/animation-1.0.min.css') }}" type="text/css" media="all">
    <link rel="stylesheet" href="{{ asset('assets/landing/menusub-1.0.min.css') }}" type="text/css" media="all">
    <link rel="stylesheet" href="{{ asset('assets/landing/popup-1.1.min.css') }}" type="text/css" media="all">
    <script src="{{ asset('assets/landing/jquery-1.10.2.min.js') }}"></script>
    <script src="{{ asset('assets/landing/scripts-2.8.min.js') }}"></script>
    <script src="{{ asset('assets/landing/blocks-2.7.js') }}"></script>
    <script src="{{ asset('assets/landing/lazyload-1.3.min.js') }}" charset="utf-8"></script>
    <script src="{{ asset('assets/landing/animation-1.0.min.js') }}" charset="utf-8"></script>
    <script src="{{ asset('assets/landing/menusub-1.0.min.js') }}" charset="utf-8"></script>
    <script src="{{ asset('assets/landing/forms-1.0.min.js') }}" charset="utf-8"></script>
    <script type="text/javascript">
        window.dataLayer = window.dataLayer || [];
    </script>
    <script type="text/javascript">
        if ((/bot|google|yandex|baidu|bing|msn|duckduckbot|teoma|slurp|crawler|spider|robot|crawling|facebook/i.test(navigator.userAgent)) === false && typeof(sessionStorage) != 'undefined' && sessionStorage.getItem('visited') !== 'y') {
            var style = document.createElement('style');
            style.type = 'text/css';
            style.innerHTML = '@media screen and (min-width: 980px) {.t-records {opacity: 0;}.t-records_animated {-webkit-transition: opacity ease-in-out .2s;-moz-transition: opacity ease-in-out .2s;-o-transition: opacity ease-in-out .2s;transition: opacity ease-in-out .2s;}.t-records.t-records_visible {opacity: 1;}}';
            document.getElementsByTagName('head')[0].appendChild(style);
            $(document).ready(function() {
                $('.t-records').addClass('t-records_animated');
                setTimeout(function() {
                    $('.t-records').addClass('t-records_visible');
                    sessionStorage.setItem('visited', 'y');
                }, 400);
            });
        }
    </script>
    <style type="text/css">
        @media screen and (min-width: 980px) {
            .t-records {
                opacity: 0;
            }
            .t-records_animated {
                -webkit-transition: opacity ease-in-out .2s;
                -moz-transition: opacity ease-in-out .2s;
                -o-transition: opacity ease-in-out .2s;
                transition: opacity ease-in-out .2s;
            }
            .t-records.t-records_visible {
                opacity: 1;
            }
        }
    </style>
    <style type="text/css">
        /*
     * contextMenu.js v 1.4.0
     * Author: Sudhanshu Yadav
     * s-yadav.github.com
     * Copyright (c) 2013 Sudhanshu Yadav.
     * Dual licensed under the MIT and GPL licenses
     **/

        .iw-contextMenu {
            box-shadow: 0px 2px 3px rgba(0, 0, 0, 0.10) !important;
            border: 1px solid #c8c7cc !important;
            border-radius: 11px !important;
            display: none;
            z-index: 1000000132;
            max-width: 300px !important;
            width: auto !important;
        }

        .dark-mode .iw-contextMenu,
        .TnITTtw-dark-mode.iw-contextMenu,
        .TnITTtw-dark-mode .iw-contextMenu {
            border-color: #747473 !important;
        }

        .iw-cm-menu {
            background: #fff !important;
            color: #000 !important;
            margin: 0px !important;
            padding: 0px !important;
            overflow: visible !important;
        }

        .dark-mode .iw-cm-menu,
        .TnITTtw-dark-mode.iw-cm-menu,
        .TnITTtw-dark-mode .iw-cm-menu {
            background: #525251 !important;
            color: #FFF !important;
        }

        .iw-curMenu {}

        .iw-cm-menu li {
            font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", Helvetica, Arial, Ubuntu, sans-serif !important;
            list-style: none !important;
            padding: 10px !important;
            padding-right: 20px !important;
            border-bottom: 1px solid #c8c7cc !important;
            font-weight: 400 !important;
            cursor: pointer !important;
            position: relative !important;
            font-size: 14px !important;
            margin: 0 !important;
            line-height: inherit !important;
            border-radius: 0 !important;
            display: block !important;
        }

        .dark-mode .iw-cm-menu li,
        .TnITTtw-dark-mode .iw-cm-menu li {
            border-bottom-color: #747473 !important;
        }

        .iw-cm-menu li:first-child {
            border-top-left-radius: 11px !important;
            border-top-right-radius: 11px !important;
        }

        .iw-cm-menu li:last-child {
            border-bottom-left-radius: 11px !important;
            border-bottom-right-radius: 11px !important;
            border-bottom: none !important;
        }

        .iw-mOverlay {
            position: absolute !important;
            width: 100% !important;
            height: 100% !important;
            top: 0px !important;
            left: 0px !important;
            background: #FFF !important;
            opacity: .5 !important;
        }

        .iw-contextMenu li.iw-mDisable {
            opacity: 0.3 !important;
            cursor: default !important;
        }

        .iw-mSelected {
            background-color: #F6F6F6 !important;
        }

        .dark-mode .iw-mSelected,
        .TnITTtw-dark-mode .iw-mSelected {
            background-color: #676766 !important;
        }

        .iw-cm-arrow-right {
            width: 0 !important;
            height: 0 !important;
            border-top: 5px solid transparent !important;
            border-bottom: 5px solid transparent !important;
            border-left: 5px solid #000 !important;
            position: absolute !important;
            right: 5px !important;
            top: 50% !important;
            margin-top: -5px !important;
        }

        .dark-mode .iw-cm-arrow-right,
        .TnITTtw-dark-mode .iw-cm-arrow-right {
            border-left-color: #FFF !important;
        }

        .iw-mSelected > .iw-cm-arrow-right {}
        /*context menu css end */
    </style>
    <style type="text/css">
        @-webkit-keyframes load4 {
            0%,
            100% {
                box-shadow: 0 -3em 0 0.2em, 2em -2em 0 0em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 0;
            }
            12.5% {
                box-shadow: 0 -3em 0 0, 2em -2em 0 0.2em, 3em 0 0 0, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
            }
            25% {
                box-shadow: 0 -3em 0 -0.5em, 2em -2em 0 0, 3em 0 0 0.2em, 2em 2em 0 0, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
            }
            37.5% {
                box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 0, 2em 2em 0 0.2em, 0 3em 0 0em, -2em 2em 0 -1em, -3em 0em 0 -1em, -2em -2em 0 -1em;
            }
            50% {
                box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 0em, 0 3em 0 0.2em, -2em 2em 0 0, -3em 0em 0 -1em, -2em -2em 0 -1em;
            }
            62.5% {
                box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 0, -2em 2em 0 0.2em, -3em 0 0 0, -2em -2em 0 -1em;
            }
            75% {
                box-shadow: 0em -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0.2em, -2em -2em 0 0;
            }
            87.5% {
                box-shadow: 0em -3em 0 0, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0, -2em -2em 0 0.2em;
            }
        }

        @keyframes load4 {
            0%,
            100% {
                box-shadow: 0 -3em 0 0.2em, 2em -2em 0 0em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 0;
            }
            12.5% {
                box-shadow: 0 -3em 0 0, 2em -2em 0 0.2em, 3em 0 0 0, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
            }
            25% {
                box-shadow: 0 -3em 0 -0.5em, 2em -2em 0 0, 3em 0 0 0.2em, 2em 2em 0 0, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
            }
            37.5% {
                box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 0, 2em 2em 0 0.2em, 0 3em 0 0em, -2em 2em 0 -1em, -3em 0em 0 -1em, -2em -2em 0 -1em;
            }
            50% {
                box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 0em, 0 3em 0 0.2em, -2em 2em 0 0, -3em 0em 0 -1em, -2em -2em 0 -1em;
            }
            62.5% {
                box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 0, -2em 2em 0 0.2em, -3em 0 0 0, -2em -2em 0 -1em;
            }
            75% {
                box-shadow: 0em -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0.2em, -2em -2em 0 0;
            }
            87.5% {
                box-shadow: 0em -3em 0 0, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0, -2em -2em 0 0.2em;
            }
        }
    </style>
    <style type="text/css">
        /* This is not a zero-length file! */
    </style>
</head>

<body class="t-body" style="margin: 0px;">
<!--allrecords-->
<div id="allrecords" class="t-records t-records_animated t-records_visible" data-hook="blocks-collection-content-node" data-tilda-project-id="2239231" data-tilda-page-id="10313189" data-tilda-formskey="36f054ff2b31da3fd5aad0fe622db2fe">
    <div id="rec176159695" class="r t-rec" style="opacity: 1;" data-animationappear="off" data-record-type="360">
        <!-- T360 -->
        <style>
            @media screen and (min-width: 980px) {
                .t-records {
                    opacity: 0;
                }
                .t-records_animated {
                    -webkit-transition: opacity ease-in-out .5s;
                    -moz-transition: opacity ease-in-out .5s;
                    -o-transition: opacity ease-in-out .5s;
                    transition: opacity ease-in-out .5s;
                }
                .t-records.t-records_visible {
                    opacity: 1;
                }
            }
        </style>
        <script>
            $(document).ready(function() {
                $(window).bind('pageshow', function(event) {
                    if (event.originalEvent.persisted) {
                        window.location.reload()
                    }
                });
                $("#rec176159695").attr('data-animationappear', 'off');
                $("#rec176159695").css('opacity', '1');
                $('.t-records').addClass('t-records_animated');
                setTimeout(function() {
                    $('.t-records').addClass('t-records_visible');
                }, 200);
            });
        </script>
        <script>
            $(document).ready(function() {
                $("button:not(.t-submit,.t835__btn_next,.t835__btn_prev,.t835__btn_result,.t862__btn_next,.t862__btn_prev,.t862__btn_result,.t854__news-btn,.t862__btn_next),a:not([href*=#],.carousel-control,.t-carousel__control,.t807__btn_reply,[href^=#price],[href^=javascript],[href^=mailto],[href^=tel],[href^=link_sub],.t-menu__link-item[data-menu-submenu-hook])").click(function(e) {
                    var goTo = this.getAttribute("href");
                    if (goTo !== null) {
                        var target = $(this).attr("target");
                        if (target !== "_blank") {
                            e.preventDefault();
                            $(".t-records").removeClass("t-records_visible");
                            setTimeout(function() {
                                window.location = goTo;
                            }, 500);
                        }
                    }
                });
            });
        </script>
    </div>
    <div id="rec176159696" class="r t-rec r_showed r_anim" style=" " data-record-type="394">
        <style>
            #allrecords .t-text a,
            #allrecords .t-descr a,
            #allrecords .t-heading a,
            #allrecords .t-title a,
            #allrecords .t-impact-text a,
            #allrecords .t-text-impact a,
            #allrecords .t-name:not(.t-feed__parts-switch-btn) a:not(.t794__typo),
            #allrecords .t-uptitle a {
                color: #0056d3;
                font-weight: inherit;
                border-bottom: px solid #ff3503;
                -webkit-box-shadow: inset 0px -px 0px 0px #ff3503;
                -moz-box-shadow: inset 0px -px 0px 0px #ff3503;
                box-shadow: inset 0px -px 0px 0px #ff3503;
            }
        </style>
    </div>
    <div id="rec176751863" class="r t-rec" style=" " data-animationappear="off" data-record-type="481">
        <!-- T481 -->
        <div id="nav176751863marker"></div>
        <div id="nav176751863" class="t481 t-col_12 t481__positionstatic " style="background-color: rgba(255,255,255,1); height:150px; " data-bgcolor-hex="#ffffff" data-bgcolor-rgba="rgba(255,255,255,1)" data-navmarker="nav176751863marker" data-appearoffset="" data-bgopacity-two="" data-menushadow="" data-bgopacity="1" data-menu-items-align="center" data-menu="yes">
            <div class="t481__maincontainer " style="height:150px;">
                <div class="t481__padding40px"></div>
                <div class="t481__leftside" style="min-width: 339px;">
                    <div class="t481__leftcontainer" style="display: block;">
                        <a href="#popup:myform" style="color:#ffffff;"><img src="{{ asset('assets/landing/linerfin_.png') }}" class="t481__imglogo t481__imglogomobile" imgfield="img" style="max-width: 250px;width: 250px; height: auto; display: block;" alt=""></a>
                    </div>
                </div>
                <div class="t481__centerside ">
                    <div class="t481__centercontainer">
                        <ul class="t481__list"> </ul>
                    </div>
                </div>
                <div class="t481__rightside" style="min-width: 339px;">
                    <div class="t481__rightcontainer">
                        <div class="t481__right_descr" style="">
                            <div style="font-size:20px;" data-customstyle="yes">
                                <a href="tel:8-800-222-85-95">
                                    8-800-222-85-95
                                </a>
                            </div>
                        </div>
                        {{--<div class="t481__right_social_links">
                            <div class="t481__right_social_links_wrap">
                                <div class="t481__right_social_links_item">
                                    <a href="https://www.facebook.com/LinerFin-101400571524132/" target="_blank">
                                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="28px" height="28px" viewBox="0 0 48 48" enable-background="new 0 0 48 48" xml:space="preserve">
										<path d="M47.761,24c0,13.121-10.638,23.76-23.758,23.76C10.877,47.76,0.239,37.121,0.239,24c0-13.124,10.638-23.76,23.764-23.76 C37.123,0.24,47.761,10.876,47.761,24 M20.033,38.85H26.2V24.01h4.163l0.539-5.242H26.2v-3.083c0-1.156,0.769-1.427,1.308-1.427 h3.318V9.168L26.258,9.15c-5.072,0-6.225,3.796-6.225,6.224v3.394H17.1v5.242h2.933V38.85z"></path>
									</svg>
                                    </a>
                                </div>
                                <div class="t481__right_social_links_item">
                                    <a href="https://vk.com/linerfin" target="_blank">
                                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="28px" height="28px" viewBox="0 0 48 48" enable-background="new 0 0 48 48" xml:space="preserve">
										<path d="M47.761,24c0,13.121-10.639,23.76-23.76,23.76C10.878,47.76,0.239,37.121,0.239,24c0-13.123,10.639-23.76,23.762-23.76 C37.122,0.24,47.761,10.877,47.761,24 M35.259,28.999c-2.621-2.433-2.271-2.041,0.89-6.25c1.923-2.562,2.696-4.126,2.45-4.796 c-0.227-0.639-1.64-0.469-1.64-0.469l-4.71,0.029c0,0-0.351-0.048-0.609,0.106c-0.249,0.151-0.414,0.505-0.414,0.505 s-0.742,1.982-1.734,3.669c-2.094,3.559-2.935,3.747-3.277,3.524c-0.796-0.516-0.597-2.068-0.597-3.171 c0-3.449,0.522-4.887-1.02-5.259c-0.511-0.124-0.887-0.205-2.195-0.219c-1.678-0.016-3.101,0.007-3.904,0.398 c-0.536,0.263-0.949,0.847-0.697,0.88c0.31,0.041,1.016,0.192,1.388,0.699c0.484,0.656,0.464,2.131,0.464,2.131 s0.282,4.056-0.646,4.561c-0.632,0.347-1.503-0.36-3.37-3.588c-0.958-1.652-1.68-3.481-1.68-3.481s-0.14-0.344-0.392-0.527 c-0.299-0.222-0.722-0.298-0.722-0.298l-4.469,0.018c0,0-0.674-0.003-0.919,0.289c-0.219,0.259-0.018,0.752-0.018,0.752 s3.499,8.104,7.463,12.23c3.638,3.784,7.764,3.36,7.764,3.36h1.867c0,0,0.566,0.113,0.854-0.189 c0.265-0.288,0.256-0.646,0.256-0.646s-0.034-2.512,1.129-2.883c1.15-0.36,2.624,2.429,4.188,3.497 c1.182,0.812,2.079,0.633,2.079,0.633l4.181-0.056c0,0,2.186-0.136,1.149-1.858C38.281,32.451,37.763,31.321,35.259,28.999"></path>
									</svg>
                                    </a>
                                </div>
                                <div class="t481__right_social_links_item">
                                    <a href="https://www.youtube.com/channel/UCEgYmwBg59RTmcJA93LysHg?view_as=subscriber" target="_blank">
                                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="28px" height="28px" viewBox="-455 257 48 48" enable-background="new -455 257 48 48" xml:space="preserve">
										<path d="M-431,257.013c13.248,0,23.987,10.74,23.987,23.987s-10.74,23.987-23.987,23.987s-23.987-10.74-23.987-23.987 S-444.248,257.013-431,257.013z M-419.185,275.093c-0.25-1.337-1.363-2.335-2.642-2.458c-3.054-0.196-6.119-0.355-9.178-0.357 c-3.059-0.002-6.113,0.154-9.167,0.347c-1.284,0.124-2.397,1.117-2.646,2.459c-0.284,1.933-0.426,3.885-0.426,5.836 s0.142,3.903,0.426,5.836c0.249,1.342,1.362,2.454,2.646,2.577c3.055,0.193,6.107,0.39,9.167,0.39c3.058,0,6.126-0.172,9.178-0.37 c1.279-0.124,2.392-1.269,2.642-2.606c0.286-1.93,0.429-3.879,0.429-5.828C-418.756,278.971-418.899,277.023-419.185,275.093z M-433.776,284.435v-7.115l6.627,3.558L-433.776,284.435z"></path>
									</svg>
                                    </a>
                                </div>
                                <div class="t481__right_social_links_item">
                                    <a href="https://www.instagram.com/linerfin/" target="_blank">
                                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="28px" height="28px" viewBox="-455 257 48 48" enable-background="new -455 257 48 48" xml:space="preserve">
										<path d="M-430.938,256.987c13.227,0,23.95,10.723,23.95,23.95c0,13.227-10.723,23.95-23.95,23.95 c-13.227,0-23.95-10.723-23.95-23.95C-454.888,267.71-444.165,256.987-430.938,256.987z M-421.407,268.713h-19.06 c-1.484,0-2.688,1.204-2.688,2.69v19.07c0,1.485,1.203,2.689,2.688,2.689h19.06c1.484,0,2.688-1.204,2.688-2.689v-19.07 C-418.72,269.917-419.923,268.713-421.407,268.713z M-430.951,276.243c2.584,0,4.678,2.096,4.678,4.681 c0,2.585-2.095,4.68-4.678,4.68c-2.584,0-4.678-2.096-4.678-4.68C-435.629,278.339-433.535,276.243-430.951,276.243z M-421.579,289.324c0,0.54-0.437,0.978-0.977,0.978h-16.779c-0.54,0-0.977-0.438-0.977-0.978V279.08h2.123 c-0.147,0.586-0.226,1.199-0.226,1.831c0,4.144,3.358,7.504,7.5,7.504c4.142,0,7.5-3.359,7.5-7.504c0-0.632-0.079-1.245-0.226-1.831 h2.061V289.324L-421.579,289.324z M-421.516,275.23c0,0.54-0.438,0.978-0.977,0.978h-2.775c-0.54,0-0.977-0.438-0.977-0.978v-2.777 c0-0.54,0.438-0.978,0.977-0.978h2.775c0.54,0,0.977,0.438,0.977,0.978V275.23z"></path>
									</svg>
                                    </a>
                                </div>
                            </div>
                        </div>--}}
                    </div>
                </div>
                <div class="t481__padding40px"></div>
            </div>
        </div>
        <style>
            @media screen and (max-width: 980px) {
                #rec176751863 .t481__leftcontainer {
                    padding: 20px;
                }
            }

            @media screen and (max-width: 980px) {
                #rec176751863 .t481__imglogo {
                    padding: 20px 0;
                }
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function() {
                t481_highlight();
            });
            $(window).resize(function() {
                t481_setWidth('176751863');
            });
            $(window).load(function() {
                t481_setWidth('176751863');
            });
            $(document).ready(function() {
                t481_setWidth('176751863');
            });
            $(window).resize(function() {
                t481_setBg('176751863');
            });
            $(document).ready(function() {
                t481_setBg('176751863');
            });
        </script>
        <script type="text/javascript">
            $(document).ready(function() {
                setTimeout(function() {
                    t_menusub_init('176751863');
                }, 500);
            });
        </script>
        <style>
            @media screen and (max-width: 980px) {
                #rec176751863 .t-menusub__menu .t-menusub__link-item {
                    color: #000000 !important;
                }
                #rec176751863 .t-menusub__menu .t-menusub__link-item.t-active {
                    color: #000000 !important;
                }
            }
        </style>
        <!--[if IE 8]><style>#rec176751863 .t481 { filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#D9ffffff', endColorstr='#D9ffffff');
        }</style><![endif]-->
    </div>
    <div id="rec176178647" class="r t-rec t-rec_pt_75 t-rec_pb_75" style="padding-top:75px;padding-bottom:75px;background-color:#f0f0f0; " data-record-type="467" data-bg-color="#f0f0f0" data-animationappear="off">
        <!-- T467 -->
        <div class="t467">
            <div class="t-container t-align_center">
                <div class="t-col t-col_10 t-prefix_1">
                    <div class="t467__title t-title t-title_lg t-margin_auto" field="title" style="">
                        <div style="font-size:42px;" data-customstyle="yes">Прогнозируй доходы. Контролируй расходы.
                            <br> Развивай свой бизнес.
                            <br>
                        </div>
                    </div>
                    <div class="t467__descr t-descr t-descr_xl t-margin_auto" field="descr" style="">Система для учета, планирования и анализа финансов.
                        <br> Управлять деньгами легко и эффективно.</div>
                    <a href="https://auth.linerapp.online/" class="t-btn " style="color:#ffffff;background-color:#f0112b;border-radius:50px; -moz-border-radius:50px; -webkit-border-radius:50px;">
                        <table style="width:100%; height:100%;">
                            <tbody>
                            <tr>
                                <td>Подключить</td>
                            </tr>
                            </tbody>
                        </table>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div id="rec176753300" class="r t-rec r_showed r_anim" style=" " data-record-type="126">
        <!-- T118 -->
        <div class="t118">
            <div class="t-container">
                <div class="t-col t-col_12 ">
                    <hr class="t118__line" style="background-color:#000000;opacity:0.2;"> </div>
            </div>
        </div>
    </div>
    <div id="rec176177691" class="r t-rec" style=" " data-animationappear="off" data-record-type="347">
        <!-- T347 -->
        <div class="t347">
            <div class="t-container">
                <div class="t-col t-col_10 t-prefix_1">
                    <div class="t347__table" style="height: 540px;">
                        <div class="t347__cell t-align_left t-valign_top">
                            <div class="t347__bg" bgimgfield="img" style="background-image:url(&#39;https://static.tildacdn.com/tild6532-6466-4266-a263-623337653662/_linerfin_PNG.png&#39;);"></div>
                            <div class="t347__overlay" style="background-image: -moz-linear-gradient(top, rgba(0,0,0,0.40), rgba(0,0,0,0.0)); background-image: -webkit-linear-gradient(top, rgba(0,0,0,0.40), rgba(0,0,0,0.0)); background-image: -o-linear-gradient(top, rgba(0,0,0,0.40), rgba(0,0,0,0.0)); background-image: -ms-linear-gradient(top, rgba(0,0,0,0.40), rgba(0,0,0,0.0));"></div>
                            <a class="t347__play-link t347__play-icon_xl" href="javascript:t347showvideo(&#39;176177691&#39;);">
                                <div class="t347__play-icon">
                                    <svg width="50px" height="50px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50">
                                        <path fill="none" d="M49.5 25c0 13.5-10.9 24.5-24.5 24.5S.6 38.5.6 25 11.5.6 25 .6 49.5 11.5 49.5 25z"></path>
                                        <path fill="#FFFFFF" d="M25 50c13.8 0 25-11.2 25-25S38.8 0 25 0 0 11.2 0 25s11.2 25 25 25zm-5-33.3l14.2 8.8L20 34.3V16.7z"></path>
                                    </svg>
                                </div>
                            </a>
                            <div class="t347__textwrapper">
                                <div class="t347__textwrapper__content t-width t-width_6"> </div>
                            </div>
                        </div>
                        <div class="t347__video-container t347__hidden" data-content-popup-video-url-youtube="LQnq5xpJA1s">
                            <div class="t347__video-carier"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                t347_setHeight('176177691');
                $('.t347').bind('displayChanged', function() {
                    t347_setHeight('176177691');
                });
            });
            $(window).resize(function() {
                t347_setHeight('176177691');
            });
        </script>
    </div>
    <div id="rec176753430" class="r t-rec r_hidden r_anim" style=" " data-record-type="126">
        <!-- T118 -->
        <div class="t118">
            <div class="t-container">
                <div class="t-col t-col_12 ">
                    <hr class="t118__line" style="background-color:#000000;opacity:0.2;"> </div>
            </div>
        </div>
    </div>
    <div id="rec176828545" class="r t-rec t-rec_pt_120 t-rec_pb_165" style="padding-top:120px;padding-bottom:165px;background-color:#f2f2f2; " data-animationappear="off" data-record-type="858" data-bg-color="#f2f2f2">
        <!-- t858 -->
        <div class="t858">
            <div class="t-section__container t-container">
                <div class="t-col t-col_12">
                    <div class="t-section__topwrapper t-align_center">
                        <div class="t-section__title t-title t-title_xs t-animate t-animate_wait" data-animate-style="fadeinup" data-animate-group="yes" field="btitle">Основные возможности
                            <br>
                        </div>
                    </div>
                </div>
            </div>
            <div class="t-container">
                <div class="t858__row">
                    <div class="t858__col t-col t-col_4 t-align_left t-animate t-animate__chain_first-in-row t-animate_wait" data-animate-style="fadeinup" data-animate-chain="yes">
                        <div class="t858__inner-col" style="background-color: rgb(255, 255, 255); border-radius: 5px; box-shadow: rgba(0, 0, 0, 0.1) 0px 0px 10px 0px; height: 399px;">
                            <div class="t858__wrap">
                                <div class="t858__wrap_top"> <img src="{{ asset('assets/landing/25fn_croudfunding.svg') }}" class="t858__img t-img" imgfield="li_img__1533046438146"> </div>
                                <div class="t858__wrap_bottom">
                                    <div class="t-name t-name_lg t858__bottommargin" style="" field="li_title__1533046438146">Финансовая аналитика
                                        <br>
                                    </div>
                                    <div class="t-descr t-descr_sm" style="" field="li_descr__1533046438146">
                                        <div style="text-align:left;" data-customstyle="yes">Используйте аналитику денежных потоков по всем направлениям и проектам. Планируйте доходы и расходы. Формируйте бюджеты. Прогнозируйте кассовые разрывы.
                                            <br>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="t858__col t-col t-col_4 t-align_left t-animate t-animate_wait" data-animate-style="fadeinup" data-animate-chain="yes">
                        <div class="t858__inner-col" style="background-color: rgb(255, 255, 255); border-radius: 5px; box-shadow: rgba(0, 0, 0, 0.1) 0px 0px 10px 0px; height: 399px;">
                            <div class="t858__wrap">
                                <div class="t858__wrap_top"> <img src="{{ asset('assets/landing/25fn_bank.svg') }}" class="t858__img t-img" imgfield="li_img__1533046463239"> </div>
                                <div class="t858__wrap_bottom">
                                    <div class="t-name t-name_lg t858__bottommargin" style="" field="li_title__1533046463239">Интеграция с банками
                                        <br>
                                    </div>
                                    <div class="t-descr t-descr_sm" style="" field="li_descr__1533046463239">Настройте загрузку операций по счетам и распределение по статьям и проектам. Используйте сервис автоматического расчета налогов и платежный календарь.
                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="t858__col t-col t-col_4 t-align_left t-animate t-animate_wait" data-animate-style="fadeinup" data-animate-chain="yes">
                        <div class="t858__inner-col" style="background-color: rgb(255, 255, 255); border-radius: 5px; box-shadow: rgba(0, 0, 0, 0.1) 0px 0px 10px 0px; height: 399px;">
                            <div class="t858__wrap">
                                <div class="t858__wrap_top"> <img src="{{ asset('assets/landing/cowork_mac.svg') }}" class="t858__img t-img" imgfield="li_img__1533046478108"> </div>
                                <div class="t858__wrap_bottom">
                                    <div class="t-name t-name_lg t858__bottommargin" style="" field="li_title__1533046478108">Интеграция с CRM
                                        <br>
                                    </div>
                                    <div class="t-descr t-descr_sm" style="" field="li_descr__1533046478108">Контролируйте оплаты от клиентов в CRM. Планируйте поступления. Используйте сервис выставления и согласования счетов от сотрудников. Формируйте KPI.
                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="t858__row" style="margin-top:50px;">
                    <div class="t858__col t-col t-col_4 t-align_left t-animate t-animate_wait" data-animate-style="fadeinup" data-animate-chain="yes">
                        <div class="t858__inner-col" style="background-color: rgb(255, 255, 255); border-radius: 5px; box-shadow: rgba(0, 0, 0, 0.1) 0px 0px 10px 0px; height: 399px;">
                            <div class="t858__wrap">
                                <div class="t858__wrap_top"> <img src="{{ asset('assets/landing/2web_magnifier.svg') }}" class="t858__img t-img" imgfield="li_img__1586027778430"> </div>
                                <div class="t858__wrap_bottom">
                                    <div class="t-name t-name_lg t858__bottommargin" style="" field="li_title__1586027778430">Проверка контрагентов
                                        <br>
                                    </div>
                                    <div class="t-descr t-descr_sm" style="" field="li_descr__1586027778430">Оценивайте факторы риска и исковую нагрузку потенциальных контрагентов. Используйте сервис автоматического заполнения реквизитов.
                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="t858__col t-col t-col_4 t-align_left t-animate t-animate_wait" data-animate-style="fadeinup" data-animate-chain="yes">
                        <div class="t858__inner-col" style="background-color: rgb(255, 255, 255); border-radius: 5px; box-shadow: rgba(0, 0, 0, 0.1) 0px 0px 10px 0px; height: 399px;">
                            <div class="t858__wrap">
                                <div class="t858__wrap_top"> <img src="{{ asset('assets/landing/Tilda_Icons_39_IT_analytics.svg') }}" class="t858__img t-img" imgfield="li_img__1586027780191"> </div>
                                <div class="t858__wrap_bottom">
                                    <div class="t-name t-name_lg t858__bottommargin" style="" field="li_title__1586027780191">Отчеты в любой период
                                        <br>
                                    </div>
                                    <div class="t-descr t-descr_sm" style="" field="li_descr__1586027780191">Формируйте регулярные отчёты о прибылях и убытках, о движении денежных средств. Контролируйте дебиторскую задолженность и рентабельность проектов.
                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="t858__col t-col t-col_4 t-align_left t-animate t-animate_wait" data-animate-style="fadeinup" data-animate-chain="yes">
                        <div class="t858__inner-col" style="background-color: rgb(255, 255, 255); border-radius: 5px; box-shadow: rgba(0, 0, 0, 0.1) 0px 0px 10px 0px; height: 399px;">
                            <div class="t858__wrap">
                                <div class="t858__wrap_top"> <img src="{{ asset('assets/landing/Tilda_Icons_43_logistics_package.svg') }}" class="t858__img t-img" imgfield="li_img__1586027781330"> </div>
                                <div class="t858__wrap_bottom">
                                    <div class="t-name t-name_lg t858__bottommargin" style="" field="li_title__1586027781330">Учет складских остатков
                                        <br>
                                    </div>
                                    <div class="t-descr t-descr_sm" style="" field="li_descr__1586027781330">Управляйте продажами на основе складских остатков. Будьте в курсе какие товары чаще продаются и какие надо оперативно закупить. Настройте уведомление о заказе.
                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                t858_init('176828545');
            });
        </script>
    </div>
    <div id="rec176835840" class="r t-rec t-rec_pt_120 t-rec_pb_150" style="padding-top:120px;padding-bottom:150px; " data-animationappear="off" data-record-type="599">
        <!-- T599 -->
        <div class="t599">
            <div class="t-section__container t-container">
                <div class="t-col t-col_12">
                    <div class="t-section__topwrapper t-align_center">
                        <div class="t-section__title t-title t-title_xs t-animate t-animate_wait" data-animate-style="fadeinup" data-animate-group="yes" field="btitle">Тарифы</div>
                    </div>
                </div>
            </div>
            <div class="t-container t599__withfeatured">
                <div class="t599__col t-col t-col_4 t-align_center t-animate t-animate__chain_first-in-row t-animate_wait" data-animate-style="fadeinup" data-animate-chain="yes">
                    <div class="t599__content" style="border: 1px solid #e0e6ed; background-color: #fffcfc; border-radius: 9px;">
                        <div class="t599__title t-name t-name_lg" field="title" style="height: 29px;">Standart</div>
                        <div class="t599__subtitle t-descr t-descr_xxs" field="subtitle" style="height: 21px;">1 пользователь в месяц</div>
{{--                        <div class="t599__price t-title t-title_xs" field="price" style="height: 51px;">₽ 499</div>--}}
                        <div class="t599__descr t-descr t-descr_xs" field="descr" style="height: 220px;">
                            <ul>
                                <li>Учет доходов и расходов </li>
                                <li>Автозаполнение контрагентов</li>
                                <li>Выставление счетов</li>
                                <li>Интеграция с CRM</li>
                                <li>Документооборот</li>
                                <li>Платежный календарь</li>
                            </ul>
                        </div>
                        <a href="#popup:myform" target="_blank" class="t599__btn t-btn t-btn_sm" style="color:#ffffff;background-color:#fa082c;border-radius:30px; -moz-border-radius:30px; -webkit-border-radius:30px;">
                            <table style="width:100%; height:100%;">
                                <tbody>
                                <tr>
                                    <td>Подключить</td>
                                </tr>
                                </tbody>
                            </table>
                        </a>
                    </div>
                </div>
                <div class="t599__col t-col t-col_4 t-align_center t599__featured t-animate t-animate_wait" data-animate-style="fadeinup" data-animate-chain="yes">
                    <div class="t599__content" style="border: 1px solid #e0e6ed; background-color: #fffcfc; border-radius: 9px;">
                        <div class="t599__title t-name t-name_lg" field="title2" style="height: 29px;">Business</div>
                        <div class="t599__subtitle t-descr t-descr_xxs" field="subtitle2" style="height: 21px;">1 пользователь в месяц
                            <br>
                        </div>
{{--                        <div class="t599__price t-title t-title_xs" field="price2" style="height: 51px;">₽ 999</div>--}}
                        <div class="t599__descr t-descr t-descr_xs" field="descr2" style="height: 220px;">
                            <ul>
                                <li> Статьи затрат бюджетирования </li>
                                <li> Автоматический расчет налогов </li>
                                <li> Отчет о прибылях и убытках </li>
                                <li>Интеграция с банками </li>
                                <li> Несколько юридических лиц </li>
                                <li> Проверка контрагентов </li>
                            </ul>
                        </div>
                        <a href="#popup:myform" target="" class="t599__btn t-btn t-btn_sm" style="color:#ffffff;background-color:#fa082c;border-radius:30px; -moz-border-radius:30px; -webkit-border-radius:30px;">
                            <table style="width:100%; height:100%;">
                                <tbody>
                                <tr>
                                    <td>Подключить</td>
                                </tr>
                                </tbody>
                            </table>
                        </a>
                    </div>
                </div>
                <div class="t599__col t-col t-col_4 t-align_center t-animate t-animate_wait" data-animate-style="fadeinup" data-animate-chain="yes">
                    <div class="t599__content" style="border: 1px solid #e0e6ed; background-color: #fffcfc; border-radius: 9px;">
                        <div class="t599__title t-name t-name_lg" field="title3" style="height: 29px;">Premium</div>
                        <div class="t599__subtitle t-descr t-descr_xxs" field="subtitle3" style="height: 21px;">1 пользователь в месяц
                            <br>
                        </div>
{{--                        <div class="t599__price t-title t-title_xs" field="price3" style="height: 51px;">₽ 1499</div>--}}
                        <div class="t599__descr t-descr t-descr_xs" field="descr3" style="height: 220px;">
                            <ul>
                                <li>Согласование счетов от сотрудников</li>
                                <li>Разграничение прав сотрудникам</li>
                                <li>Дебиторская задолженность</li>
                                <li>Учет складских остатков</li>
                                <li>Логирование и API</li>
                                <li>Интеграция с 1С</li>
                            </ul>
                        </div>
                        <a href="#popup:myform" target="" class="t599__btn t-btn t-btn_sm" style="color:#ffffff;background-color:#fa082c;border-radius:30px; -moz-border-radius:30px; -webkit-border-radius:30px;">
                            <table style="width:100%; height:100%;">
                                <tbody>
                                <tr>
                                    <td>Подключить</td>
                                </tr>
                                </tbody>
                            </table>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <style type="text/css">
            #rec176835840 .t599__featured .t599__content {
                box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.10) !important;
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function() {
                setTimeout(function() {
                    t599_init('176835840');
                }, 500);
                $(window).bind('resize', t_throttle(function() {
                    t599_init('176835840')
                }, 250));
                $('.t599').bind('displayChanged', function() {
                    t599_init('176835840');
                });
            });
            $(window).load(function() {
                t599_init('176835840');
            });
        </script>
    </div>
    <div id="rec181842782" class="r t-rec t-rec_pt_135 t-rec_pb_135" style="padding-top:135px;padding-bottom:135px;background-color:#efefef; " data-record-type="580" data-bg-color="#efefef" data-animationappear="off">
        <!-- T580 -->
        <div class="t580">
            <div class="t-container">
                <div class="t-col t-col_10 t-prefix_1 t-align_center">
                    <div class="t580__title t-title t-title_sm t-margin_auto t-animate t-animate_wait" data-animate-style="fadeinup" data-animate-group="yes" data-animate-order="1" style="color: rgb(0, 0, 0); transition-delay: 0s;" field="title">
                        <div style="font-size:46px;" data-customstyle="yes">Управляйте финансами эффективно</div>
                    </div>
                    <div class="t580__descr t-descr t-descr_xl t-margin_auto t-animate t-animate_wait" data-animate-style="fadeinup" data-animate-group="yes" data-animate-order="2" data-animate-delay="0.3" style="color: rgb(0, 0, 0); max-width: 600px; transition-delay: 0.3s;" field="descr">
                        <div style="font-size:22px;" data-customstyle="yes">Подключите наш сервис и получите подарок!</div>
                    </div>
                    <div class="t580__buttons">
                        <div class="t580__buttons-wrapper t-margin_auto">
                            <div class="t-animate t-animate_wait" data-animate-style="fadein" data-animate-group="yes" data-animate-order="5" data-animate-delay="0.3" style="transition-delay: 1.1s;">
                                <svg class="t580__arrow-icon_mobile" style="fill:#c9c9c9;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 35 70">
                                    <path d="M31.5 47c-1.1-.9-2.7-.7-3.5.4L20.2 57V5.8c0-1.4-1.1-2.5-2.5-2.5s-2.5 1.1-2.5 2.5V57l-7.8-9.7c-.8-1-2.4-1.2-3.5-.3-1.1.9-1.2 2.4-.4 3.5l12.2 15.2c.5.6 1.2.9 1.9.9s1.5-.3 1.9-.9l12.2-15.2c1-1.1.9-2.6-.2-3.5z"></path>
                                </svg>
                                <svg class="t580__arrow-icon " style="fill:#c9c9c9; " xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 180">
                                    <path d="M54.1 109c-.8 0-1.6-.4-2-1.1-.8-1.1-.5-2.7.6-3.5 1.3-.9 6.8-4 11.6-6.6-15.9-1.3-29.2-8.3-38.5-20.2C8.9 56 8.5 24.1 13.2 3.4c.3-1.3 1.7-2.2 3-1.9 1.3.3 2.2 1.7 1.9 3-4.5 19.6-4.2 49.8 11.6 70 9 11.5 21.5 17.7 37.2 18.4l-1.8-2.3c-1.4-1.7-2.7-3.4-4.1-5.1-.7-.9-1.5-1.9-2.3-2.9-.9-1.1-.7-2.6.4-3.5 1.1-.9 2.6-.7 3.5.4 0 0 0 .1.1.1l6.4 7.9c.5.5.9 1.1 1.4 1.7 1.5 1.8 3.1 3.6 4.4 5.6 0 .1.1.1.1.2.1.3.2.5.3.8v.6c0 .2-.1.4-.2.6-.1.1-.1.3-.2.4-.1.2-.3.4-.5.6-.1.1-.3.2-.5.3-.1 0-.1.1-.2.1-1.2.6-16 8.6-18.1 10-.5.5-1 .6-1.5.6z"></path>
                                </svg>
                            </div>
                            <a href="https://auth.linerapp.online/" target="" class="t580__btn t-btn t-animate t-animate_no-hover t-animate_wait" data-animate-style="zoomin" data-animate-group="yes" data-animate-order="3" data-animate-delay="0.5" style="color: rgb(255, 255, 255); background-color: rgb(232, 7, 30); border-radius: 100px; transition-delay: 0.8s;" data-btneffects-first="btneffects-flash">
                                <table style="width:100%; height:100%;">
                                    <tbody>
                                    <tr>
                                        <td>Подключить</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="t-btn_wrap-effects">
                                    <div class="t-btn_effects"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
            #rec181842782 .t-btn[data-btneffects-first],
            #rec181842782 .t-btn[data-btneffects-second],
            #rec181842782 .t-submit[data-btneffects-first],
            #rec181842782 .t-submit[data-btneffects-second] {
                position: relative;
                overflow: hidden;
                -webkit-transform: translate3d(0, 0, 0);
                transform: translate3d(0, 0, 0);
            }

            #rec181842782 .t-btn[data-btneffects-first="btneffects-flash"] .t-btn_wrap-effects,
            #rec181842782 .t-submit[data-btneffects-first="btneffects-flash"] .t-btn_wrap-effects {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                -webkit-transform: translateX(-85px);
                -ms-transform: translateX(-85px);
                transform: translateX(-85px);
                -webkit-animation-name: flash;
                animation-name: flash;
                -webkit-animation-duration: 3s;
                animation-duration: 3s;
                -webkit-animation-timing-function: linear;
                animation-timing-function: linear;
                -webkit-animation-iteration-count: infinite;
                animation-iteration-count: infinite;
            }

            #rec181842782 .t-btn[data-btneffects-first="btneffects-flash"] .t-btn_wrap-effects_md,
            #rec181842782 .t-submit[data-btneffects-first="btneffects-flash"] .t-btn_wrap-effects_md {
                -webkit-animation-name: flash-md;
                animation-name: flash-md;
            }

            #rec181842782 .t-btn[data-btneffects-first="btneffects-flash"] .t-btn_wrap-effects_lg,
            #rec181842782 .t-submit[data-btneffects-first="btneffects-flash"] .t-btn_wrap-effects_lg {
                -webkit-animation-name: flash-lg;
                animation-name: flash-lg;
            }

            #rec181842782 .t-btn[data-btneffects-first="btneffects-flash"] .t-btn_effects,
            #rec181842782 .t-submit[data-btneffects-first="btneffects-flash"] .t-btn_effects {
                background: -webkit-gradient(linear, left top, right top, from(rgba(255, 255, 255, .1)), to(rgba(255, 255, 255, .4)));
                background: -webkit-linear-gradient(left, rgba(255, 255, 255, .1), rgba(255, 255, 255, .4));
                background: -o-linear-gradient(left, rgba(255, 255, 255, .1), rgba(255, 255, 255, .4));
                background: linear-gradient(90deg, rgba(255, 255, 255, .1), rgba(255, 255, 255, .4));
                width: 45px;
                height: 100%;
                position: absolute;
                top: 0;
                left: 30px;
                -webkit-transform: skewX(-45deg);
                -ms-transform: skewX(-45deg);
                transform: skewX(-45deg);
            }

            @-webkit-keyframes flash {
                20% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
                100% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
            }

            @keyframes flash {
                20% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
                100% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
            }

            @-webkit-keyframes flash-md {
                30% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
                100% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
            }

            @keyframes flash-md {
                30% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
                100% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
            }

            @-webkit-keyframes flash-lg {
                40% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
                100% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
            }

            @keyframes flash-lg {
                40% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
                100% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#rec181842782 .t-btn[data-btneffects-first], #rec181842782 .t-submit[data-btneffects-first]').append('<div class="t-btn_wrap-effects"><div class="t-btn_effects"></div></div>');
                if ($('#rec181842782 .t-btn[data-btneffects-first], #rec181842782 .t-submit[data-btneffects-first]').outerWidth() > 230) {
                    $('#rec181842782 .t-btn[data-btneffects-first] .t-btn_wrap-effects, #rec181842782 .t-submit[data-btneffects-first] .t-btn_wrap-effects').addClass('t-btn_wrap-effects_md');
                }
                if ($('#rec181842782 .t-btn[data-btneffects-first], #rec181842782 .t-submit').outerWidth() > 750) {
                    $('#rec181842782 .t-btn[data-btneffects-first] .t-btn_wrap-effects, #rec181842782 .t-submit[data-btneffects-first] .t-btn_wrap-effects').removeClass('t-btn_wrap-effects_md');
                    $('#rec181842782 .t-btn[data-btneffects-first] .t-btn_wrap-effects, #rec181842782 .t-submit[data-btneffects-first] .t-btn_wrap-effects').addClass('t-btn_wrap-effects_lg');
                }
            });
        </script>
    </div>
    <div id="rec181841906" class="r t-rec" style="opacity: 1;" data-animationappear="off" data-record-type="825">
        <!-- t825 -->
        <div class="t825" style="">
            <div class="t825__btn" style="">
                <div class="t825__btn_wrapper " style="background:#e60505;">
                    <svg class="t825__icon" width="35" height="32" viewBox="0 0 35 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.2667 12.6981H23.3667M11.2667 16.4717H23.3667M4.8104 23.5777C2.4311 21.1909 1 18.1215 1 14.7736C1 7.16679 8.38723 1 17.5 1C26.6128 1 34 7.16679 34 14.7736C34 22.3804 26.6128 28.5472 17.5 28.5472C15.6278 28.5472 13.8286 28.2868 12.1511 27.8072L12 27.7925L5.03333 31V23.8219L4.8104 23.5777Z" stroke="#ffffff" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
                    </svg>
                    <svg class="t825__icon-close" width="16px" height="16px" viewBox="0 0 23 23" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <g stroke="none" stroke-width="1" fill="#000" fill-rule="evenodd">
                            <rect transform="translate(11.313708, 11.313708) rotate(-45.000000) translate(-11.313708, -11.313708) " x="10.3137085" y="-3.6862915" width="2" height="30"></rect>
                            <rect transform="translate(11.313708, 11.313708) rotate(-315.000000) translate(-11.313708, -11.313708) " x="10.3137085" y="-3.6862915" width="2" height="30"></rect>
                        </g>
                    </svg>
                </div>
            </div>
            <div class="t825__popup">
                <div class="t825__popup-container" style="">
                    <div class="t825__mobile-top-panel">
                        <div class="t825__mobile-top-panel_wrapper">
                            <svg class="t825__mobile-icon-close" width="16px" height="16px" viewBox="0 0 23 23" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <desc>Close</desc>
                                <g stroke="none" stroke-width="1" fill="#fff" fill-rule="evenodd">
                                    <rect transform="translate(11.313708, 11.313708) rotate(-45.000000) translate(-11.313708, -11.313708) " x="10.3137085" y="-3.6862915" width="2" height="30"></rect>
                                    <rect transform="translate(11.313708, 11.313708) rotate(-315.000000) translate(-11.313708, -11.313708) " x="10.3137085" y="-3.6862915" width="2" height="30"></rect>
                                </g>
                            </svg>
                        </div>
                    </div>
                    <div class="t825__wrapper" style="">
                        <div class="t825__text-wrapper">
                            <div class="t825__title t-name t-name_xl" style="">Напишите нам</div>
                        </div>
                        <div class="t825__messenger-wrapper">
                            <div class="t825__messenger-block">
                                <a href="https://t.me/Linerfin" data-messenger-telegram="LinerFin" class="t825__messenger t825__telegram t-name t-name_lg" target="_blank" rel="nofollow noopener">
                                    <svg width="62" height="62" xmlns="http://www.w3.org/2000/svg">
                                        <g fill="#0087D0" fill-rule="nonzero">
                                            <path d="M31 0C13.88 0 0 13.88 0 31c0 17.12 13.88 31 31 31 17.12 0 31-13.88 31-31C62 13.88 48.12 0 31 0zm16.182 15.235l-6.737 31.207a.91.91 0 0 1-1.372.58l-10.36-6.777-5.449 5.002a.913.913 0 0 1-1.447-.385l-3.548-11.037L8.74 29.97c-.73-.329-.72-1.477.029-1.764l37.193-13.985c.67-.256 1.361.319 1.219 1.013z"></path>
                                            <path d="M22.966 41.977l.606-5.754 16.807-16.43-20.29 13.325z"></path>
                                        </g>
                                    </svg>
                                </a>
                                <div class="t825__messenger-descr t-descr t-descr_xs" style="">Telegram</div>
                            </div>
                            <div class="t825__messenger-block">
                                <a href="https://api.whatsapp.com/send?phone=79393641299" data-messenger-whatsapp="79393641299" class="t825__messenger t825__whatsapp t-name t-name_lg" rel="nofollow noopener">
                                    <svg width="62" height="62" xmlns="http://www.w3.org/2000/svg">
                                        <g fill="#27D061" fill-rule="nonzero">
                                            <path d="M32.367 14.888c-8.275 0-15.004 6.726-15.007 14.993a14.956 14.956 0 0 0 2.294 7.98l.356.567-1.515 5.533 5.677-1.488.548.325a14.979 14.979 0 0 0 7.634 2.09h.006c8.268 0 14.997-6.727 15-14.995a14.9 14.9 0 0 0-4.389-10.608 14.898 14.898 0 0 0-10.604-4.397zm8.417 21.34c-.369 1.052-2.138 2.013-2.989 2.142-.763.116-1.728.164-2.789-.179a25.28 25.28 0 0 1-2.524-.949c-4.444-1.95-7.345-6.502-7.566-6.802-.222-.301-1.809-2.443-1.809-4.661 0-2.218 1.144-3.307 1.55-3.759.406-.451.886-.564 1.181-.564.295 0 .591.003.849.016.272.014.637-.105.996.773.37.903 1.255 3.12 1.366 3.346.11.225.185.488.037.79-.148.3-.222.488-.443.75-.222.264-.465.588-.664.79-.222.224-.453.469-.194.92.258.45 1.147 1.926 2.463 3.12 1.692 1.535 3.119 2.011 3.562 2.237.443.226.701.188.96-.113.258-.3 1.106-1.316 1.401-1.766.295-.45.59-.376.997-.226.406.15 2.583 1.24 3.026 1.466.443.226.738.338.849.526.11.188.11 1.09-.259 2.143z"></path>
                                            <path d="M31 0C13.88 0 0 13.88 0 31c0 17.12 13.88 31 31 31 17.12 0 31-13.88 31-31C62 13.88 48.12 0 31 0zm1.283 47.573h-.007c-3 0-5.948-.75-8.566-2.171l-9.502 2.48 2.543-9.243a17.735 17.735 0 0 1-2.392-8.918c.003-9.836 8.044-17.838 17.924-17.838 4.795.002 9.296 1.86 12.68 5.232 3.384 3.371 5.247 7.853 5.245 12.62-.004 9.836-8.046 17.838-17.925 17.838z"></path>
                                        </g>
                                    </svg>
                                </a>
                                <div class="t825__messenger-descr t-descr t-descr_xs" style="">WhatsApp</div>
                            </div>
                            <div class="t825__messenger-block">
                                <a href="skype:azgin999?chat" data-messenger-skype-chat="azgin999" class="t825__messenger t825__skype_chat t-name t-name_lg" target="_blank" rel="nofollow noopener">
                                    <svg width="62" height="62" xmlns="http://www.w3.org/2000/svg">
                                        <g fill="#00B2F0" fill-rule="nonzero">
                                            <path d="M33.784 28.904l-3.874-.86c-1.473-.336-3.17-.779-3.17-2.169 0-1.392 1.19-2.364 3.339-2.364 4.328 0 3.933 2.974 6.082 2.974 1.131 0 2.12-.665 2.12-1.808 0-2.668-4.27-4.67-7.892-4.67-3.932 0-8.12 1.67-8.12 6.117 0 2.142.766 4.42 4.98 5.477l5.235 1.307c1.583.39 1.98 1.28 1.98 2.085 0 1.336-1.328 2.642-3.733 2.642-4.699 0-4.047-3.615-6.564-3.615-1.132 0-1.953.779-1.953 1.891 0 2.168 2.632 5.061 8.517 5.061 5.598 0 8.372-2.699 8.372-6.312 0-2.334-1.076-4.811-5.319-5.756z"></path>
                                            <path d="M31 0C13.88 0 0 13.88 0 31c0 17.12 13.88 31 31 31 17.12 0 31-13.88 31-31C62 13.88 48.12 0 31 0zm14.32 45.534a9.732 9.732 0 0 1-6.93 2.872 9.796 9.796 0 0 1-4.824-1.271c-.952.17-1.928.258-2.901.258-2.236 0-4.405-.438-6.447-1.304a16.472 16.472 0 0 1-5.265-3.548 16.478 16.478 0 0 1-3.548-5.265 16.44 16.44 0 0 1-1.303-6.447c0-.958.086-1.916.25-2.854a9.797 9.797 0 0 1-1.234-4.754 9.742 9.742 0 0 1 2.872-6.934 9.739 9.739 0 0 1 6.933-2.872c1.605 0 3.189.4 4.598 1.15l.008-.002a16.667 16.667 0 0 1 3.136-.296 16.496 16.496 0 0 1 11.71 4.85 16.505 16.505 0 0 1 4.853 11.712 16.65 16.65 0 0 1-.276 3.002 9.807 9.807 0 0 1 1.24 4.77 9.735 9.735 0 0 1-2.871 6.933z"></path>
                                        </g>
                                    </svg>
                                </a>
                                <div class="t825__messenger-descr t-descr t-descr_xs" style="">Skype</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
            #rec181841906 .t825__btn-text {
                color: ;
            }
        </style>
        <script>
            $(document).ready(function() {
                setTimeout(function() {
                    t825_initPopup('181841906');
                }, 500);
            });
        </script>
    </div>
    <div id="rec177161524" class="r t-rec t-rec_pt_45 t-rec_pb_30" style="padding-top:45px;padding-bottom:30px;background-color:#111111; " data-record-type="144" data-bg-color="#111111" data-animationappear="off">
        <!-- T134 -->
        <div class="t134">
            <div class="t-container">
                <div class="t-col t-col_10 t-prefix_1"> <img src="{{ asset('assets/landing/_linerfin_PNG.png') }}" class="t134__img" imgfield="img" style="max-width: 100px; width: 100%;">
                    <div class="t134__descr" field="descr" style="color:#ffffff;">
                        <br>© {{ date('Y') }} LINERFIN | Все права защищены
                        <br><a href="mailto:info@linerapp.online">info@linerapp.online</a> |
                        <a href="tel:88002228595">8-800-222-85-95</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="rec176189870" class="r t-rec" style="opacity: 1;" data-animationappear="off" data-record-type="702">
        <!-- T702 -->
        <div class="t702">
            <div class="t-popup" data-tooltip-hook="#popup:myform">
                <div class="t-popup__close">
                    <div class="t-popup__close-wrapper">
                        <svg class="t-popup__close-icon" width="23px" height="23px" viewBox="0 0 23 23" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g stroke="none" stroke-width="1" fill="#fff" fill-rule="evenodd">
                                <rect transform="translate(11.313708, 11.313708) rotate(-45.000000) translate(-11.313708, -11.313708) " x="10.3137085" y="-3.6862915" width="2" height="30"></rect>
                                <rect transform="translate(11.313708, 11.313708) rotate(-315.000000) translate(-11.313708, -11.313708) " x="10.3137085" y="-3.6862915" width="2" height="30"></rect>
                            </g>
                        </svg>
                    </div>
                </div>
                <div class="t-popup__container t-width t-width_6" style="background: transparent; height: 670px;">
                    <div class="t702__wrapper" style="height: 685px;  position: relative;">
                        <script>var amo_forms_params = {"id":636661,"hash":"7adc4ab1ef207d32c7ff435921cd9f1d","locale":"ru"};</script>
                        <script id="amoforms_script" async="async" charset="utf-8" src="https://forms.amocrm.ru/forms/assets/js/amoforms.js"></script>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                setTimeout(function() {
                    t702_initPopup('176189870');
                }, 500); /* hack for Android */
                var ua = navigator.userAgent.toLowerCase();
                var isAndroid = ua.indexOf("android") > -1;
                if (isAndroid) {
                    $('.t-body').addClass('t-body_scrollable-hack-for-android');
                    $('head').append("<style>@media screen and (max-width: 560px) {\n.t-body_scrollable-hack-for-android {\noverflow: visible !important;\n}\n}\n</style>");
                    console.log('Android css hack was inited');
                }
            });
        </script>
        <style>
            #rec176189870 .t-btn[data-btneffects-first],
            #rec176189870 .t-btn[data-btneffects-second],
            #rec176189870 .t-submit[data-btneffects-first],
            #rec176189870 .t-submit[data-btneffects-second] {
                position: relative;
                overflow: hidden;
                -webkit-transform: translate3d(0, 0, 0);
                transform: translate3d(0, 0, 0);
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function() {});
        </script>
    </div>
</div>
<!--/allrecords-->
</body>

</html>
