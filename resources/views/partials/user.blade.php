<div class="user-card">
    @if(Auth::guard('webadmin')->check())
    <a href="{{ route('view-user-admin', ['username' => $user->username]) }}">
    @else
    <a href="{{ route('user', ['username' => $user->username]) }}">
    @endif
        @if($user->profile_picture)
        <img src="{{stream_get_contents($user->profile_picture)}}"/>
        @else
        <img src="{{ url('assets/profile-picture.png') }}"/>
        @endif

        <span class="card-info">
            {{ $user->name }}
            <span class="username">&#64;{{$user->username}}</span>
        </span>
    </a>


    @if(isset($group) && $group->is_owner(Auth::user()))
        <div class="remove-icon">
            <input type="hidden" name="group_id" value="{{ $group->id }}">
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <span class="material-symbols-outlined">
                delete
            </span>
        </div>
    @endif

</div>