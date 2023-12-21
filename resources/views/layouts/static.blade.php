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
        <link href="{{ url('css/static.css') }}" rel="stylesheet">
        <script type="text/javascript">
            // Fix for Firefox autofocus CSS bug
            // See: http://stackoverflow.com/questions/18943276/html-5-autofocus-messes-up-css-loading/18945951#18945951
        </script>
        <script type="text/javascript" src={{ url('js/app.js') }} defer>
        </script>

        <!-- Open Graph tags -->
        <meta property="og:title" content="SkillSwap" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://www.skillswap.com/about-us" />
        <meta property="og:image" content="https://www.skillswap.com/assets/about-us-og-image.jpg" />
        <meta property="og:description" content="Connect, inspire, and grow together with experts and enthusiasts from various fields." />
        <meta property="og:site_name" content="SkillSwap" />
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
            </header>
            <section id="content">
                @yield('content')
            </section>
        </main>
        @include('partials.footer')
    </body>
</html>