<article class="post" data-id="{{ $post->id }}">
    <div class="post-header">
        <div class="post-header-left">
            @if($post->author->profile_picture) 
            <img src="{{stream_get_contents($post->author->profile_picture)}}"/>
            @else
            <img src="{{ url('assets/profile-picture.png') }}"/>
            @endif

            <div class="author-date">
                <p> {{$post->author->name}} </p>
                <p> {{$post->date->format('F j, Y, g:i a')}} </p>
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
    @include('partials.post-actions')
</article>