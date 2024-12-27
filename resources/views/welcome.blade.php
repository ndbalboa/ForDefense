<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Records Office</title>
        <link href="{{ asset('assets/js/style.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('admin/css/styles.css') }}" rel="stylesheet" />
        <script src="{{ asset('assets/js/all.js') }}" crossorigin="anonymous"></script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        @vite('resources/css/app.css')
    </head>
    <body>
        <div id="app">
        </div>

        @vite('resources/js/app.js')
        <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}" crossorigin="anonymous"></script>
        <script src="{{ asset('admin/js/scripts.js') }}"></script>
        <script src="{{ asset('assets/js/simple-datatables.min.js')}}" crossorigin="anonymous"></script>
        <script src="{{ asset('admin/js/datatables-simple-demo.js') }}"></script>
    </body>
</html>
