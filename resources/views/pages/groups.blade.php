@extends(Auth::guard('webadmin')->check() ? 'layouts.appLoggedAdmin' : 'layouts.appLogged')

@section('title', 'Groups')

@section('content')

<section id="groups">
    @if (session('success'))
    <p class="success">
        {{ session('success') }}
    </p>
    @endif
    @if (session('error'))
        <p class="error">
            {{ session('error') }}
        </p>
    @endif
    @if(Auth::user())
    <button><a href="{{route('create_group_form')}}"><span class='material-symbols-outlined'>add_circle</span> group</a></button>
    @endif
    <div class="users">
        @each('partials.group', $groups, 'group')
    </div>
    </div> 
</section>
    {{$groups->links()}} <!-- use Laravel's default pagination mode. -->

@endsection
