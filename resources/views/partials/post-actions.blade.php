
<div class="post-actions">
    @if (Auth::user() instanceof App\Models\User)
        <div class="post-action @if(Auth::user() && $post->isLikedBy(auth()->user()->id))active @endif">
            <span class="material-symbols-outlined">
                thumb_up
                </span>
            <p> Like </p>
        </div>

        <div class="post-action">
            <span class="material-symbols-outlined">
                mode_comment
                </span>
            <p> Comment </p>
        </div>
    @endif
</div>
@include('partials.comment-box')

