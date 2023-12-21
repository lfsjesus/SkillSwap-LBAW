<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Styles -->
        <link href="{{ url('css/milligram.min.css') }}" rel="stylesheet">
        <link href="{{ url('css/auth.css') }}" rel="stylesheet">
        <link href="{{ url('css/auth-responsive.css') }}" rel="stylesheet">
        <script type="text/javascript">
            // Fix for Firefox autofocus CSS bug
            // See: http://stackoverflow.com/questions/18943276/html-5-autofocus-messes-up-css-loading/18945951#18945951
        </script>
        <script type="text/javascript" src={{ url('js/app.js') }} defer>
        </script>
    </head>
    
    <body>
        <main>
            <header>
                <div class="logo">
                    <!-- image is one public/assets/skillswap_white_grey.svg -->
                    <a href="{{ url('/home') }}">
                            <img src="{{ url('assets/skillswap_white_grey.png') }}" alt="logo">
                    </a>
                </div>
                @if (Auth::check())
                    <a class="button" href="{{ url('/logout') }}"> Logout </a> <span>{{ Auth::user()->name }}</span>
                @endif

                @if (Auth::guard('webadmin')->check())
                    <a class="button" href="{{ url('/admin/logout') }}"> Logout </a>
                @endif
            </header>
            <section id="content">
                @yield('content')
            </section>
        </main>
        @include('partials.footer')
    </body>
</html>