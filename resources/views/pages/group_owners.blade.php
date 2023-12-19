@extends(Auth::guard('webadmin')->check() ? 'layouts.appLoggedAdmin' : 'layouts.appLogged')

@section('title', 'User')

@section('content')

<div class="users">
    @each('partials.user', $group->get_owners(), 'user')
</div>
@endsection



