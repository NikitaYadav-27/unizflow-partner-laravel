<!DOCTYPE html>
<html style="--primary-rgb: {!! env("colors.primary.rgb"); !!}; --menu-prime-color: black;" lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay"
    data-theme-mode="light" data-header-styles="light" data-menu-styles="light" loader="enable">

<head>

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title> Dashboard - {!! env("app.title"); !!} </title>

<link rel="icon" href="{!! env("favicon"); !!}" type="image/x-icon">

<script src="/assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>

<script src="/assets/js/main.js"></script>

<link id="style" href="/assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<link href="/assets/css/styles.min.css" rel="stylesheet">

<link href="/assets/css/icons.css" rel="stylesheet">

<link href="/assets/libs/node-waves/waves.min.css" rel="stylesheet">

<link href="/assets/libs/simplebar/simplebar.min.css" rel="stylesheet">

<link rel="stylesheet" href="/assets/libs/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="/assets/libs/@simonwep/pickr/themes/nano.min.css">

<link rel="stylesheet" href="/assets/libs/choices.js/public/assets/styles/choices.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<link rel="stylesheet" href="/assets/libs/jsvectormap/css/jsvectormap.min.css">

    <link rel="stylesheet" href="/assets/libs/swiper/swiper-bundle.min.css">

<link rel="stylesheet" href="https://cdn.datatables.net/2.2.0/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="/assets/libs/sweetalert2/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script>window.onloads = []; window.intervals = []; </script>

</head>

<body>

    <style>
        .app-sidebar .side-menu__item.active {
            background: linear-gradient(124deg, rgba({!! env("colors.primary.rgb"); !!}, 0.7) 0%, rgb(var(--primary-rgb)) 100%);
            box-shadow: 0px 2px 6px 0px rgba(115, 103, 240, .3);
            color: #fff !important;
        }
        
        a.side-menu__item:hover{
            color: black !important;
        }

        .app-sidebar .side-menu__item.active .side-menu__label {
            color: white;
        }

        .app-sidebar .side-menu__item.active .side-menu__icon {
            color: #ffffff;
        }

        .header .header-content {
            display: flex;
            height: min-content;
            min-height: 55px;
            align-items: center;
        }

        .app-sidebar .slide-menu.child1 .side-menu__item:before {
            display: none !important;
        }

        .app-sidebar .slide-menu.child1 .side-menu__item,
        .app-sidebar .slide-menu.child2 .side-menu__item {
            padding: .45rem 0.6rem;
        }

        .slide.has-sub>.side-menu__item
        {
          margin-block: 0px !important;
        }

        .app-sidebar .slide.has-sub.open > .slide-menu {
            display: block;
        }

        [data-vertical-style=overlay][data-toggled=icon-overlay-close]:not([data-icon-overlay=open]) .app-sidebar .slide.has-sub.open > .slide-menu {
            display: none !important;
        }

        .app-sidebar {
            background: #ffffff !important;
            border-inline-end: 1px solid #e1e9e8 !important;
        }

        .app-sidebar .main-sidebar-header {
            background: #ffffff !important;
            border-block-end: 1px solid #e1e9e8 !important;
            border-inline-end: 1px solid #e1e9e8 !important;
        }

        .main-sidebar {
            background: #ffffff !important;
        }

        [data-vertical-style=overlay][data-toggled=icon-overlay-close][data-icon-overlay=open] .app-sidebar {
            background: #ffffff !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
        }

        #sidebar-scroll{
            overflow-y: scroll !important;
        }

        div.d-md-flex.justify-content-between.align-items-center.col-12.dt-layout-full.col-md {
         padding: 0px !important;
        }

        .select2-selection__rendered{
            min-width: 130px;
        }

        .breadcrumb-item{
            cursor: pointer;
        }
        
        @media (max-width: 991.98px) {
            .main-sidebar {
                background: white !important;
            }
        }
    </style>

<div class="page">
        
        <header class="app-header">

<div class="main-header-container container-fluid">

<div class="header-content-left">

