<div class="comment" data-id="{{$comment->id}}">
    @if($comment->author->profile_picture)
    <img src="{{stream_get_contents($comment->author->profile_picture)}}"/>
    @else
    <img src="{{ url('assets/profile-picture.png') }}"/>
    @endif
    <div class="comment-body">
        <div class="comment-main">
            <div class="comment-header">
                <a href="{{ route('user', ['username' => $comment->author->username]) }}">
                    <p> {{$comment->author->name}} </p>
                    <span class="username">
                        &#64;{{$comment->author->username}}
                    </span>
                </a>
                <div class="comment-content">
                    <p> {!! $comment->content !!} </p>
                </div>
            </div>
            <div class="comment-stat">
                <span class="material-symbols-outlined">
                    thumb_up
                </span>
                <p> {{$comment->getLikesCount()}} </p>
            </div>     
        </div>
        <div class="comment-actions">
            <p> {{Carbon\Carbon::parse($comment->date)->diffForHumans()}} </p>
            <p> @if($comment->isLikedBy(Auth::user()->id)) Unlike @else Like @endif </p>
            <p> Reply </p>
            <!-- Here we will add a comment-box -->
        </div>
    </div>    
</div>