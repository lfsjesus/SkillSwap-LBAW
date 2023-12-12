<div class="comment" data-id="{{$comment->id}}">
        <a href="{{ route('user', ['username' => $comment->author->username]) }}">
            @if($comment->author->profile_picture)
            <img src="{{stream_get_contents($comment->author->profile_picture)}}"/>
            @else
            <img src="{{ url('assets/profile-picture.png') }}"/>
            @endif
        </a>    
        <div class="comment-body">
            <div class="comment-main">
                <div class="inner-comment">
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
                <div class="comment-stat @if(Auth::check() && $comment->isLikedBy(Auth::user()->id)) active @endif">
                    <span class="material-symbols-outlined">
                        thumb_up
                    </span>
                    <p> {{$comment->getLikesCount()}} </p>
                </div>     
            </div>
            <div class="comment-actions">
                <p> {{Carbon\Carbon::parse($comment->date)->diffForHumans()}} </p>
                @if(Auth::user())
                <p class="reply-comment"> Reply </p>
                @if ($comment->author->id == Auth::user()->id)
                <p class="edit-comment"> Edit </p>
                <p class="delete-comment"> Delete </p>
                @endif
                @endif
            </div>
            </div>
            <div class="comment-replies">
            @if($comment->isParent() && $comment->getRepliesCount() > 0)
                @foreach($comment->descendants() as $reply)
                    @include('partials.comment', ['comment' => $reply])
                @endforeach
            @endif
            </div>
            @if($comment->isParent())
            @include('partials.comment-box') 
            @endif
        </div>   
</div>