<div class="header-element">
                        <div class="horizontal-logo">
                            <a href="{{ route('home') }}" class="header-logo">
                                <img src="{!! env("logo"); !!}" alt="logo" class="desktop-logo">
                                <img src="{!! env("logo"); !!}" alt="logo" class="toggle-logo">
                                <img src="{!! env("logo"); !!}" alt="logo" class="desktop-dark">
                                <img src="{!! env("logo"); !!}" alt="logo" class="toggle-dark">
                                <img src="{!! env("logo"); !!}" alt="logo" class="desktop-white">
                                <img src="{!! env("logo"); !!}" alt="logo" class="toggle-white">
                            </a>
                        </div>
                    </div>

<div class="header-element">
                        
                        <a aria-label="Hide Sidebar"
                            class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                            data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
                        
                    </div>

</div>

<div class="header-content-right">

<div class="header-element">
                        
                        <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div class="me-sm-2 me-0">
                                <span class="avatar avatar-sm me-2 bg-primary">{{ collect(explode(' ', $user['name'] ?? ''))->filter()->map(fn($n) => strtoupper($n[0] ?? ''))->implode('') }}</span>
                                </div>
                                <div class="d-sm-block d-none">
                                    <p class="fw-semibold mb-0 lh-1">{!! $user['name']; !!}</p>
                                    <span class="op-7 fw-normal d-block fs-11"><i class="ti ti-id"></i>&nbsp;{!! $user['merchant_id']; !!}</span>
                                </div>
                            </div>
                        </a>
                        
                        <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end"
                            aria-labelledby="mainHeaderProfile">
                            
                            <li><a class="dropdown-item d-flex" style="cursor: pointer;"
                                    onclick="navigate('{{ route('profile') }}'); closeDropdown(this);"><i
                                        class="ti ti-user fs-18 me-2 op-7"></i>My Profile</a></li>

<li><a class="dropdown-item d-flex" style="cursor: pointer;"
                                    onclick="navigate('{{ route('user.change-password') }}'); closeDropdown(this);"><i
                                        class="ti ti-lock fs-18 me-2 op-7"></i>Change Password</a></li>

                            <li><a class="dropdown-item d-flex" href="{{ route('logout') }}"><i
                                        class="ti ti-logout fs-18 me-2 op-7"></i>Log Out</a></li>
                        </ul>
                    </div>

</div>

</div>

</header>

<aside class="app-sidebar sticky" id="sidebar">

<div class="main-sidebar-header">
                <div class="d-flex gap-2 justify-content-center align-items-center">
                    <a href="{{ route('home') }}" class="header-logo">
                        <img src="{!! env("logo"); !!}" alt="logo" style="height: 50px;" class="desktop-logo">
                        <img src="{!! env("favicon"); !!}" alt="logo" style="height: 50px;" class="toggle-logo">
                        <img src="{!! env("logo"); !!}" alt="logo" style="height: 50px;" class="desktop-dark">
                        <img src="{!! env("favicon"); !!}" alt="logo" style="height: 50px;" class="toggle-dark">
                        <img src="{!! env("logo"); !!}" alt="logo" style="height: 50px;" class="desktop-white">
                        <img src="{!! env("favicon"); !!}" alt="logo" style="height: 50px;" class="toggle-white">
                    </a>
                   
                </div>

            </div>

<div class="main-sidebar" id="sidebar-scroll">

<nav class="main-menu-container nav nav-pills flex-column sub-open">
                    <div class="slide-left" id="slide-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                            viewBox="0 0 24 24">
                            <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                        </svg>
                    </div>
                    
                    <ul class="main-menu">
                        
                        <li class="slide__category"><span class="category-name"></span></li>

<li class="slide">
                            <a onclick="navigate('{{ route('dashboard') }}'); activeNavigation(this);"
                                class="side-menu__item {{ ($page ?? '') == 'dashboard' ? 'active' : '' }}">
                                <i class="ti ti-home side-menu__icon"></i>
                                <span class="side-menu__label">Dashboard</span>
                            </a>
                        </li>

<li class="slide__category"><span class="category-name">Services</span></li>

