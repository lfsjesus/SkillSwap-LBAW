<div class="comment comment-search" data-id="{{$comment->id}}">
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
                    <a href="{{ route('user',  ['username' => $comment->author->username]) }}">
                        <p> {{$comment->author->name}} </p>
                        <span class="username">
                            &#64;{{$comment->author->username}}
                        </span>
                    </a>
                    <div class="comment-content">
                        <p> {!! $comment->content !!} </p>
                    </div>
                </div> 
                <a href="{{ route('post', ['id' => $comment->post->id]) }}#comment-{{$comment->id}}" class="material-symbols-outlined go-to-comment" title="Go to comment to interact">
                    arrow_forward
                </a>
            </div> 
        </div>
        <div class="comment-actions">
            <p> {{Carbon\Carbon::parse($comment->date)->diffForHumans()}} </p>
        </div>
        <div class="comment-replies">
        @if($comment->isParent() && $comment->getRepliesCount() > 0)
            @foreach($comment->descendants() as $reply)
                @include('partials.comment-search-card', ['comment' => $reply])
            @endforeach
        @endif
        </div>
    </div>   
</div>




