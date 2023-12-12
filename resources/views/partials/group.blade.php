<div class="group-card">
    <a href="{{ Auth::guard('webadmin')->check() ? route('view-group-admin', ['id' => $group->id]) : route('group', ['id' => $group->id]) }}">
         @if($group->banner)
        <img src="{{stream_get_contents($group->banner)}}"/>
        @else
        <img src="{{url('assets/group.png')}}" alt="Background Picture">
        @endif

        <span class="card-info">
            {{ $group->name }}
            <span class="username">{{$group->description}}</span>
        </span>
    </a>

</div>