<article class="post" data-id="{{ $post->id }}" data-public = "{{ $post->public_post ? '1' : '0' }}">
    <div class="post-header">
        <div class="post-header-left">
            <a href="{{ route('user', ['username' => $post->author->username]) }}">
            @if($post->author->profile_picture) 
            <img src="{{stream_get_contents($post->author->profile_picture)}}" alt="profile picture"/>
            @else
            <img src="{{ url('assets/profile-picture.png') }}" alt="profile picture"/>
            @endif
            </a>
            <div class="author-date">
                <div class="author-date-left">
                <a class="flex" href="{{ route('user', ['username' => $post->author->username]) }}">
                    <p> {{$post->author->name}}</p>
                    <span class="username">
                        &#64;{{$post->author->username}}
                    </span>
                </a>
                
                @if($post->group) 
                <a class="flex" href="{{ route('group', ['id' => $post->group->id]) }}">
                    <span class="groupname">
                        | &nbsp <span class="material-symbols-outlined">group</span> {{$post->group->name}}
                    </span>
                </a>
                @endif
                </div>
                <a href="{{route('post', ['id' => $post->id])}}" class="post-date-stamp"> {{Carbon\Carbon::parse($post->date)->diffForHumans()}} </a>
            </div>
        </div>
        @if(Auth::guard('webadmin')->check() || Auth::check())
            @if (Auth::guard('webadmin')->check() || $post->author->id == Auth::user()->id)
                <div class="post-header-right">
                    <span class='material-symbols-outlined'>edit</span>
                    <span class='material-symbols-outlined'>delete</span>
                </div>
            @endif
        @endif
    </div>
    <div class="post-body">
        <p> {!! $post->description !!} </p>
        @if($post->files())
            @foreach($post->files() as $file)
                <a href="">
                    <img src="{{ url($file->file_path) }}" alt="post file"/>
                </a>
            @endforeach
        @endif

    </div>
    <div class="post-stats">
        <div class="post-stat">
            <span class="material-symbols-outlined">
                thumb_up
            </span>
            <p> {{$post->getLikesCount()}} </p>
        </div>
        <div class="post-stat">
            <p>
            @if($post->getCommentsCount() > 0)
            {{$post->getCommentsCount()}} comments 
            @endif
            </p>
        </div>
    </div>
    @include('partials.post-actions')
    <div class="post-comments">
        @if($post->getCommentsCount() > 0)
            @php
                if (isset($limit)) {
                    $directComments = $post->directComments->sortByDesc('date')->take(4);
                }
                else {
                    $directComments = $post->directComments->sortByDesc('date');
                }
            @endphp
            @foreach($directComments as $comment)
                @include('partials.comment')
            @endforeach
            @if (isset($limit))
                @if ($post->getCommentsCount() > 4)
                    <div class="show-more-comments">
                        <span class="material-symbols-outlined">visibility</span>
                        <a href="{{ route('post', ['id' => $post->id]) }}">Show more comments...</a>
                    </div>
                @endif
            @endif
        @endif
    </div>

</article>

