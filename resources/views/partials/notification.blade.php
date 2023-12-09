<div class="notification" data-id="{{ $notification->id }}" data-type="{{ $notification->subNotification()->notification_type }}">
    <input type="checkbox"/>
    
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
        <div class="card-info">
            <span class="name"> {{ $sender->name }} </span>
            <span class="username">&#64;{{ $sender->username }}</span>
        </div>
        <p class="notification-text">  Sent you a friend request </p>
            <!-- Notification Date -->
        <p class="notification-date"> {{Carbon\Carbon::parse($notification->date)->diffForHumans()}} </p>
        <div class="notification-answer">
            <button class="button accept-friend-request-notification">
                <input type="hidden" name="sender_id" value="{{ $sender->id }}">
                Accept
            </button>
            <button class="button reject-friend-request-notification">
                <input type="hidden" name="sender_id" value="{{ $sender->id }}">
                Decline
            </button>
        </div>
    </div>


</div>
