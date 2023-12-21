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
        @php 
        rewind(Auth::user()->profile_picture);
        @endphp
        @else
        <img src="{{ url('assets/profile-picture.png') }}"/>
        @endif
        </a>

        <div class="post-text">
            <form method="POST" action="{{ route('create_post') }}" enctype="multipart/form-data">
                {{ csrf_field() }}
                <textarea name="description" placeholder="What project are you thinking about?" cols="25" required></textarea>
                <input type="file" name="files[]" multiple="multiple" style="display: none;"/>
                @if(isset($group))
                <input type="hidden" name="group_id" value="{{$group->id}}">
                @endif

                <!-- Visibility  checkbox -->
                <div class="create-post-footer">
                    <button type="submit">
                        Post
                    </button>
                    <div>
                        <input type="checkbox" name="visibility" id="visibility" value="1" checked>
                        <label for="visibility">Public</label>
                    </div>
                </div>
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
