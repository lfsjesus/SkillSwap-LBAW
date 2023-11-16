<article class="post" data-id="{{ $post->id }}">
    <div class="post-header">
        <img src="{{ url('assets/skillswap_white_grey.png') }}"/>
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