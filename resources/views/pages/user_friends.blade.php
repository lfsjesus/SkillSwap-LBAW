@extends(Auth::guard('webadmin')->check() ? 'layouts.appLoggedAdmin' : 'layouts.appLogged')

@section('title', 'User')

@section('content')

<div class="friends">
    @each('partials.user', $user->get_friends(), 'user')
</div>
@endsection


