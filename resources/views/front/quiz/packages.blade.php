<?php

use App\Models\Plan;
use App\Models\UserReferenceAnswer;
use Illuminate\Support\Facades\Session;

?>
<x-paywall-layout title="Paywall">
    @section('styles')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
    <style>
        /* Yearly Commitment Info Styles */
        .yearly-commitment-info p{
            font-size: 16px !important;
            letter-spacing: -0.32px !important;
            line-height: 24px !important;
            font-weight: normal !important;
            font-family: var(--body-font) !important;
            color: #00120b !important;
            margin-top: 0px !important;
        }
        #klarnaButton img{
            width: 48px;
        }
        .mt-30 {
            margin-top: 30px;
        }
        .cf-alerts {
            margin-bottom: 15px;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 6px;
            padding: 12px 15px;
            font-size: 15px;
            line-height: 1.4;
            text-align: left;
            font-weight: 500;
        }

        .alert-danger:hover {
            background-color: #f5c6cb;
        }

        #loader , #loader1 {
            width: 24px;
            height: 24px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: none;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .loading {
            background-color: #ccc;
            pointer-events: none;
        }

        .main-btn:disabled {
            background-color: #007bff7d;
            /* Lighten button when disabled */
            cursor: not-allowed;
        }

        .form-control {
            margin-bottom: 15px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            font-size: 14px;
            line-height: 16px;
            padding: 16px 14px;
            width: 100%;
            box-shadow: 0px 12px 24px -8px rgba(0, 0, 0, 0.1);
        }

        .submit-button svg {
            fill: white;
            width: 16px;
            height: 16px;
        }

        .message {
            margin-top: 10px;
            font-size: 14px;
        }

        .fixed-main-btn {
            position: sticky;
            left: inherit;
            transform: inherit;
            bottom: 0;
            margin: 0 auto;
            padding: 20px 16px;
            max-width: 375px;
            z-index: 999;
            background-color: white;
        }
        .price-box-wrapper .price-right {
            margin-top: 18px;
        }
        .price-box-wrapper .plan-discount {
            margin-top: 6px;
        }
        .price-box-wrapper .accordion-title .price-left + .card-img {
            max-width: 40px !important;
            position: absolute;
            top: 12px;
            right: 16px;
        }
        .price-box-wrapper .discount {
            white-space:wrap;
            width: 210px;
            text-align: center;
            top: -1px;
        }
        .price-box-wrapper .price-box:has(.accordion-title.open .discount) {
            padding-top: 44px;
        }

        /* Prevent this summary from inheriting any `.plan { display: flex }` rules */
        .yearly-commitment-info {
            display: none; /* hidden by default */
            padding: 10px;
            border: 1px dashed #cbd5e1;
            margin-bottom: 8px;
            box-sizing: border-box;
            flex: none !important;
            align-items: stretch !important;
            flex-direction: column !important;
        }

        /* If shown via JS (inline style or added class), force block layout */
        .yearly-commitment-info[style*="display:block"],
        .yearly-commitment-info.show {
            display: block !important;
        }
    </style>
    @endsection

    <section class="banner">
        <div class="container">
            <div class="inner-banner">
                <img src="{{ asset('paywall/images/banner/01.png') }}" class="banner-img">
            </div>
        </div>
    </section>

    <!-- Personalized Program -->
    <section class="bg-light space-small-ptb personalised-program">
        <div class="container">
            <div class="section-title">
                <p class="subtitle text-align-center">Ton programme personnalisé de perte de gras est prêt !</p>
                <!-- <h3 class="title text-align-center">Renforce ta confiance en toi<br>et atteins <span>{{ $desiredWeight }}</span> d’ici le <span>{{ $newGoalAchieveDate }}</span></h3> -->
                <h3 class="title text-align-center">Suis ton programme sur mesure pour atteindre <span>{{ $desiredWeight }}</span> kg</h3>
            </div>
            <!-- special-offer -->
            <div>
                <div class="special-offer style-02">
                    <div class="container">
                        <div class="offer-wrapper">
                            <p class="offer-info">Ta place est réservée pour</p>
                            <div class="offer-timer text-align-center">
                                <div class="countdown">
                                    <h6 id="mins"></h6>
                                    <h6>:</h6>
                                    <h6 id="secs"></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- special-offer -->
            <p class="price-title text-align-center">Sélectionne ton plan pour commencer :</p>
            <div class="price-box-wrapper">
                <div id="accordion" class="accordion-container">
                    @foreach($getPackages as $package)
                    <?php
                    $perMonthOnPrice = round(($package['price'] / 12), 2);
                    $perMonthOnDiscPrice = round(($package['discprice'] / 12), 2);

                    $priceDifference = ((float) $package['total_price'] - (float) $package['total_disc_price']);
                    $dicsPercent = (int)(($priceDifference * 100) / (float) $package['total_price']);
                    ?>
                    <div class="price-box" data-package-id="{{ $package['id'] }}" data-for-klarna="{{ $package['for_klarna'] }}" data-yearly-commitment="{{ $package['is_yearly_commitment'] }}">
                        <div class="accordion-title js-accordion-title">
                            @if(!empty($package['offer_label']))
                            <span class="discount">{{ $package['offer_label'] }}</span>
                            @endif
                            <div class="plan-select-icon">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="price-left">
                                <h4 class="plan-duration">{{ $package['name'] }}</h4>
                                <div class="plan-discount">
                                    (-{{ $dicsPercent }}%)
                                    <p class="before-price">{{ $package['total_price'] }} €</p>
                                    →
                                    <p class="discounted-price">{{ $package['total_disc_price'] }} €</p>
                                </div>
                            </div>
                            @if($package['for_klarna'] == 1)
                            <img src="{{ asset('subscriptionDetails/images/klarna-img.png') }}" class="card-img" style="max-width: 50px;">
                            @endif
                            <div class="price-right">
                                <h4 class="main-amount">{{ $package['discprice'] }}</h4>
                                <div class="per-month-amount">
                                    <p class="before-price">{{ $package['price'] }}</p>
                                    <p class="per-month">€/mois</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>


    </section>
    <!-- Personalized Program -->

    <!-- Order Summery -->
    <?php
    $planDetails = Plan::where(['id' => Session::get('planId')])->first();
    // dd($planDetails);
    $trainerDiscountMsg = Session::get('trainerDiscountMsg');
    ?>
    <section class="space-small-pt" id="order-summery">
        <div class="container">
            <div class="order-summery">
                <h4 class="title">Résumé de la commande</h4>
                <div class="plan actual-plan">
                    <p class="plan-type"></p>
                    <p class="amount">309,99€</p>
                </div>
                {{-- Yearly Commitment Summary (hidden by default) --}}
                <div class="yearly-commitment-info" style="display:none;">
                    <p><strong>Plan engagé annuel (paiement mensuel)</strong></p>
                    <p>Montant aujourd'hui : <span class="yc-pay-today">0€</span></p>
                    <p>Puis <span class="yc-monthly">0€</span> par mois pendant 11 mois</p>
                    <p>Total pour 12 mois : <span class="yc-total">0€</span></p>
                    <small class="yc-note">La résiliation n’est autorisée que pendant le dernier mois.</small>
                </div>

                <div class="plan discount-info">
                    <p class="discount-percentage">
                        {{ $trainerDiscountMsg ?? 'Réduction pour les 1k' }} (-50 %)
                    </p>
                    </p>
                    <p class="discount-amount"><b>-155,00€</b></p>
                </div>
                <div class="plan klarna-fee" style="display: none;">
                    <p>frais de Klarna</p>
                    <p class="klarna-charge">0€</p>
                </div>
                <div class="promocode-applied" style="display: none;">
                    <p>Code promotionnel appliqué</p>
                    <p class="promocode-discount">0€</p>
                </div>
                <div class="total">
                    <p>Total à payer aujourd'hui</p>
                    <p class="total-amount">154,99 €</p>
                    <p class="promocode-id" style="display: none">0</p>
                </div>
                <p class="saved-amount">Économise -155,00 € sur ton plan annuel (-50 %)</p>

                <div class="card-section"  style="margin-top: 20px;">
                    <div class="card-top">
                        <div class="card-img-wrapper klarna_img">
                            <img src="{{ asset('subscriptionDetails/images/klarna-img.png') }}" class="card-img">
                        </div>
                        <div class="card-img-wrapper non_klarna_img">
                            <img class="visa" src="{{ asset('paywall/images/svgs/visa.svg') }}" alt="visa">
                            <img src="{{ asset('paywall/images/svgs/mastercard.svg') }}" alt="mastercard">
                            <img src="{{ asset('paywall/images/svgs/maestro.svg') }}" alt="maestro">
                            <img src="{{ asset('paywall/images/svgs/ae.svg') }}" alt="american express">
                        </div>
                        <img class="stripe" src="{{ asset('paywall/images/svgs/stripe.svg') }}" alt="stripe">
                    </div>
                    <div class="card-footer">
                        <form id="checkout-payment" class="add-card-form" action="{{ route('checkout') }}" method="POST">
                            {{ csrf_field() }}
                            <div class="cf-alerts"></div>
                            <div class="cf-inside space-y-3 md:space-y-6">
                                <div class="cf-fieldset">
                                    <div id="card_number" class="form-control"></div>
                                    <div id="card_expiry" class="form-control"></div>
                                    <div id="card_cvc" class="form-control"></div>
                                </div>
                            </div>

                            <p id="promoDescription" style="display: none;"></p> <!-- Optional promocode description -->

                            <!-- Discount Code Input -->
                            <div class="discount-container">
                                <input type="text" id="discountCode" class="discount-input" placeholder="Ajouter un code promo">
                                <button class="submit-button" id="checkCode">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                        <path d="M12 2L11.293 2.707 18.586 10H4v2h14.586l-7.293 7.293L12 22l10-10L12 2z" />
                                    </svg>
                                </button>
                            </div>
                            <div class="message" id="responseMessage"></div>

                            <button type="submit" id="nextButton" class="form-submit main-btn">
                                Accède à l'app maintenant (-50%)
                                <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M13.2749 5.28264C13.6646 4.89131 14.2978 4.89 14.6891 5.27971L20.7261 11.2917C20.9145 11.4794 21.0205 11.7343 21.0205 12.0003C21.0205 12.2662 20.9145 12.5212 20.7261 12.7089L14.6891 18.7209C14.2978 19.1106 13.6646 19.1093 13.2749 18.7179C12.8852 18.3266 12.8865 17.6934 13.2778 17.3037L17.6192 12.9803H4.98047C4.42818 12.9803 3.98047 12.5326 3.98047 11.9803C3.98047 11.428 4.42818 10.9803 4.98047 10.9803H17.5791L13.2778 6.69685C12.8865 6.30714 12.8852 5.67398 13.2749 5.28264Z" fill="white" />
                                </svg>
                                <div class="loader" id="loader" style="display: none;"></div>
                            </button>

                            <!-- Klarna Payment Button -->
                            <button type="button" id="klarnaButton" class="form-submit main-btn">
                                <img src="{{ asset('subscriptionDetails/images/klarna-img.png') }}" class="card-img">
                                Payer avec Klarna
                                <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M13.2749 5.28264C13.6646 4.89131 14.2978 4.89 14.6891 5.27971L20.7261 11.2917C20.9145 11.4794 21.0205 11.7343 21.0205 12.0003C21.0205 12.2662 20.9145 12.5212 20.7261 12.7089L14.6891 18.7209C14.2978 19.1106 13.6646 19.1093 13.2749 18.7179C12.8852 18.3266 12.8865 17.6934 13.2778 17.3037L17.6192 12.9803H4.98047C4.42818 12.9803 3.98047 12.5326 3.98047 11.9803C3.98047 11.428 4.42818 10.9803 4.98047 10.9803H17.5791L13.2778 6.69685C12.8865 6.30714 12.8852 5.67398 13.2749 5.28264Z" fill="white" />
                                </svg>
                                <div class="loader" id="loader1" style="display: none;"></div>
                            </button>
                        </form>
                        <div class="refund-info">
                            <div class="plan-info">
                                <p class="normal-cancel-policy">Reveal est un abonnement sans engagement. Tu peux résilier facilement à tout moment en quelques clics dans les paramètres. En validant cet abonnement, tu acceptes que ce montant soit facturé aujourd'hui. Ensuite, le même montant sera automatiquement prélevé à la même date, dans 12 mois ou dans 6 mois ou 1 mois, selon l'offre que tu as choisie. Ce prélèvement se poursuivra selon la fréquence définie dans ton plan, jusqu'à ce que tu résilies dans les paramètres.</p>
                                <!-- Yearly Commitment Summary (hidden by default)  -->
                                <div class="yearly-commitment-info" style="display:none;">
                                    <p><strong>Plan engagé annuel (paiement mensuel)</strong></p>
                                    <p>Montant aujourd'hui : <span class="yc-pay-today">0€</span></p>
                                    <p>Puis <span class="yc-monthly">0€</span> par mois pendant 11 mois</p>
                                    <p>Total pour 12 mois : <span class="yc-total">0€</span></p>
                                    <small class="yc-note">La résiliation n’est autorisée que pendant le dernier mois.</small>
                                </div>
                                <p>Cet abonnement est soumis aux <a href="{{ url('/pages/terms-conditions')}}" class="contact-link">conditions générales</a> et à la <a href="{{ url('/pages/privacy-policy')}}" class="contact-link">politique de confidentialité</a> de Reveal Club.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Order Summery -->
    <div>
        <!-- Owl carousel -->
        <section class="space-pt">
            <div class="container">
                <div class="section-title space-px text-align-center">
                    <h2 class="title">On te guide pas à pas pour être sûr d'obtenir des résultats.</h2>
                    <p class="description">Un programme d'entraînement et de nutrition conçu pour s'adapter à ton mode de vie.</p>
                </div>
                <div class="diet-program" id="diet-program">
                    <div class="owl-carousel owl-theme owl-loaded">
                        <div class="owl-stage-outer">
                            <div class="owl-stage">
                                <div class="owl-item">
                                    <div class="diet-slide">
                                        <img src="{{ asset('paywall/images/slider/04.jpg') }}" alt="">
                                        <div class="diet-content">
                                            <h3 class="diet-quote">Leçons quotidiennes pour des habitudes durables</h3>
                                            <p class="description">Des leçons amusantes de moins de deux minutes sont offertes chaque jour pour apprendre de nouvelles habitudes.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="owl-item">
                                    <div class="diet-slide">
                                        <img src="{{ asset('paywall/images/slider/05.jpg') }}" alt="">
                                        <div class="diet-content">
                                            <h3 class="diet-quote">300 recettes adaptables</h3>
                                            <p class="description">L'application contient plus de 300 recettes simples, toutes personnalisables en fonction des objectifs nutritionnels. Des vidéo sont fournis par Bananamo et The Slow Method pour simplifier les courses.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="owl-item">
                                    <div class="diet-slide">
                                        <img src="{{ asset('paywall/images/slider/06.jpg') }}" alt="">
                                        <div class="diet-content">
                                            <h3 class="diet-quote">Suivi des calories adapté</h3>
                                            <p class="description">L'application propose un suivi alimentaire basé sur le métabolisme de l'utilisateur, calculant les besoins caloriques quotidiens pour atteindre les objectifs sans effet yo-yo.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="owl-item">
                                    <div class="diet-slide">
                                        <img src="{{ asset('paywall/images/slider/07.jpg') }}" alt="">
                                        <div class="diet-content">
                                            <h3 class="diet-quote">Soutient d'une communauté</h3>
                                            <p class="description">Rejoignez RevealClub, où les membres se motivent mutuellement. Le bracelet marque votre engagement et est coupé lorsque vos objectifs sont atteints et son célébrés ensemble.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Owl carousel -->

        <!-- Testimonial -->
        <section class="space-ptb">
            <div class="container">
                <div class="testimonial-wrapper">
                    <div class="testimonial testimonial-01">
                        <img class="testimonial-img" src="{{ asset('paywall/images/testimonial/01.png') }}" alt="">
                        <div class="testimonial-content">
                            <h5 class="name">BananaMo</h5>
                            <p class="message">J'ai perdu 50 kg et créé cette application pour t'aider à éviter mes erreurs et à maintenir ta perte de poids, comme je le fais depuis 10 ans.</p>
                            <div class="social-btn">
                                <a href="#" class="btn">
                                    <img class="btn-icon" src="{{ asset('paywall/images/svgs/instagram.svg') }}" alt="">
                                    <span class="btn-text">278K abonnés</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial testimonial-02">
                        <img class="testimonial-img" src="{{ asset('paywall/images/testimonial/02.png') }}" alt="">
                        <div class="testimonial-content">
                            <h5 class="name">The Slow Method</h5>
                            <p class="message">Vous n'avez pas un problème de poids, vous avez un problème de gras! Avec mes 15 ans d'expérience j'ai créé des programmes de sport progressifs et adaptés à tous!</p>
                            <div class="social-btn">
                                <a href="#" class="btn">
                                    <img class="btn-icon" src="{{ asset('paywall/images/svgs/instagram.svg') }}" alt="">
                                    <span class="btn-text">52,6K abonnés</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Testimonial -->

        <!-- Plans -->
        <section class="space-pb">
            <div class="container">
                <div class="section-title space-px">
                    <h2 class="title text-align-center">Votre chemin vers vos objectifs corporels</h2>
                </div>
                <div class="body-goal-wrapper">
                    <div class="body-goal goal-coaching">
                        <div class="goal-main-title" style="background-image: url('paywall/images/bg/01.png');">COACHING</div>
                        <div class="goal-duration-wrapper">
                            <div class="goal-duration week">
                                <h4 class="goal-title">Après 1 semaine</h4>
                                <p class="description">Mon coach répond à mes besoins et à mes questions.</p>
                            </div>
                            <div class="goal-duration">
                                <h4 class="goal-title">Après 1 mois</h4>
                                <p class="description">Mon coach personnalise mon programme et me motive.</p>
                            </div>
                        </div>
                    </div>
                    <div class="body-goal goal-program">
                        <div class="goal-main-title" style="background-image: url('paywall/images/bg/02.png');">PROGRAMME</div>
                        <div class="goal-duration-wrapper">
                            <div class="goal-duration week">
                                <h4 class="goal-title">Après 1 semaine</h4>
                                <p class="description">J'apporte des changements simples à mes habitudes.</p>
                            </div>
                            <div class="goal-duration">
                                <h4 class="goal-title">Après 1 mois</h4>
                                <p class="description">Je mange ce que j'aime avec de petits changements et je vois déjà les résultats.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Plans -->

        <!-- Personalized Program -->
        <section>
            <div class="container">
                <div class="section-title space-px">
                    <p class="subtitle text-align-center">Ton programme personnalisé de perte de graisse est prêt !</p>
                    <h3 class="title text-align-center">Suis ton programme sur mesure pour atteindre <span>{{ $desiredWeight }}</span> kg
                    </h3>
                </div>
                <p class="price-title text-align-center">Sélectionne ton plan pour commencer :</p>
                <div class="price-box-wrapper">
                    <div id="accordion" class="accordion-container">
                        @foreach($getPackages as $package)
                        <?php
                        $perMonthOnPrice = round(($package['price'] / 12), 2);
                        $perMonthOnDiscPrice = round(($package['discprice'] / 12), 2);

                        $priceDifference = ((float) $package['total_price'] - (float) $package['total_disc_price']);
                        $dicsPercent = (int)(($priceDifference * 100) / (float) $package['total_price']);
                        ?>
                        <div class="price-box" data-package-id="{{ $package['id'] }}" data-for-klarna="{{ $package['for_klarna'] }}" data-yearly-commitment="{{ $package['is_yearly_commitment'] }}">
                            <div class="accordion-title js-accordion-title">
                                @if(!empty($package['offer_label']))
                                <span class="discount">{{ $package['offer_label'] }}</span>
                                @endif
                                <div class="plan-select-icon">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <div class="price-left">
                                    <h4 class="plan-duration">{{ $package['name'] }}</h4>
                                    <div class="plan-discount">
                                        (-{{ $dicsPercent }}%)
                                        <p class="before-price">{{ $package['total_price'] }} €</p>
                                        →
                                        <p class="discounted-price">{{ $package['total_disc_price'] }} €</p>
                                    </div>
                                </div>
                                @if($package['for_klarna'] == 1)
                                <img src="{{ asset('subscriptionDetails/images/klarna-img.png') }}" class="card-img" style="max-width: 50px;">
                                @endif
                                <div class="price-right">
                                    <h4 class="main-amount">{{ $package['discprice'] }}</h4>
                                    <div class="per-month-amount">
                                        <p class="before-price">{{ $package['price'] }}</p>
                                        <p class="per-month">€/mois</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-content">
                                <p class="feature">{!! $package['description'] !!} </p>
                                {{-- NEW: Yearly-commitment vertical info (only visible for is_yearly_commitment == 1) --}}
                                @if($package['is_yearly_commitment'] == 1)
                                <div class="yearly-commitment-note" style="margin-top:12px; padding:10px; border-left:4px solid #2b6cb0; background:#f7fbfe;">
                                    <strong>Engagement 12 mois — Paiement mensuel</strong>
                                    <p class="small">Vous payez <span class="yc-monthly">{{ $package['discprice'] }}€</span> maintenant, puis <span class="yc-monthly">{{ $package['discprice'] }}€</span> chaque mois pour 11 mois supplémentaires. Total : <strong class="yc-total">{{ $package['total_disc_price'] }}€</strong>.</p>
                                    <p class="small">La résiliation n’est disponible que pendant le dernier mois de l’année d’abonnement.</p>
                                </div>
                                @endif

                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
        <!-- Personalized Program -->

        <!-- FAQ -->
        <section class="mt-30">
            <div class="container">
                <div class="faq">
                    <div class="section-title text-align-center">
                        <h2 class="title">Les questions fréquentes</h2>
                    </div>
                    <ul class="faq-list">
                        <li class="faq-item">
                            <div class="faq-icon">
                                <img src="{{ asset('paywall/images/svgs/faq.svg') }}" alt="">
                            </div>
                            <div class="faq-content">
                                <h6 class="faq-question">Je suis un débutant. Est-ce fait pour moi ?</h6>
                                <p class="faq-answer">Bien-sûr que oui ! Notre application est conçue pour tous les niveaux. Nous proposons des entraînements guidés qui commencent doucement et augmentent progressivement. Tu auras tout le soutien et les instructions nécessaires pour te sentir confiant et voir des progrès.</p>
                            </div>
                        </li>
                        <li class="faq-item">
                            <div class="faq-icon">
                                <img src="{{ asset('paywall/images/svgs/faq.svg') }}" alt="">
                            </div>
                            <div class="faq-content">
                                <h6 class="faq-question">Faut-il suivre un régime strict ?</h6>
                                <p class="faq-answer">Non, pas du tout ! On privilégie la nutrition équilibrée, sans aucune contrainte. Nos menus sont flexibles et diversifiés, pour que tu puisses te régaler avec des aliments sains qui correspondent à ton mode de vie.</p>
                            </div>
                        </li>
                        <li class="faq-item">
                            <div class="faq-icon">
                                <img src="{{ asset('paywall/images/svgs/faq.svg') }}" alt="">
                            </div>
                            <div class="faq-content">
                                <h6 class="faq-question">Que faire si je ne vois pas de résultats rapidement ?</h6>
                                <p class="faq-answer">Les résultats demandent du temps, mais notre programme est conçu pour t'assurer des progrès constants. On se concentre sur des changements durables, pour que tu te sentes bien, sois actif et restes motivé sur le long terme. Patience et persévérance, on est là pour t'accompagner à chaque pas !</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- FAQ -->
        <div class="fixed-main-btn">
            <input type="hidden" id="selected-package-id">
            <a href="#order-summery" class="main-btn">→ Profite de l'app dès maintenant</a>
            <div class="places-count">
                <div class="remaining-places">
                    <span>56</span>
                    places restantes
                </div>
                <div class="sold-places">
                    <span>123</span>
                    vendu hier
                </div>
            </div>
        </div>
    </div>
    <div id="stripeKey" data-value="{{ config('services.stripe.key') }}"></div>
    @section('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            showMethod: 'slideDown',
            timeOut: 40000
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
        const endTime = new Date().getTime() + (15 * 60 * 1000); // 15 minutes from now
        let timerInterval;

        function updateTimer() {
            const currentTime = new Date().getTime();
            const timeLeft = Math.max(0, Math.floor((endTime - currentTime) / 1000));

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                document.getElementById("mins").innerText = "00";
                document.getElementById("secs").innerText = "00";
                return;
            }

            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;

            document.getElementById("mins").innerText = minutes.toString().padStart(2, '0');
            document.getElementById("secs").innerText = seconds.toString().padStart(2, '0');
        }

        // Initial display
        updateTimer();

        // Clear any existing intervals
        if (timerInterval) {
            clearInterval(timerInterval);
        }

        // Update based on actual timestamp difference
        timerInterval = setInterval(updateTimer, 1000);
    </script>

    <!-- Order Summery, Stripe Script and Promocode Script-->
    <script>
        $(document).ready(function() {

            function updateOrderSummary(selectedPackageId) {
                $.ajax({
                    url: "/package/details/" + selectedPackageId, // Adjust this route as necessary
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            const pkg = response.package;

                            // Base updates
                            $('.plan-type').text(pkg.name);
                            $('.amount').text((pkg.total_price ?? '') + "€");
                            $('.klarna-charge').text((pkg.klarnaCommision ?? 0) + "€");
                            $('.discount-percentage').text(pkg.discount_percentage ?? '');
                            $('.discount-amount b').text("-" + (pkg.discount_amount ?? 0) + "€");
                            $('.saved-amount').text(pkg.message ?? '');

                            // Promo / total
                            $('.total-amount').text((pkg.final_price ?? pkg.total_disc_price ?? pkg.total_price ?? 0) + "€");
                            $('.promocode-id').text(pkg.promocodeId ?? '');

                            // Yearly commitment specific
                            if (pkg.is_yearly_commitment == 1) {
                                $('.yearly-commitment-info').show();
                                $('.normal-cancel-policy').hide();

                                // pay_today = the per-month amount (first month)
                                $('.yc-pay-today').text((pkg.monthly_price ?? pkg.discprice) + "€");
                                $('.yc-monthly').text((pkg.monthly_price ?? pkg.discprice) + "€");
                                $('.yc-total').text((pkg.total_disc_price ?? pkg.total_price) + "€");

                                // Update the main total to the immediate charge (pay today)
                                $('.total-amount').text((pkg.monthly_price ?? pkg.discprice) + "€");
                            } else {
                                $('.yearly-commitment-info').hide();
                                $('.normal-cancel-policy').show();

                                // For normal plans use final_price or total due today
                                $('.total-amount').text((pkg.final_price ?? pkg.total_price ?? 0) + "€");
                            }
                        } else {
                            alert('Failed to fetch package details. Please try again.');
                        }
                    },
                    error: function(xhr) {
                        alert('Error fetching package details. Please try again.');
                        console.error('Error fetching package details:', xhr);
                    }
                });
            }

            // Function to update UI based on selected package
            function updateUIForPackage(selectedPackageId) {
                const selectedPackage = $(`[data-package-id="${selectedPackageId}"]`);
                const forKlarna = selectedPackage.data('for-klarna');
                if (forKlarna === 1) {
                    $('#klarnaButton, .klarna-fee, .klarna_img').show();
                    $('#card_number, #card_expiry, #card_cvc, .discount-container, #nextButton, .non_klarna_img').hide();
                } else {
                    $('#klarnaButton, .klarna-fee, .klarna_img').hide();
                    $('#card_number, #card_expiry, #card_cvc, .discount-container, #nextButton, .non_klarna_img').show();
                }
            }

            const stripeKey = $('#stripeKey').data('value');
            const stripe = Stripe(stripeKey);
            const elements = stripe.elements();
            const style = {
                base: {
                    color: "#32325D",
                    fontWeight: "500",
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSize: "16px",
                    letterSpacing: "0.025em",
                    lineHeight: "1.5",
                    padding: "10px",
                    border: "1px solid #ccc",
                    borderRadius: "4px",
                    '::placeholder': {
                        color: "#CFD7DF",
                    },
                },
                invalid: {
                    color: "#E25950",
                    iconColor: "#E25950",
                },
            };
            const cardElement = elements.create('cardNumber');
            const expElement = elements.create('cardExpiry');
            const cvcElement = elements.create('cardCvc');
            cardElement.mount('#card_number');
            expElement.mount('#card_expiry');
            cvcElement.mount('#card_cvc');

            $('.price-box').on('click', function() {
                document.getElementById('discountCode').value = '';
                $('#responseMessage').hide();
                $('.promocode-applied').hide();
                $('.price-box').removeClass('active');
                $(this).addClass('active');
                var selectedPackageId = $(this).data('package-id');
                $('#selected-package-id').val(selectedPackageId);

                updateOrderSummary(selectedPackageId);
                updateUIForPackage(selectedPackageId);
            });

            var firstPackageId = $('.price-box').first().data('package-id');
            if (firstPackageId) {
                $('#selected-package-id').val(firstPackageId);
                updateOrderSummary(firstPackageId);
                updateUIForPackage(firstPackageId);
            }

            if (firstPackageId) {
                $.ajax({
                    url: "/payment/" + firstPackageId,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            console.log('Package stored in session successfully.');
                        } else {
                            alert('Failed to store package in session. Please try again.');
                        }
                    },
                    error: function(xhr) {
                        alert('Error storing package in session. Please try again.');
                        console.error('Error storing package in session:', xhr);
                    }
                });
            } else {
                alert('Please select a package before continuing.');
            }

            $('#checkout-payment').on('submit', async (event) => {
                event.preventDefault();

                var selectedPackageId = $('#selected-package-id').val();
                var selectedPaymentMethod = $('input[name="payment_method"]').val(); // e.g., "klarna" or "stripe"

                $('#nextButton, #klarnaButton').prop('disabled', true);
                $('#loader, #loader1').show();

                if (!selectedPackageId) {
                    alert('Please select a package before continuing.');
                    $('#nextButton, #klarnaButton').prop('disabled', false);
                    $('#loader, #loader1').hide();
                    return;
                }

                try {
                    const response = await $.ajax({
                        url: "/payment/" + selectedPackageId,
                        type: "GET"
                    });

                    if (!response.success) {
                        alert('Failed to store package in session. Please try again.');
                        $('#nextButton, #klarnaButton').prop('disabled', false);
                        $('#loader, #loader1').hide();
                        return;
                    }
                } catch (xhr) {
                    alert('Error storing package in session. Please try again.');
                    console.error('Error storing package in session:', xhr);
                    $('#nextButton, #klarnaButton').prop('disabled', false);
                    $('#loader, #loader1').hide();
                    return;
                }

                // Common hidden inputs (both Klarna and Stripe)
                var promocodeDiscount = $('.promocode-discount').text().replace('€', '').trim();
                var finalPrice = $('.total-amount').text().replace('€', '').trim();
                var promoCodeId = $('.promocode-id').text();

                $('<input>').attr({
                    type: 'hidden',
                    name: 'planId',
                    value: selectedPackageId
                }).appendTo('#checkout-payment');

                $('<input>').attr({
                    type: 'hidden',
                    name: 'promoCodeId',
                    value: promoCodeId
                }).appendTo('#checkout-payment');

                $('<input>').attr({
                    type: 'hidden',
                    name: 'promocode_discount',
                    value: promocodeDiscount
                }).appendTo('#checkout-payment');

                $('<input>').attr({
                    type: 'hidden',
                    name: 'final_price',
                    value: finalPrice
                }).appendTo('#checkout-payment');

                $('<input>').attr({
                    type: 'hidden',
                    name: 'payment_method',
                    value: selectedPaymentMethod
                }).appendTo('#checkout-payment');

                if (selectedPaymentMethod === 'klarna') {
                    $('#checkout-payment')[0].submit();
                    return;
                }

                // Stripe card payment
                const { token, error } = await stripe.createToken(cardElement);

                if (error) {
                    // Stripe error code translation
                    let translatedMessage = '';
                    switch (error.code) {
                        case 'incomplete_number':
                            translatedMessage = "Le numéro de votre carte est incomplet.";
                            break;
                        case 'incomplete_expiry':
                            translatedMessage = "La date d’expiration de votre carte est incomplète.";
                            break;
                        case 'incomplete_cvc':
                            translatedMessage = "Le code CVC de votre carte est incomplet.";
                            break;
                        case 'incomplete_zip':
                            translatedMessage = "Le code postal de votre carte est incomplet.";
                            break;
                        case 'invalid_number':
                            translatedMessage = "Le numéro de carte est invalide.";
                            break;
                        case 'invalid_expiry_year_past':
                            translatedMessage = "L’année d’expiration de votre carte est passée";
                            break;
                        case 'invalid_expiry_month_past':
                            translatedMessage = "Le mois d’expiration est invalide.";
                            break;
                        case 'invalid_expiry_month':
                            translatedMessage = "Le mois d’expiration est invalide.";
                            break;
                        case 'invalid_expiry_year':
                            translatedMessage = "L’année d’expiration est invalide.";
                            break;
                        case 'invalid_cvc':
                            translatedMessage = "Le code CVC est incorrect.";
                            break;
                        case 'postal_code_invalid':
                            translatedMessage = "Le code postal fourni est incorrect.";
                            break;
                        case 'card_declined':
                            translatedMessage = "La carte a été refusée.";
                            break;
                        case 'expired_card':
                            translatedMessage = "La carte a expiré.";
                            break;
                        case 'incorrect_address':
                            translatedMessage = "L’adresse saisie est incorrecte.";
                            break;
                        case 'incorrect_cvc':
                            translatedMessage = "Le code CVC est incorrect.";
                            break;
                        case 'incorrect_number':
                            translatedMessage = "Le numéro de carte est incorrect.";
                            break;
                        case 'incorrect_zip':
                            translatedMessage = "Le code postal est incorrect.";
                            break;
                        case 'insufficient_funds':
                            translatedMessage = "Le compte n’a pas suffisamment de fonds.";
                            break;
                        case 'card_decline_rate_limit_exceeded':
                            translatedMessage = "Cette carte a été refusée trop de fois.";
                            break;
                        case 'processing_error':
                            translatedMessage = "Une erreur est survenue lors du traitement de la carte.";
                            break;
                        case 'authentication_required':
                            translatedMessage = "La carte a été refusée car la transaction nécessite une authentification telle que 3D Secure.";
                            break;
                        case 'payment_intent_authentication_failure':
                            translatedMessage = "La méthode de paiement a échoué l’authentification.";
                            break;
                        case 'payment_intent_payment_attempt_failed':
                            translatedMessage = "La dernière tentative de paiement a échoué.";
                            break;
                        case 'payment_intent_unexpected_state':
                            translatedMessage = "L’état du PaymentIntent est incompatible avec l’opération.";
                            break;
                        case 'payment_intent_incompatible_payment_method':
                            translatedMessage = "Le PaymentIntent attend une méthode de paiement avec des propriétés différentes.";
                            break;
                        case 'payment_intent_action_required':
                            translatedMessage = "La méthode de paiement nécessite une action de la part du client.";
                            break;
                        case 'payment_intent_amount_reconfirmation_required':
                            translatedMessage = "Le montant total du PaymentIntent a été modifié, une reconfirmation est nécessaire.";
                            break;
                        case 'account_closed':
                            translatedMessage = "Le compte bancaire du client a été fermé.";
                            break;
                        case 'balance_insufficient':
                            translatedMessage = "Le solde du compte est insuffisant pour effectuer la transaction.";
                            break;
                        case 'bank_account_unusable':
                            translatedMessage = "Le compte bancaire fourni ne peut pas être utilisé.";
                            break;
                        case 'bank_account_declined':
                            translatedMessage = "Le compte bancaire fourni ne peut pas être débité.";
                            break;
                        case 'bank_account_exists':
                            translatedMessage = "Le compte bancaire fourni existe déjà pour ce client.";
                            break;
                        case 'no_account':
                            translatedMessage = "Le compte bancaire n’a pas pu être localisé.";
                            break;
                        case 'debit_not_authorized':
                            translatedMessage = "Le client a signalé à sa banque que cette transaction était non autorisée.";
                            break;
                        case 'amount_too_large':
                            translatedMessage = "Le montant spécifié est supérieur au montant maximal autorisé.";
                            break;
                        case 'amount_too_small':
                            translatedMessage = "Le montant spécifié est inférieur au montant minimal autorisé.";
                            break;
                        case 'invalid_charge_amount':
                            translatedMessage = "Le montant spécifié est invalide.";
                            break;
                        case 'token_already_used':
                            translatedMessage = "Le jeton fourni a déjà été utilisé.";
                            break;
                        case 'token_in_use':
                            translatedMessage = "Le jeton fourni est actuellement utilisé dans une autre demande.";
                            break;
                        case 'refer_to_customer':
                            translatedMessage = "Le client a arrêté le paiement avec sa banque.";
                            break;
                        case 'payment_method_provider_decline':
                            translatedMessage = "La tentative de paiement a été refusée par l’émetteur ou le client.";
                            break;
                        case 'payment_method_currency_mismatch':
                            translatedMessage = "La devise spécifiée ne correspond pas à la devise de la méthode de paiement attachée.";
                            break;
                        default:
                            translatedMessage = error.message || 'Une erreur inattendue est survenue. Veuillez réessayer.';
                    }
                    $('.cf-alerts').html(`<div class="alert alert-danger"><p>${translatedMessage}</p></div>`);
                    $('#nextButton, #klarnaButton').prop('disabled', false);
                    $('#loader, #loader1').hide();
                } else {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'stripeToken',
                        value: token.id
                    }).appendTo('#checkout-payment');

                    $('#checkout-payment')[0].submit();
                }
            });

            // promocode Script
            $('#checkCode').click(function(e) {
                e.preventDefault();
                var code = $('#discountCode').val();

                var selectedPackageId = $('#selected-package-id').val();

                if (code === "") {
                    $('#responseMessage').text("Please enter a discount code.");
                    return;
                }

                // AJAX call to validate the code
                $.ajax({
                    url: '/check-discount-code/' + (selectedPackageId ? selectedPackageId : ''), // Your backend endpoint
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        code: code
                    },
                    success: function(response) {
                        console.log(response);
                        if (response.isValid) {
                            // Update the DOM with the promocode details if valid
                            $('.promocode-discount').text("-" + response.promocode.promocode_discount + "€");
                            $('.total-amount').text(response.promocode.final_price + "€");
                            $('.saved-amount').text(response.promocode.message);
                            $('.promocode-id').text(response.promocode.promocodeId);
                            $('#responseMessage').text("Le code de réduction est appliqué!").css('color', 'green');

                            // Show the promocode discount div
                            $('.promocode-applied').show(); // Ensure the promocode div is visible

                            // Optionally, display more promocode-related data
                            if (response.promocode.description) {
                                $('#promoDescription').text(response.promocode.description).show();
                            }
                        } else {
                            $('#responseMessage').text("Code de réduction invalide.").css('color', 'red');
                            // Hide the promocode discount div if the code is invalid
                            $('.total.promocode-applied').hide();
                        }
                    },
                    error: function() {
                        $('#responseMessage').text("Erreur lors de la vérification du code de réduction.").css('color', 'red');
                        // Hide the promocode discount div in case of an error
                        $('.total.promocode-applied').hide();
                    }
                });
            });
            // End Promocode Script
        });
    </script>
    <!-- End Order Summery, Stripe Script and Promocode Script-->

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            if (window.ApplePaySession && ApplePaySession.canMakePayments()) {
                const applePayButton = document.getElementById("apple-pay-button");
                applePayButton.style.display = "block";
            }
        });

        function onApplePayButtonClicked() {
            const paymentRequest = {
                countryCode: 'US',
                currencyCode: 'EUR',
                supportedNetworks: ['visa', 'masterCard', 'amex'],
                merchantCapabilities: ['supports3DS'],
                total: {
                    label: 'Your Company',
                    amount: '10.00'
                }
            };

            const session = new ApplePaySession(1, paymentRequest);

            session.onvalidatemerchant = function(event) {
                fetch('/apple-pay/validate-merchant', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            validationUrl: event.validationURL
                        })
                    })
                    .then(response => response.json())
                    .then(data => session.completeMerchantValidation(data));
            };

            session.onpaymentauthorized = function(event) {
                fetch('/apple-pay/process-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            paymentData: event.payment.token.paymentData
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            session.completePayment(ApplePaySession.STATUS_SUCCESS);
                        } else {
                            session.completePayment(ApplePaySession.STATUS_FAILURE);
                        }
                    });
            };
            session.begin();
        }
        $('#klarnaButton').on('click', function () {
            $('<input>').attr({
                type: 'hidden',
                name: 'payment_method',
                value: 'klarna'
            }).appendTo('#checkout-payment');

            $('#checkout-payment').submit();
        });

    </script>
    <script>window.amplitude.init('58b25352c5f050c3039f226c757dc9ba', {"fetchRemoteConfig":true,"autocapture":true});</script>
    <script>
        amplitude.track('TPaywall');
    </script>
    @endsection
</x-paywall-layout>
