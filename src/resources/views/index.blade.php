<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="/favicon.ico"/>
        <link rel="icon" type="image/png" sizes="138x136" href="/images/favicon.png">
        <link id="theme" rel="stylesheet" type="text/css" href="/themes/clean/bulma.min.css">
        <link href="{{ mix('css/enso.css') }}" rel="stylesheet" type="text/css"/>
    </head>
    <body>

        <div id="app"></div>

        @include('laravel-enso/core::polyfills')

        <script src="{{ mix('js/enso.js') }}"></script>

    </body>
</html>