<li class="slide has-sub {!! in_array($page,["upi", "static","payin-transactions","settlements"])?"open":"" !!}">
                            <a class="side-menu__item">
                                <i class="ti ti-inbox side-menu__icon"></i>
                                <span class="side-menu__label">Payin</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                               
                                <li class="slide"><a onclick="navigate('{{ route('service.collection.upi') }}'); activeNavigation(this);" class="side-menu__item {!! $page == "upi"?"active":"" !!}"><i class="ti ti-qrcode mx-2"></i> Generate QR</a></li>

<li class="slide"><a onclick="navigate('{{ route('service.collection.transactions') }}'); activeNavigation(this);" class="side-menu__item {!! $page == "payin-transactions"?"active":"" !!}"><i class="ti ti-history mx-2"></i>History</a></li>
                                <li class="slide"><a onclick="navigate('{{ route('service.collection.settlements') }}'); activeNavigation(this);" class="side-menu__item {!! $page == "settlements"?"active":"" !!}"><i class="ti ti-receipt mx-2"></i>Settlements</a></li>
                            </ul>
                        </li>

<li class="slide has-sub {!! in_array($page,["payout", "bulk-payout","payout-transactions"])?"open":"" !!}">
                            <a class="side-menu__item">
                                <i class="ti ti-cash side-menu__icon"></i>
                                <span class="side-menu__label">Payout</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                               
                                <li class="slide"><a onclick="navigate('{{ route('service.payout.create') }}'); activeNavigation(this);" class="side-menu__item {!! $page == "payout"?"active":"" !!}"><i class="ti ti-arrow-up-right mx-2"></i> Make Payout</a></li>
                                <li class="slide"><a onclick="navigate('{{ route('service.payout.transactions') }}'); activeNavigation(this);" class="side-menu__item {!! $page == "payout-transactions"?"active":"" !!}"><i class="ti ti-history mx-2"></i>Payout History</a></li>

                            </ul>
                        </li>

<li class="slide__category"><span class="category-name">Manage</span></li>

<li class="slide has-sub {!! in_array($page,["ledger", "topup"])?"open":"" !!}">
                            <a class="side-menu__item">
                                <i class="ti ti-wallet side-menu__icon"></i>
                                <span class="side-menu__label">Wallet</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                            
                                <li class="slide"><a onclick="navigate('{{ route('wallet.ledger') }}'); activeNavigation(this);" class="side-menu__item {!! $page == "ledger"?"active":"" !!}"><i class="ti ti-file-text mx-2"></i>Wallet Ledger</a></li>
                                <li class="slide"><a onclick="navigate('{{ route('wallet.topup') }}'); activeNavigation(this);" class="side-menu__item {!! $page == "topup"?"active":"" !!}"><i class="ti ti-circle-plus mx-2"></i>Wallet Topup</a></li>
                            </ul>
                        </li>

<li class="slide has-sub {!! in_array($page,["download"])?"open":"" !!}">
                            <a class="side-menu__item">
                                <i class="ti ti-report side-menu__icon"></i>
                                <span class="side-menu__label">Reports</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">

<li class="slide"><a onclick="navigate('{{ route('reports.download') }}'); activeNavigation(this);" class="side-menu__item {!! $page=="download"?"active":"" !!}"><i class="ti ti-download mx-2"></i>Download</a></li>
                            </ul>
                        </li>

<li class="slide">
                            <a href='{{ route('api-docs') }}' target="_blank"
                                class="side-menu__item">
                                <i class="ti ti-file-code side-menu__icon"></i>
                                <span class="side-menu__label">Api Documents</span>
                            </a>
                        </li>

</ul>
                    <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                            width="24" height="24" viewBox="0 0 24 24">
                            <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                        </svg></div>
                </nav>

</div>

</aside>

<style>
            .app-sidebar .side-menu__item:hover {
                background-color: transparent !important;
                cursor: pointer;
            }

            .app-sidebar .side-menu__item.active:hover {
                background-color: #f3f6f8 !important;
                cursor: pointer;
            }

            #loader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: var(--custom-white);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
        </style>

<div class="main-content app-content">

            <div class="container-fluid" id="innerBody">