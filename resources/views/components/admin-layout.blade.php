<!DOCTYPE html>
<html lang="en">
    <head>
        @include('partials.admin.head', ['title' => $attributes['title']])
    </head>
    <body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
        @include('partials.admin.header')
        @include('partials.admin.sidebar')
        <main id="main" class="main">
            {{ $slot }}
        </main>
        @include('partials.admin.footer')
        @include('partials.admin.foot')
    </body>
</html>
