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
        <link href="{{ url('css/app.css') }}" rel="stylesheet">
        <link href="{{ url('css/responsive.css') }}" rel="stylesheet">
        <script type="text/javascript">
            // Fix for Firefox autofocus CSS bug
            // See: http://stackoverflow.com/questions/18943276/html-5-autofocus-messes-up-css-loading/18945951#18945951
        </script>
        <script type="text/javascript" src={{ url('js/app.js') }} defer>
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    
    <body>
        <main>
            <aside id="left-bar">
                <button class="close-btn left" onclick="toggleMenu('left')">×</button>
                <div class="upper-bar">
                    <div class="logo">
                        <!-- image is one public/assets/skillswap_white_grey.svg -->
                        <a href="{{ url('/admin') }}">
                                <img src="{{ url('assets/skillswap.png') }}" alt="logo">
                        </a>
                    </div>
                    <nav>
                    <ul>
                        <li>
                            <a href="{{ route('admin')}}" > 
                                <span class="material-symbols-outlined">
                                    account_circle
                                    </span>Users
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin-groups') }}">
                                <span class="material-symbols-outlined">
                                groups
                                </span>Groups
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropbtn">
                                <span class="material-symbols-outlined">
                                expand_more
                                </span>See More
                            </a>
                            <div class="dropdown-content">
                                <a href="{{ route('mainFeatures') }}">Main Features</a>
                                <a href="{{ route('about') }}">About Us</a>
                                <a href="{{ route('about') }}">Contact Us</a>
                            </div>
                        </li>
                    </ul>
                    </nav>
                </div>
                @if (Auth::guard('webadmin')->check())
                    <a class="button" href="{{ url('admin/logout') }}"> Logout </a>
                @endif
            </aside>

            <section id="content">
                <section class="responsive-buttons">
                    <span class="material-symbols-outlined hamburger-menu left-hamburger" onclick="toggleMenu('left')">
                        menu
                    </span>
                    <span class="material-symbols-outlined hamburger-menu right-hamburger" onclick="toggleMenu('right')">
                        mark_chat_unread
                    </span>
                </section>
                <div class="search">
                    <form action="{{ route('search') }}" method="GET">
                        <span class="material-symbols-outlined">
                            search
                        </span>
                        <input type="text" name="q" placeholder='Search... (e.g., "[term]" for exact match or [term] for full-text search)' value="{{ $query ?? '' }}" autofocus>
                        <input type="hidden" name="type" value="{{ $type ?? 'user' }}">
                        <input type="hidden" name="date" value="{{ $date ?? 'asc' }}">
                        <input type="hidden" name="popularity" value="{{ $popularity ?? 'asc' }}">
                        <input type="submit" value="Search" style="display: none;">
                    </form>
                </div>
                @yield('content')
            </section>
            
            <aside id="right-bar">
                <button class="close-btn right" onclick="toggleMenu('right')">×</button>
                <ul>
                    <li><span class="material-symbols-outlined">
                        expand_more
                        </span>Notifications</li>
                </ul>
                <button class="button">Help</button>
            </aside>
        </main>
    </body>
</html>