<div class="create-post">
    <div class="post-header">
        @if(Auth::user()->profile_picture)
        <img src="{{stream_get_contents(Auth::user()->profile_picture)}}"/>
        @else
        <img src="{{ url('assets/profile-picture.png') }}"/>
        @endif

        <div class="post-text">
            <form method="POST" action="{{ route('create_post') }}" enctype="multipart/form-data">
                {{ csrf_field() }}
                <textarea name="description" placeholder="What project are you thinking about?" cols="25"></textarea>
                <input type="file" name="files[]" multiple="multiple">
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
