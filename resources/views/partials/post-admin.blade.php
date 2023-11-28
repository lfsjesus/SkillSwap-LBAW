<article class="post" data-id="{{ $post->id }}">
    <div class="post-header">
        <div class="post-header-left">
            <a href="{{ route('view-user-admin', ['username' => $post->author->username]) }}">
            @if($post->author->profile_picture) 
            <img src="{{stream_get_contents($post->author->profile_picture)}}"/>
            @else
            <img src="{{ url('assets/profile-picture.png') }}"/>
            @endif
            </a>

            <div class="author-date">
                <a class="flex" href="{{ route('view-user-admin', ['username' => $post->author->username]) }}">
                    <p> {{$post->author->name}} </p>
                    <span class="username">
                        &#64;{{$post->author->username}}
                    </span>
                </a>    
                <p> {{$post->date->format('F j, Y, g:i a')}} </p>
                
            </div>
        </div>
        <div class="post-header-right">
            <span class='material-symbols-outlined'>edit</span>
            <span class='material-symbols-outlined'>delete</span>
        </div>

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
    @include('partials.post-actions')
</article>