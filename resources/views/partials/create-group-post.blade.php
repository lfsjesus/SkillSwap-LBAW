@if (session('error'))
<p class="error">
    {{ session('error') }}
</p>
@endif

<div class="create-post">
    <div class="post-header">

        <!-- User profile link -->
        <a href="{{ route('user', ['username' => Auth::user()->username]) }}">
            @if(Auth::user()->profile_picture)
                <img src="{{ stream_get_contents(Auth::user()->profile_picture) }}"/>
                @php 
                    rewind(Auth::user()->profile_picture);
                @endphp
            @else
                <img src="{{ url('assets/profile-picture.png') }}"/>
            @endif
        </a>

        <!-- Post creation form -->
        <div class="post-text">
            <form method="POST" action="{{ route('create_group_post', ['id' => $group->id]) }}" enctype="multipart/form-data">
                {{ csrf_field() }}

                <!-- Hidden input for group ID -->
                <input type="hidden" name="group_id" value="{{ $group->id }}">

                <!-- Textarea for post content -->
                <textarea name="description" placeholder="What's on your mind in this group?" cols="25" value="{{ old('description') }}"></textarea>

                <!-- File input (hidden by default) -->
                <input type="file" name="files[]" multiple="multiple" style="display: none;"/>

                <!-- Submit button -->
                <button type="submit">
                    Post to Group
                </button>
            </form>
        </div>

        <!-- Attachment button -->
        <div class="post-files" id="attach-button">
            <span class="material-symbols-outlined">
                attach_file
            </span>
        </div>
    </div>

    <!-- File list preview -->
    <div class="files-list-preview"></div>

    <!-- Error display for description field -->
    @if ($errors->has('description'))
        <span class="error">
            {{ $errors->first('description') }}
        </span>
    @endif
    
    <!-- Error display for files field -->
    @if ($errors->has('files'))
        <span class="error">
            {{ $errors->first('files') }}
        </span>
    @endif
</div>
