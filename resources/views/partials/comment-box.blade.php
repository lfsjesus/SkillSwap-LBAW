<form class="comment-box" style="">
    <input type="hidden" name="post_id" value="{{ $post->id }}"/>
    <input type="hidden" name="user_id" value="{{ Auth::user()->id }}"/>
    <div class="comment-box-header">
        <div class="comment-box-header-left">
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
        </div>
        <div class="comment-box-header-right">
            <textarea placeholder="Write a comment..." name="content"></textarea>
            <span class="material-symbols-outlined">
                attach_file
            </span>
            <input type="submit" value="send" class="material-symbols-outlined">
        </div>
    </div>
</form>
