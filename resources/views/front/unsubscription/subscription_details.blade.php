<?php
use Carbon\Carbon;
?>
<x-after-paywall-layout title="Subscription details">
    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
    <style>
        body {
            margin: 0;
            color: #00120B;
            font-size: 14px;
            line-height: 20px;
            font-family: 'SF Pro Text';
        }

        * {
            box-sizing: border-box;
        }

        img {
            max-width: 100%;
        }

        p {
            margin: 0;
        }

        a {
            text-decoration: none;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0;
            font-family: 'SFRounded';
        }

        button.btn {
            border: none;
            outline: none;
            box-shadow: none;
        }

        .container {
            max-width: 375px;
            margin: 0 auto;
            padding: 0 16px !important;
        }

        .space-ptb {
            padding: 24px 0;
        }

        .space-pt {
            padding-top: 24px;
        }

        .space-pb {
            padding-bottom: 24px;
        }

        .section-title {
            margin-bottom: 16px;
        }

        .section-title h2.title {
            font-size: 24px;
            line-height: 29px;
            letter-spacing: -0.48px;
        }

        .section-title h4.title {
            font-size: 20px;
            line-height: 24px;
        }


        .inner-header.age-header .header-top {
            display: flex;
            position: relative;
            text-align: center;
            justify-content: center;
            align-items: center;
        }

        .inner-header.age-header .header-top .logo {
            line-height: 0;
        }

        .inner-header.age-header .header-top .logo .logo-img {
            height: 28px;
            max-height: 30px;
        }

        .user-box .inner-user-box {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background-image: linear-gradient(#1BC666, #A5DF74);
            padding: 4px 8px;
            border-radius: 50px;
        }

        .subscription-description.with-np {
            font-size: 16px;
            font-weight: 500;
        }
        .subscription-description.with-np span {
            color: #7662F1;
        }

        .user-box .inner-user-box .user-logo {
            line-height: 0;
            flex-shrink: 0;
        }

        .user-box .inner-user-box .user-email {
            color: #ffffff;
            font-size: 12px;
            line-height: 14px;
            font-weight: 500;
        }

        .plan-details-box {
            margin-top: 11px;
        }

        .plan-details-card {
            background-color: rgba(0, 18, 11, 0.1);
            border-radius: 16px;
            padding: 2px;
        }

        .plan-details-card .card-top {
            background-color: #ffffff;
            padding: 16px 12px;
            border-radius: 16px;
            border: rgba(0, 18, 11, 0.1);
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin: 0;
        }

        .plan-details-card .card-top .plan-details {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .plan-details-card .card-top .plan-details p {
            font-family: 'SFRounded';
            font-size: 14px;
            line-height: 20px;
            font-weight: 600;
        }

        .plan-details-card .card-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 20px 12px 18px;
        }

        .plan-details-card .card-bottom .card-details {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .plan-details-card .card-bottom .card-details .card-img {
            height: 28px;
            width: auto;
            flex-shrink: 0;
        }

        .plan-details-card .card-bottom .card-details .card-number {
            font-size: 14px;
            line-height: 20px;
            font-family: 'SFRounded';
            font-weight: 600;
        }

        .plan-details-card .card-bottom .card-edit-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 16px;
            background-color: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0px 12px 24px -8px rgba(0, 0, 0, 0.1);
            border-radius: 50px;
        }

        .plan-details-card .card-bottom .card-edit-btn .card-edit-icon {
            line-height: 0;
            flex-shrink: 0;
        }

        .plan-details-card .card-bottom .card-edit-btn .card-edit-icon svg {
            height: 24px;
            width: 24px;
        }

        .plan-details-card .card-bottom .card-edit-btn .card-edit-text {
            font-size: 14px;
            line-height: 20px;
            font-family: 'SFRounded';
            font-weight: 600;
        }

        .faq-box {
            border: 2px solid rgba(0, 18, 11, 0.1);
            box-shadow: 0 1px 4px 0 rgba(12, 12, 13, 0.05);
            border-radius: 14px;
        }

        .accordion {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .accordion .accordion-item {
            padding: 0 12px;
            border-bottom: 1px solid rgba(0, 18, 11, 0.05);
            margin: 0;
        }

        .accordion .accordion-item:last-child {
            border-bottom: none;
        }

        .accordion .accordion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
        }

        .accordion .accordion-header .accordion-header-left {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            line-height: 20px;
            letter-spacing: -0.32px;
        }

        .accordion .accordion-header .accordion-header-icon {
            flex-shrink: 0;
            line-height: 0;
            transition: all 0.35s;
        }

        .accordion .accordion-header .accordion-header-left svg {
            flex-shrink: 0;
        }

        .accordion .accordion-item:has(.accordion-body.show) .accordion-header .accordion-header-icon {
            transform: rotate(-180deg);
        }

        .accordion .accordion-item .accordion-body {
            display: none;
            padding: 0 0 16px 30px;
        }

        .footer-button {
            position: sticky;
            bottom: 0;
            margin: auto;
            border: none;
            font-weight: 600;
            padding: 30px 16px;
            background-color: white;
            margin: 0 -16px;
        }

        .btn-link {
            font-family: 'SFRounded';
            font-size: 14px;
            line-height: 20px;
            font-weight: 600;
            text-align: center;
            padding: 12px;
            width: 100%;
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #7662F1;
            border-radius: 50px;
            box-shadow: none;
            border: none;
            color: #ffffff;
        }

        .btn-link.style-2 {
            padding: 6px 12px;
            background-color: #ffffff;
            color: #000000;
            margin-top: 16px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0px 12px 24px -8px rgba(0, 0, 0, 0.1);
        }
        .header-top {
            position: relative;
            width: 100%;
            height: 60px; /* adjust if needed */
            display: flex;
            align-items: center;
            justify-content: center; /* Center the logo */
        }

        .logo {
            position: absolute;
            left: 50%;
            transform: translateX(-50%); /* Perfect center */
        }

        .btn-logout {
            position: absolute;
            right: 0;  /* move to right corner */
            background: #fff;
            border: 1px solid #7662F1;
            padding: 8px 10px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        .btn-logout:hover {
            background: #f4f0ff;
        }

    </style>
    @endsection
    <section class="main-section">
        <div class="container">
            <div class="inner-section">
                <!-- Header -->
                    <div class="main-header">
                        <div class="inner-header age-header">
                            <div class="header-top">
                                <div class="logo">
                                    <img class="logo-img" src="{{ asset('subscriptionDetails/images/logo.png') }}" alt="logo">
                                </div>
                                <button class="btn btn-logout" onclick="window.location.href='{{ route('user-logout') }}'">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M16 17L21 12L16 7" stroke="#7662F1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M21 12H9" stroke="#7662F1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 19H5C3.89543 19 3 18.1046 3 17V7C3 5.89543 3.89543 5 5 5H12" stroke="#7662F1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                <!-- Header -->

                <!-- User Box -->
                <div class="user-box">
                    <div class="inner-user-box">
                        <div class="user-logo">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.99992 2.66671C5.05411 2.66671 2.66659 5.05423 2.66659 8.00004C2.66659 10.9459 5.05411 13.3334 7.99992 13.3334C10.9457 13.3334 13.3333 10.9459 13.3333 8.00004C13.3333 5.05423 10.9457 2.66671 7.99992 2.66671ZM1.33325 8.00004C1.33325 4.31785 4.31773 1.33337 7.99992 1.33337C11.6821 1.33337 14.6666 4.31785 14.6666 8.00004C14.6666 11.6822 11.6821 14.6667 7.99992 14.6667C4.31773 14.6667 1.33325 11.6822 1.33325 8.00004ZM8.58918 5.57745C8.26374 5.25201 7.73611 5.25201 7.41067 5.57745C7.08523 5.90289 7.08523 6.43053 7.41067 6.75596C7.73611 7.0814 8.26374 7.0814 8.58918 6.75596C8.91462 6.43053 8.91462 5.90289 8.58918 5.57745ZM6.46786 4.63464C7.314 3.78851 8.68585 3.78851 9.53199 4.63464C10.3781 5.48078 10.3781 6.85264 9.53199 7.69877C8.68585 8.54491 7.314 8.54491 6.46786 7.69877C5.62173 6.85264 5.62173 5.48078 6.46786 4.63464ZM4.46087 10.9226C4.6583 9.68175 5.72533 8.72537 7.02925 8.72537H8.97058C10.2737 8.72537 11.3415 9.6816 11.539 10.9226C11.5968 11.2862 11.349 11.6279 10.9853 11.6858C10.6217 11.7436 10.2801 11.4957 10.2222 11.1321C10.125 10.5211 9.60081 10.0587 8.97058 10.0587H7.02925C6.3985 10.0587 5.87487 10.521 5.77764 11.1321C5.71978 11.4957 5.37811 11.7436 5.0145 11.6858C4.65088 11.6279 4.40301 11.2862 4.46087 10.9226Z" fill="white" />
                            </svg>
                        </div>
                        <p class="user-email">{{$userEmail}}</p>
                    </div>
                </div>
                <!-- User Box -->
                @php
                    if($userSubscriptionData){
                        $formattedEndDate = Carbon::parse($userSubscriptionData['end_date'])->locale('fr_FR')->translatedFormat('F j, Y');
                    }
                @endphp
                <!-- Plan Details -->
                <div class="plan-details-box">
                    <div class="section-title">
                        <h2 class="title">{{$currentPlanName}}</h2>
                        @if($CanceledSubscription)
                        <p class="subscription-description with-np mt-12">
                            Votre actuel <span>{{$currentPlanName}}</span> sera annulé le {{$formattedEndDate}}.
                        </p>
                        @endif
                        @if($nextPlanName && !$CanceledSubscription)
                        <p class="subscription-description with-np mt-12">
                            Votre nouveau <span>{{$nextPlanName}}</span> commencera le {{$formattedEndDate}}.
                        </p>
                        @endif
                    </div>
                    @if($userSubscriptionData == null)
                        <div class="plan-details-card">
                            No Details Found
                        </div>
                    @elseif($userSubscriptionAlreadyCanceledData != null)
                        <div class="plan-details-card">
                            <ul class="card-top">
                                <p> Votre dernier abonnement a déjà été annulé </p>
                                {{-- <li class="plan-details">
                                    <p>Cycle de facturation</p>
                                    <p>{{$userSubscriptionAlreadyCanceledData['billing_cycle']}} mois</p>
                                </li>
                                <li class="plan-details">
                                    <p>Montant</p>
                                    <p>{{$userSubscriptionAlreadyCanceledData['amount']}}€</p>
                                </li>
                                <li class="plan-details">
                                    <p>Date de facturation</p>
                                    <p>{{ $formattedEndDate }}</p>
                                </li> --}}
                            </ul>
                            {{-- <div class="card-bottom">
                                <div class="card-details">
                                    <img src="{{ asset('subscriptionDetails/images/'.$brand.'.png') }}" class="card-img">
                                    <p class="card-number">•••• •••• •••• {{$last4}}</p>
                                </div>
                                <button class="btn card-edit-btn" onclick="window.location.href='{{ route('update-card', ['custId' => $cust_id]) }}'">
                                    <div class="card-edit-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M17.1714 2.58641C17.9526 1.80357 19.2192 1.80501 20.0001 2.5859L21.4141 3.9999C22.1949 4.78074 22.1964 6.0472 21.4137 6.82847C21.4136 6.82865 21.4139 6.82829 21.4137 6.82847L10.83 17.4122C10.8296 17.4126 10.8292 17.413 10.8288 17.4134C10.4525 17.7911 9.94206 18 9.414 18H7C6.44772 18 6 17.5523 6 17V14.586C6 14.0579 6.20894 13.5474 6.5868 13.171C6.58713 13.1707 6.58745 13.1703 6.58778 13.17L17.1714 2.58641C17.1715 2.58628 17.1712 2.58654 17.1714 2.58641ZM18.914 6.49979L17.5002 5.08601L16.086 6.50022L17.4998 7.91401L18.914 6.49979ZM5 4.00001C4.44728 4.00001 4 4.44729 4 5.00001V19C4 19.5527 4.44728 20 5 20H19C19.5527 20 20 19.5527 20 19V11C20 10.4477 20.4477 10 21 10C21.5523 10 22 10.4477 22 11V19C22 20.6573 20.6573 22 19 22H5C3.34272 22 2 20.6573 2 19V5.00001C2 3.34272 3.34272 2.00001 5 2.00001H13C13.5523 2.00001 14 2.44772 14 3.00001C14 3.55229 13.5523 4.00001 13 4.00001H5Z" fill="#00120B" />
                                        </svg>
                                    </div>
                                    <span class="card-edit-text">Modifier</span>
                                </button>
                            </div> --}}
                        </div>
                    @elseif($paidWithKlarna == true)
                        <div class="plan-details-card">
                            <ul class="card-top">
                                <li class="plan-details">
                                    <p>Méthode de paiement</p>
                                    <p>Klarna - Paiement unique</p>
                                </li>
                                <li class="plan-details">
                                    <p>Montant payé</p>
                                    <p>{{$userSubscriptionData['amount']}}€</p>
                                </li>
                                <li class="plan-details">
                                    <p>Expiration du plan</p>
                                    <p>{{ $formattedEndDate }}</p>
                                </li>
                            </ul>
                            <div class="card-bottom">
                                <div class="card-details">
                                    <img src="{{ asset('subscriptionDetails/images/klarna-img.png') }}" class="card-img">
                                    <p class="card-note">Votre plan a été réglé via Klarna. Aucun prélèvement récurrent ne sera effectué.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="plan-details-card">
                            <ul class="card-top">
                                @if($userSubscriptionData['is_yearly_commitment'] == 1)
                                    @php
                                        $startDate = Carbon::parse($userSubscriptionData['start_date']);
                                        $endDate = $startDate->copy()->addYear();

                                        $now = Carbon::now();

                                        // months completed already
                                        $monthsPaid = $startDate->diffInMonths($now) + 1;
                                        if ($monthsPaid > 12) $monthsPaid = 12;

                                        $monthlyPrice = $userSubscriptionData['amount'];
                                        $totalPaidSoFar = $monthsPaid * $monthlyPrice;
                                        $totalYearCost = 12 * $monthlyPrice;

                                        $formattedStart = $startDate->format('d M Y');
                                        $formattedEnd = $endDate->format('d M Y');
                                    @endphp
                                    <li class="plan-details">
                                        <p>Type d'abonnement</p>
                                        <p>Engagement annuel (paiement mensuel)</p>
                                    </li>

                                    <li class="plan-details">
                                        <p>Date de début</p>
                                        <p>{{ $formattedStart }}</p>
                                    </li>

                                    <li class="plan-details">
                                        <p>Date de fin</p>
                                        <p>{{ $formattedEnd }}</p>
                                    </li>

                                    <li class="plan-details">
                                        <p>Mois déjà réglés</p>
                                        <p>{{ $monthsPaid }} / 12 mois</p>
                                    </li>

                                    <li class="plan-details">
                                        <p>Paiement mensuel</p>
                                        <p>{{ $monthlyPrice }}€</p>
                                    </li>

                                    <li class="plan-details">
                                        <p>Total réglé jusqu’à présent</p>
                                        <p>{{ $totalPaidSoFar }}€</p>
                                    </li>

                                    <li class="plan-details">
                                        <p>Total sur l'année</p>
                                        <p>{{ $totalYearCost }}€</p>
                                    </li>

                                    <li class="plan-details warning-text">
                                        <p>Annulation</p>
                                        @if($monthsPaid < 12)
                                            <p class="text-red">Annulation possible uniquement après {{ Carbon::parse($userSubscriptionData['lockDate'])->toDateString() }}</p>
                                        @else
                                            <p class="text-green">Vous pouvez résilier maintenant</p>
                                        @endif
                                    </li>
                                @else
                                    <li class="plan-details">
                                        <p>Cycle de facturation</p>
                                        <p>{{$userSubscriptionData['billing_cycle']}} mois</p>
                                    </li>
                                    <li class="plan-details">
                                        <p>Montant</p>
                                        {{-- NEW --}}
                                        @if(isset($applyToExisting))
                                            <p>{{ $currentPlanPrice }}€ </p>
                                        @else
                                            {{$userSubscriptionData['amount']}}€
                                        @endif
                                    </li>
                                    <li class="plan-details">
                                        <p>Date de facturation</p>
                                        <p>{{ $formattedEndDate }}</p>
                                    </li>
                                @endif
                            </ul>
                            <div class="card-bottom">
                                <div class="card-details">
                                    <img src="{{ asset('subscriptionDetails/images/'.$brand.'.png') }}" class="card-img">
                                    <p class="card-number">•••• •••• •••• {{$last4}}</p>
                                </div>
                                <button class="btn card-edit-btn" onclick="window.location.href='{{ route('update-card', ['custId' => $cust_id]) }}'">
                                    <div class="card-edit-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M17.1714 2.58641C17.9526 1.80357 19.2192 1.80501 20.0001 2.5859L21.4141 3.9999C22.1949 4.78074 22.1964 6.0472 21.4137 6.82847C21.4136 6.82865 21.4139 6.82829 21.4137 6.82847L10.83 17.4122C10.8296 17.4126 10.8292 17.413 10.8288 17.4134C10.4525 17.7911 9.94206 18 9.414 18H7C6.44772 18 6 17.5523 6 17V14.586C6 14.0579 6.20894 13.5474 6.5868 13.171C6.58713 13.1707 6.58745 13.1703 6.58778 13.17L17.1714 2.58641C17.1715 2.58628 17.1712 2.58654 17.1714 2.58641ZM18.914 6.49979L17.5002 5.08601L16.086 6.50022L17.4998 7.91401L18.914 6.49979ZM5 4.00001C4.44728 4.00001 4 4.44729 4 5.00001V19C4 19.5527 4.44728 20 5 20H19C19.5527 20 20 19.5527 20 19V11C20 10.4477 20.4477 10 21 10C21.5523 10 22 10.4477 22 11V19C22 20.6573 20.6573 22 19 22H5C3.34272 22 2 20.6573 2 19V5.00001C2 3.34272 3.34272 2.00001 5 2.00001H13C13.5523 2.00001 14 2.44772 14 3.00001C14 3.55229 13.5523 4.00001 13 4.00001H5Z" fill="#00120B" />
                                        </svg>
                                    </div>
                                    <span class="card-edit-text">Modifier</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                <!-- Plan Details -->

                <!-- FAQ -->
                <div class="space-ptb">
                    <div class="section-title">
                        <h4 class="title">FAQ</h4>
                    </div>
                    <div class="faq-box">
                        <ul class="accordion">
                            <li class="accordion-item">
                                <h5 class="accordion-header" href=#>
                                    <div class="accordion-header-left">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 21C7.029 21 3 16.971 3 12C3 7.029 7.029 3 12 3C16.971 3 21 7.029 21 12C21 16.971 16.971 21 12 21Z" fill="#818EFF" />
                                            <path d="M12 13.25V13C12 12.183 12.505 11.74 13.011 11.4C13.505 11.067 14 10.633 14 9.83301C14 8.72801 13.105 7.83301 12 7.83301C10.895 7.83301 10 8.72801 10 9.83301" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M11.999 16C11.861 16 11.749 16.112 11.75 16.25C11.75 16.388 11.862 16.5 12 16.5C12.138 16.5 12.25 16.388 12.25 16.25C12.25 16.112 12.138 16 11.999 16" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        J'ai besoin de plus d'informations sur l'utilisation de mon compte. Puis-je avoir de l'assistance ?
                                    </div>
                                    <div class="accordion-header-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M16.7071 9.29289C17.0976 9.68342 17.0976 10.3166 16.7071 10.7071L12.7071 14.7071C12.3166 15.0976 11.6834 15.0976 11.2929 14.7071L7.29289 10.7071C6.90237 10.3166 6.90237 9.68342 7.29289 9.29289C7.68342 8.90237 8.31658 8.90237 8.70711 9.29289L12 12.5858L15.2929 9.29289C15.6834 8.90237 16.3166 8.90237 16.7071 9.29289Z" fill="#00120B" fill-opacity="0.3" />
                                        </svg>
                                    </div>
                                </h5>
                                <p class="accordion-body">
                                    Vous pouvez créer un ticket dans l’onglet support de l’application ou nous envoyer un e-mail à : support@reveal.club
                                </p>
                            </li>
                        @if($paidWithKlarna == true)
                            <li class="accordion-item">
                                <h5 class="accordion-header" href=#>
                                    <div class="accordion-header-left">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 21C7.029 21 3 16.971 3 12C3 7.029 7.029 3 12 3C16.971 3 21 7.029 21 12C21 16.971 16.971 21 12 21Z" fill="#818EFF" />
                                            <path d="M12 13.25V13C12 12.183 12.505 11.74 13.011 11.4C13.505 11.067 14 10.633 14 9.83301C14 8.72801 13.105 7.83301 12 7.83301C10.895 7.83301 10 8.72801 10 9.83301" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M11.999 16C11.861 16 11.749 16.112 11.75 16.25C11.75 16.388 11.862 16.5 12 16.5C12.138 16.5 12.25 16.388 12.25 16.25C12.25 16.112 12.138 16 11.999 16" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        Puis-je annuler mon plan payé avec Klarna ?
                                    </div>
                                    <div class="accordion-header-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M16.7071 9.29289C17.0976 9.68342 17.0976 10.3166 16.7071 10.7071L12.7071 14.7071C12.3166 15.0976 11.6834 15.0976 11.2929 14.7071L7.29289 10.7071C6.90237 10.3166 6.90237 9.68342 7.29289 9.29289C7.68342 8.90237 8.31658 8.90237 8.70711 9.29289L12 12.5858L15.2929 9.29289C15.6834 8.90237 16.3166 8.90237 16.7071 9.29289Z" fill="#00120B" fill-opacity="0.3" />
                                        </svg>
                                    </div>
                                </h5>
                                <p class="accordion-body">
                                    Les paiements Klarna sont uniques et non remboursables. Il n’est pas possible de résilier ou d’interrompre un plan actif réglé via Klarna.
                                </p>
                            </li>
                            <li class="accordion-item">
                                <h5 class="accordion-header" href=#>
                                    <div class="accordion-header-left">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 21C7.029 21 3 16.971 3 12C3 7.029 7.029 3 12 3C16.971 3 21 7.029 21 12C21 16.971 16.971 21 12 21Z" fill="#818EFF" />
                                            <path d="M12 13.25V13C12 12.183 12.505 11.74 13.011 11.4C13.505 11.067 14 10.633 14 9.83301C14 8.72801 13.105 7.83301 12 7.83301C10.895 7.83301 10 8.72801 10 9.83301" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M11.999 16C11.861 16 11.749 16.112 11.75 16.25C11.75 16.388 11.862 16.5 12 16.5C12.138 16.5 12.25 16.388 12.25 16.25C12.25 16.112 12.138 16 11.999 16" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        Quand mon plan Klarna prendra-t-il fin ?
                                    </div>
                                    <div class="accordion-header-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M16.7071 9.29289C17.0976 9.68342 17.0976 10.3166 16.7071 10.7071L12.7071 14.7071C12.3166 15.0976 11.6834 15.0976 11.2929 14.7071L7.29289 10.7071C6.90237 10.3166 6.90237 9.68342 7.29289 9.29289C7.68342 8.90237 8.31658 8.90237 8.70711 9.29289L12 12.5858L15.2929 9.29289C15.6834 8.90237 16.3166 8.90237 16.7071 9.29289Z" fill="#00120B" fill-opacity="0.3" />
                                        </svg>
                                    </div>
                                </h5>
                                <p class="accordion-body">
                                    Votre accès prendra fin automatiquement à la date suivante : <strong>{{ $formattedEndDate }}</strong>. Aucun renouvellement automatique ne sera effectué.
                                </p>
                            </li>
                        @else
                            <li class="accordion-item">
                                <h5 class="accordion-header" href=#>
                                    <div class="accordion-header-left">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 21C7.029 21 3 16.971 3 12C3 7.029 7.029 3 12 3C16.971 3 21 7.029 21 12C21 16.971 16.971 21 12 21Z" fill="#818EFF" />
                                            <path d="M12 13.25V13C12 12.183 12.505 11.74 13.011 11.4C13.505 11.067 14 10.633 14 9.83301C14 8.72801 13.105 7.83301 12 7.83301C10.895 7.83301 10 8.72801 10 9.83301" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M11.999 16C11.861 16 11.749 16.112 11.75 16.25C11.75 16.388 11.862 16.5 12 16.5C12.138 16.5 12.25 16.388 12.25 16.25C12.25 16.112 12.138 16 11.999 16" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        Comment faire pour résilier mon adhésion au Reveal ?
                                    </div>
                                    <div class="accordion-header-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M16.7071 9.29289C17.0976 9.68342 17.0976 10.3166 16.7071 10.7071L12.7071 14.7071C12.3166 15.0976 11.6834 15.0976 11.2929 14.7071L7.29289 10.7071C6.90237 10.3166 6.90237 9.68342 7.29289 9.29289C7.68342 8.90237 8.31658 8.90237 8.70711 9.29289L12 12.5858L15.2929 9.29289C15.6834 8.90237 16.3166 8.90237 16.7071 9.29289Z" fill="#00120B" fill-opacity="0.3" />
                                        </svg>
                                    </div>
                                </h5>
                                <p class="accordion-body">
                                    Il vous suffit de cliquer sur le bouton « résilier mon abonnement » en bas de cette page
                                </p>
                            </li>
                            <li class="accordion-item">
                                <h5 class="accordion-header" href=#>
                                    <div class="accordion-header-left">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 21C7.029 21 3 16.971 3 12C3 7.029 7.029 3 12 3C16.971 3 21 7.029 21 12C21 16.971 16.971 21 12 21Z" fill="#818EFF" />
                                            <path d="M12 13.25V13C12 12.183 12.505 11.74 13.011 11.4C13.505 11.067 14 10.633 14 9.83301C14 8.72801 13.105 7.83301 12 7.83301C10.895 7.83301 10 8.72801 10 9.83301" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M11.999 16C11.861 16 11.749 16.112 11.75 16.25C11.75 16.388 11.862 16.5 12 16.5C12.138 16.5 12.25 16.388 12.25 16.25C12.25 16.112 12.138 16 11.999 16" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        J'ai des soucis avec mon abonnement. Puis-je obtenir de l'assistance ?
                                    </div>
                                    <div class="accordion-header-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M16.7071 9.29289C17.0976 9.68342 17.0976 10.3166 16.7071 10.7071L12.7071 14.7071C12.3166 15.0976 11.6834 15.0976 11.2929 14.7071L7.29289 10.7071C6.90237 10.3166 6.90237 9.68342 7.29289 9.29289C7.68342 8.90237 8.31658 8.90237 8.70711 9.29289L12 12.5858L15.2929 9.29289C15.6834 8.90237 16.3166 8.90237 16.7071 9.29289Z" fill="#00120B" fill-opacity="0.3" />
                                        </svg>
                                    </div>
                                </h5>
                                <p class="accordion-body">
                                    Vous pouvez créer un ticket dans l’onglet support de l’application ou nous envoyer un e-mail à : support@reveal.club
                                </p>
                            </li>
                        @endif
                        </ul>
                    </div>
                </div>
                <!-- FAQ -->
                @php
                    $isEnded = false;
                    if ($userSubscriptionData) {
                        $isEnded = in_array($userSubscriptionData->status, ['canceled', 'expired'])
                            || Carbon::parse($userSubscriptionData->end_date)->isPast();
                    }
                @endphp

                @if($userSubscriptionData == null || $isEnded)
                <div class="footer-button">
                    <a href="{{route('user-subscription-package')}}" class="btn-link">Activate mon plan</a>
                </div>
                @elseif($paidWithKlarna == true)
                    @if (str_starts_with($userSubscriptionData->subscription_id, 'klarna_'))

                    @else
                        <a href="{{route('change-plan-page')}}" class="btn-link">Modifier mon plan</a>
                        <a href="{{ url('feedback/1') }}" class="btn-link style-2">Résilier mon abonnement</a>
                    @endif
                @else
                <div class="footer-button">
                    @if($CanceledSubscription != 1)
                        @php
                            $canCancel = true;
                            if(isset($userSubscriptionData->is_cancellation_locked) && $userSubscriptionData->is_cancellation_locked == 1) {
                                if(isset($userSubscriptionData->lockDate) && Carbon::now()->lt(Carbon::parse($userSubscriptionData->lockDate))) {
                                    $canCancel = false;
                                }
                            }
                        @endphp
                        @if($canCancel)
                            <a href="{{route('change-plan-page')}}" class="btn-link">Modifier mon plan</a>
                            <a href="{{ url('feedback/1') }}" class="btn-link style-2">Résilier mon abonnement</a>
                        @else
                            <a class="btn-link style-2" href="#">
                                Résiliation et modification verrouillées jusqu'au {{ Carbon::parse($userSubscriptionData->lockDate)->toDateString() }}
                            </a>
                        @endif
                    @else
                        <a href="{{route('discardCancelSubscription')}}" class="btn-link">Oubliez l'annulation; J'aimerais rester abonné.</a>
                    @endif
                </div>
                @endif
            </div>
    </section>

    <!-- Scrips -->
    @section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            showMethod: 'slideDown',
            timeOut: 60000
        };

        @if(Session::has('success'))
        toastr.success("{{ session('success') }}", 'Success', {
            "background-color": "#4CAF50"
        });
        @endif

        @if(Session::has('error'))
        toastr.error("{{ session('error') }}", 'Error', {
            "background-color": "#f44336"
        });
        @endif
    </script>
    <script>
        $('.accordion-header').click(function(e) {
            e.preventDefault();

            let $this = $(this);

            if ($this.next().hasClass('show')) {
                $this.next().removeClass('show');
                $this.next().slideUp(350);
            } else {
                $this.parent().parent().find('li .accordion-body').removeClass('show');
                $this.parent().parent().find('li .accordion-body').slideUp(350);
                $this.next().toggleClass('show');
                $this.next().slideToggle(350);
            }
        });
    </script>
    @endsection
</x-after-paywall-layout>
