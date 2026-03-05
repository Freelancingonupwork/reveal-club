@props([
    'callback' => 'onTurnstileSuccess',
    'errorCallback' => 'onTurnstileError',
    'theme' => 'auto',
    'language' => 'en-US',
    'size' => 'normal',
])

<div {{ $attributes->merge([
    'class' => 'cf-turnstile',
    'data-sitekey' => config('turnstile.turnstile_site_key'),
    'data-callback' => $callback,
    'data-error-callback' => $errorCallback,
    'data-theme' => $theme,
    'data-language' => $language,
    'data-size' => $size,
]) }}></div>
<input type="hidden" name="cf-turnstile-response" id="cf-turnstile-response" />

<script>
    window.onTurnstileSuccess = function (token) {
        console.log("Turnstile success: ", token);
        const input = document.getElementById('cf-turnstile-response');
        if (input) {
            input.value = token;
        }
    };

    window.onTurnstileError = function () {
        console.error("Turnstile failed to verify.");
    };
</script>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>