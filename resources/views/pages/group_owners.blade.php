@extends(Auth::guard('webadmin')->check() ? 'layouts.appLoggedAdmin' : 'layouts.appLogged')

@section('title', 'User')

@section('content')
@if($errors->has('add_owner'))
    <div class="error">
        {{ $errors->first('add_owner') }}
    </div>
@endif

@if(session('success'))
    <div class="success">
        {{ session('success') }}
    </div>
@endif
<div class="users">
    @if(Auth::user() && $group->isOwner(Auth::user()))
    <form action="{{ route('add_owner') }}" class="add-user-group user-card" method="POST">
        <p> Add a new owner </p>
        {{ csrf_field() }}
        <input type="hidden" name="group_id" value="{{ $group->id }}">
        <div class="user-add-input">
            <input type="text" name="user" placeholder="Username or email">
            <input type="submit" class="material-symbols-outlined" value="add_circle_outline">
        </div>
    </form>
    @endif
    @foreach ($group->get_owners() as $user)
        @include('partials.user', ['user' => $user, 'group' => $group, 'owners' => true])
    @endforeach
</div>
@endsection



