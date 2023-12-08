<div class="notification">
    <input type="checkbox" id="notification-{{ $notification->id }}" />
    
    <!-- Sender Profile Picture -->
    @php
        $sender = $notification->sender;
    @endphp

    @if($sender->profile_picture) 
        <img src="{{stream_get_contents($sender->profile_picture)}}"/>
    @else
        <img src="{{ url('assets/profile-picture.png') }}"/>
    @endif

    <div class="notification-inner">
        <p>{{ $sender->name }} ({{ $sender->username }}) wants to be your friend</p>
        <div class="notification-answer">
            <button class="button">Accept</button>
            <button class="button">Decline</button>
        </div>
    </div>

    <!-- Notification Date -->
    <p class="notification-date"> {{Carbon\Carbon::parse($notification->date)->diffForHumans()}} </p>
</div>
