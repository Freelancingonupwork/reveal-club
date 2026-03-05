<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>

<script src="{{ asset('paywall/js/fontawesome/all.min.js') }}"></script>

<script src="{{ asset('paywall/js/owl/owl.carousel.min.js') }}"></script>

<script src="{{ asset('paywall/js/script.js') }}"></script>
<script src="{{ asset('webAssets/js/amplitude.js') }}"></script>
{{-- <script>
    window.amplitude.init('58b25352c5f050c3039f226c757dc9ba', {
            "fetchRemoteConfig": true,
            "autocapture": true
        }, function() {
            // This callback ensures amplitude is initialized before calling logEvent
            amplitude.getInstance().logEvent("After Paywall Viewed");
        });
</script> --}}
@yield('scripts')
