<article class="post" data-id="{{ $post->id }}">
    <div class="post-header">
        
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
    <div class="post-body">
        <p> {{$post->description}} </p>
    </div>
    @include('partials.post-actions')
</article>