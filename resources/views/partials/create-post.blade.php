@if (session('error'))
<p class="error">
    {{ session('error') }}
</p>
@endif

<div class="create-post">
    <div class="post-header">
        
        <a href="{{ route('user', ['username' => Auth::user()->username]) }}">
        @if(Auth::user()->profile_picture)
        <img src="{{stream_get_contents(Auth::user()->profile_picture)}}"/>
        @else
        <img src="{{ url('assets/profile-picture.png') }}"/>
        @endif
        </a>

        <div class="post-text">
            <form method="POST" action="{{ route('create_post') }}" enctype="multipart/form-data">
                {{ csrf_field() }}
                <textarea name="description" placeholder="What project are you thinking about?" cols="25" value="{{ old('description') }}"></textarea>
                <input type="file" name="files[]" multiple="multiple" style="display: none;"/>
                <button type="submit">
                    Post
                </button>
            </form>
        </div>
        <div class="post-files" id="attach-button">
            <span class="material-symbols-outlined">
                attach_file
            </span>
        </div>
    </div>

    <div class="files-list-preview"></div>
    @if ($errors->has('description'))
    <span class="error">
        {{ $errors->first('description') }}
    </span>
    @endif
    
    @if ($errors->has('files'))
    <span class="error">
        {{ $errors->first('files') }}
    </span>
    @endif
</div>
