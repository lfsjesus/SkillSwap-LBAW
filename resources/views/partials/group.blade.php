<div class="group-card">
    <a href="{{ route('groups') }}">

        @if($group->banner)
        <img src="{{stream_get_contents($group->banner)}}"/>
        @else
        <img src="{{url('assets/blob-background.jpg')}}" alt="Background Picture">
        @endif

        <span class="card-info">
            {{ $group->name }}
            <span class="username">{{$group->name}}</span>
        </span>
    </a>

</div>