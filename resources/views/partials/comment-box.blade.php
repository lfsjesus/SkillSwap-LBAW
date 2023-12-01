<form class="comment-box" style="display: none;">
    <div class="comment-box-header">
        <div class="comment-box-header-left">
            <a href="{{ route('user', ['username' => auth()->user()->username]) }}">
                <img src="{{ url('assets/profile-picture.png') }}"/>
            </a>
        </div>
        <div class="comment-box-header-right">
            <textarea placeholder="Write a comment..."></textarea>
            <span class="material-symbols-outlined">
                attach_file
            </span>
            <span class="material-symbols-outlined">
                send
            </span>
        </div>
    </div>
</form>
