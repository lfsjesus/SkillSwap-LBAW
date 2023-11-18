<div class="create-post">
    <div class="post-header">
        <img src="{{ url('assets/profile-picture.jpeg') }}"/>
        <div class="post-text">
            <form method="POST">
                {{ csrf_field() }}
                <textarea name="description" placeholder="What project are you thinking about?" cols="25"></textarea>
            </form>
        </div>
        <div class="post-files">
            <span class="material-symbols-outlined">
                attach_file
            </span>
        </div>
    </div>

</div>
