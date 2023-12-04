<article class="post" data-id="{{ $post->id }}">
    <div class="post-header">
        <div class="post-header-left">
            <a href="{{ route('user', ['username' => $post->author->username]) }}">
            @if($post->author->profile_picture) 
            <img src="{{stream_get_contents($post->author->profile_picture)}}"/>
            @else
            <img src="{{ url('assets/profile-picture.png') }}"/>
            @endif
            </a>
            <div class="author-date">
                <a class="flex" href="{{ route('user', ['username' => $post->author->username]) }}">
                    <p> {{$post->author->name}}</p>
                    <span class="username">
                        &#64;{{$post->author->username}}
                    </span>
                </a>
                <p> {{Carbon\Carbon::parse($post->date)->diffForHumans()}} </p>
            </div>
        </div>
        @if(Auth::user())
            @if ($post->author->id == Auth::user()->id)
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
                    <!-- file_path is one storage/public/uploads/name_of_file -->
                    <img src="{{ url($file->file_path) }}"/>
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
            @if($post->getCommentsCount() > 0)
            <p> {{$post->getCommentsCount()}} comments </p>
            @endif
        </div>
    </div>
    @include('partials.post-actions')
    <div class="post-comments">
        @if($post->getCommentsCount() > 0)
            @foreach($post->directComments as $comment)
                @include('partials.comment')
            @endforeach
        @endif
    </div>

</article>

