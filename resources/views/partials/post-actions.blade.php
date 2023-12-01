<div class="post-actions">
    <div class="post-action @if($post->isLikedBy(auth()->user()->id))active @endif">
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
</div>
@include('partials.comment-box')

