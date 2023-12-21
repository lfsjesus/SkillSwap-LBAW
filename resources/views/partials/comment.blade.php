<div class="comment" data-id="{{$comment->id}}">
        <a href="{{ route('user', ['username' => $comment->author->username]) }}">
            @if($comment->author->profile_picture)
            <img src="{{stream_get_contents($comment->author->profile_picture)}}" alt="profile picture"/>
            @else
            <img src="{{ url('assets/profile-picture.png') }}" alt="profile picture"/>
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
                @if(Auth::user() instanceof App\Models\User)
                <p class="reply-comment"> Reply </p>
                @endif
                @if (Auth::guard('webadmin')->check() || Auth::check())
                    @if (Auth::guard('webadmin')->check() || $comment->author->id == Auth::user()->id)
                    <p class="edit-comment"> Edit </p>
                    <p class="delete-comment"> Delete </p>
                    @endif
                @endif
            </div>
            </div>
            <div class="comment-replies">
            @if($comment->isParent() && $comment->getRepliesCount() > 0)
                @php
                    if(isset($limitCommentReplies) && isset($limit)) {
                        $descendants = $comment->descendants()->take(3);
                    }
                    else {
                        $descendants = $comment->descendants();
                    }
                @endphp
                @foreach($descendants as $reply)
                    @include('partials.comment', ['comment' => $reply])
                @endforeach
            @endif
            </div>            
            @if($comment->isParent())
            @include('partials.comment-box', ['post' => $comment->post])
            @endif
            @if (isset($limitCommentReplies) && $loop->last && isset($limit))
                @if ($post->getCommentsCount() > 3)
                    <div class="show-more-comments">
                        <span class="material-symbols-outlined">visibility</span>
                        <a href="{{ route('post', ['id' => $post->id]) }}">Show more comments...</a>
                    </div>
                @endif
            @endif 
        </div>  
</div>
