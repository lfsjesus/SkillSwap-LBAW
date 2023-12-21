@php
$sender = $notification->sender;
$subNotification = $notification->subNotification();
$notificationType = $subNotification->notification_type;

$href = 'javascript:void(0)';

if($subNotification instanceof App\Models\PostNotification) {
    $href = route('post', ['id' => $subNotification->post_id]);
}

if($subNotification instanceof App\Models\CommentNotification) {
    $href = route('post', ['id' => $subNotification->comment->post_id]) . '#comment-' . $subNotification->comment->id;
}

if($subNotification instanceof App\Models\UserNotification) {
    $href = route('user', ['username' => $sender->username]);
}

if($subNotification instanceof App\Models\GroupNotification) {
    $href = route('user', ['username' => $sender->username]);
}

@endphp
<a class="notification-href" href="{{ $href }}">
    <div class="notification @if(!$notification->viewed) active @endif" data-id="{{ $notification->id }}" data-type="{{ $notificationType }}" 
        data-sender-id="{{ $notification->sender->id }}" data-receiver-id="{{ $notification->receiver->id }}">
        @if($sender->profile_picture) 
            <img src="{{stream_get_contents($sender->profile_picture)}}" alt="profile picture"/>
        @else
            <img src="{{ url('assets/profile-picture.png') }}" alt="profile picture"/>
        @endif

        <div class="notification-inner">
            <div class="card-info">
                <span class="name"> {{ $sender->name }} </span>
                <span class="username">&#64;{{ $sender->username }}</span>
            </div>

            @if($notificationType == 'friend_request')
                <p class="notification-text">  Sent you a friend request </p>
            @elseif($notificationType == 'like_post')
                <p class="notification-text">  Liked your post </p>
            @elseif($notificationType == 'like_comment')
                <p class="notification-text">  Liked your comment </p>
            @elseif($notificationType == 'new_comment')
                <p class="notification-text">  Commented on your post </p>
            @elseif($notificationType == 'join_request')
                <?php $group = App\Models\Group::find($subNotification->group_id); ?>
                <p class="notification-text">  
                    Wants to join
                    <span class="name">  {{ $group->name }} </span>
                </p>


            @endif

            <p class="notification-date"> {{Carbon\Carbon::parse($notification->date)->diffForHumans()}} </p>

            @if($subNotification->notification_type == 'friend_request')
                <div class="notification-answer">
                    <span class="button accept-friend-request-notification">
                        <input type="hidden" name="sender_id" value="{{ $sender->id }}">
                        Accept
                    </span>
                    <span class="button btn-danger reject-friend-request-notification">
                        <input type="hidden" name="sender_id" value="{{ $sender->id }}">
                        Decline
                    </span>
                </div>
            @endif

            @if($subNotification->notification_type == 'join_request')
                <div class="notification-answer">
                    <span class="button accept-join-request-notification">
                        <input type="hidden" name="sender_id" value="{{ $sender->id }}">
                        <input type="hidden" name="group_id" value="{{ $subNotification->group_id }}">
                        Accept
                    </span>
                    <span class="button btn-danger reject-join-request-notification">
                        <input type="hidden" name="sender_id" value="{{ $sender->id }}">
                        <input type="hidden" name="group_id" value="{{ $subNotification->group_id }}">
                        Decline
                    </span>
                </div>
            @endif

        </div>
    </div>
</a>
