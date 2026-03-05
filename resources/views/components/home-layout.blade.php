<!DOCTYPE html>
<html lang="en">

<head>
    {!! renderCachedView('partials.user.head', ['title' => $attributes['title']]) !!}
    @yield('styles')
</head>

<body>
    {!! renderCachedView('partials.user.header') !!}

    {{ $slot }}

    {!! renderCachedView('partials.user.footer') !!}
    {!! renderCachedView('partials.user.foot') !!}
    @yield('scripts')
</body>

</html>