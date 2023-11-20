<div class="user-info">
    <h3>{{ $user->name }}</h3>
    <p>{{ $user->email }}</p>
</div>

<div class="user-card">
    <a href="{{ route('user', ['username' => $user->username]) }}">
        @if($user->profile_picture)
        <img src="{{stream_get_contents($user->profile_picture)}}"/>
        @else
        <img src="{{ url('assets/profile-picture.png') }}"/>
        @endif
        {{ $user->name }} | {{$user->username}}
    </a>

</div>