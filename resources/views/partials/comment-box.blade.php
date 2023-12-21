@if(Auth::user() instanceof App\Models\User)
<form class="comment-box" style="display: none;">
    <input type="hidden" name="post_id" value="{{ $post->id }}"/>
    <div class="comment-box-header">
        <div class="comment-box-header-left">
            <a href="{{ route('user', ['username' => Auth::user()->username]) }}">
                @if(Auth::user()->profile_picture)
                <img src="{{ stream_get_contents(Auth::user()->profile_picture) }}" alt="profile picture"/>
                @php 
                rewind(Auth::user()->profile_picture); 
                @endphp
                @else
                <img src="{{ url('assets/profile-picture.png') }}" alt="profile picture"/>
                @endif
            </a>
        </div>
        <div class="comment-box-header-right">
            <textarea placeholder="Write a comment..." name="content" required></textarea>
            <span class="material-symbols-outlined">
                attach_file
            </span>
            <input type="submit" value="send" class="material-symbols-outlined">
        </div>
    </div>
</form>
@endif
