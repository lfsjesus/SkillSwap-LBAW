<div class="create-post">
    <div class="post-header">
        @if(Auth::user()->profile_picture)
        <img src="{{stream_get_contents($post->author->profile_picture)}}"/>
        @else
        <img src="{{ url('assets/profile-picture.png') }}"/>
        @endif

        <div class="post-text">
            <form method="POST" action="{{ route('create_post') }}">
                {{ csrf_field() }}
                <textarea name="description" placeholder="What project are you thinking about?" cols="25"></textarea>
                <button type="submit">
                    Post
                </button>
            </form>
        </div>
        <div class="post-files">
            <span class="material-symbols-outlined">
                attach_file
            </span>
        </div>
    </div>

</div>
