// make click on attach button open input file not jquery

// on dom ready

function encodeForAjax(data) {
  if (data == null) return null;
  return Object.keys(data).map(function(k){
    return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
  }).join('&');
}


function sendAjaxRequest(method, url, data, handler) {
  let request = new XMLHttpRequest();
  console.log(url);
  request.open(method, url, true);
  request.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
  request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  request.addEventListener('load', handler);
  
  request.send(encodeForAjax(data));
}

function postDeletedHandler() {
  if (this.status != 200) window.location = window.location.href;
  let item = JSON.parse(this.responseText);
  let element = document.querySelector('.post[data-id="' + item.id + '"]');
  element.remove();
}



document.addEventListener('DOMContentLoaded', function() {

  let button = document.querySelector('#attach-button');

  if (button != null) {
    button.addEventListener('click', function() {
        document.querySelector('input[type="file"]').click();
    }
    );
  }

  let input = document.querySelector('input[type="file"]');
  
  
  if (input != null) {
    let preview = input.parentNode.parentNode.parentNode.parentNode.querySelector('.files-list-preview');
    input.addEventListener('change', function() { 
      inputFilesHandler.call(this, preview); }
    );
  }

  // when user clicks on delete button, perform ajax request to delete post
  let postDeleteButtons = document.querySelectorAll('.post-header-right span:last-child');

  if (postDeleteButtons != null) {
    postDeleteButtons.forEach(function(button) {
      button.addEventListener('click', function(e) {
        let id = e.target.parentNode.parentNode.parentNode.getAttribute('data-id');
        let data = {post_id: id};
        sendAjaxRequest('DELETE', '/posts/delete', data, postDeletedHandler);
        }
      );
    }
    );
  }


  // when user clicks on edit button, replace article with create_post div
  let postEditButtons = document.querySelectorAll('.post-header-right span:first-child');

  if (postEditButtons != null) {
    postEditButtons.forEach(function(button) {
      button.addEventListener('click', function(e) {
        let id = e.target.parentNode.parentNode.parentNode.getAttribute('data-id');
        editPost(id);
        }
      );
    }
    );
  
  }

}
);

function filterContent(content) {
  return content.replace(/(<([^>]+)>)/ig, ''); // remove html tags
}

async function editPost(id) {
  let post = document.querySelector('.post[data-id="' + id + '"]');
  let profile_picture = post.querySelector('.post-header-left img');
  let files = post.querySelectorAll('.post-body a img'); // TO CHANGE AFTER MODIFYING IMAGES VIEW
  
  let content = post.querySelector('.post-body p');
  /* Elements to create */
  let create_post = document.createElement('div');
  create_post.className = 'create-post';

  let post_header = document.createElement('div');
  post_header.className = 'post-header';

  let post_text = document.createElement('div');
  post_text.className = 'post-text';

  let form = document.createElement('form');
  form.setAttribute('method', 'POST');
  form.setAttribute('action', '/posts/edit');
  form.setAttribute('enctype', 'multipart/form-data');

  let form_id = document.createElement('input');
  form_id.setAttribute('type', 'hidden');
  form_id.setAttribute('name', 'post_id');
  form_id.setAttribute('value', id);
  form.appendChild(form_id);


  let post_files = document.createElement('div');
  post_files.className = 'post-files';
  post_files.setAttribute('id', 'attach-button');

  let input = document.createElement('input');
  input.setAttribute('type', 'file');
  input.setAttribute('name', 'files[]');
  input.setAttribute('multiple', 'multiple');
  input.setAttribute('id', 'test');
  input.style.display = 'none';

  form.appendChild(input);


  let files_list_preview = document.createElement('div');
  files_list_preview.className = 'files-list-preview';
  files_list_preview.style.display = 'flex';

  

  
  /* */


  /* Elements to append */
  let textarea = document.createElement('textarea');
  textarea.className = 'post-textarea';
  textarea.setAttribute('cols', '25');
  textarea.setAttribute('rows', '5');
  textarea.setAttribute('name', 'description');

  textarea.value = (content == null) ? '' : filterContent(content.innerHTML);

  let button = document.createElement('button');
  button.className = 'edit-button';
  button.innerHTML = 'Edit';
  button.setAttribute('type', 'submit');
  form.innerHTML += '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">';
  form.appendChild(textarea);
  form.appendChild(button);
  
  // add hidden input with method PUT
  let method = document.createElement('input');
  method.setAttribute('type', 'hidden');
  method.setAttribute('name', '_method');
  method.setAttribute('value', 'PUT');
  form.appendChild(method);



  post_text.appendChild(form);
  //post_text.appendChild(button);

  post_header.appendChild(profile_picture);
  post_header.appendChild(post_text);

  post_header.appendChild(post_files);
  post_files.innerHTML = "<span class='material-symbols-outlined'>attach_file</span>";

  create_post.appendChild(post_header);
  create_post.appendChild(files_list_preview);

  
  // replace article with create_post div
  post.replaceWith(create_post);




   let realInput = create_post.querySelector('input[type="file"]');
   console.log(realInput);
   
   await fetchFiles(realInput, files);

   //console.log(realInput.files);

  // create as many file-preview divs as there are files
  if (files != null) {
    files.forEach(function(file) {
      let div = document.createElement('div');
      div.className = 'file-preview';
      let span = document.createElement('span');
      span.addEventListener('click', function(e) {
        remove_file_from_preview(e, realInput, files_list_preview);
      }
      );
      span.className = 'material-symbols-outlined';
      span.innerHTML = 'close';
      div.appendChild(span);
      let a = document.createElement('a');
      let img = file;
      a.appendChild(img);
      div.appendChild(a);
      files_list_preview.appendChild(div);
    });

    //console.log(input.files);
  }



  post_files.addEventListener('click', function() {
    realInput.click();
  }
  );

  realInput.addEventListener('change', function() { inputFilesHandler.call(this, files_list_preview); } );
  
  let editButton = create_post.querySelector('.edit-button');
  editButton.addEventListener('click', function() {
    // prevent default
    event.preventDefault();

  
    let request = new XMLHttpRequest();
    let data = new FormData(form);

    console.log(data.get('files[]'));
    // send PUT
    request.open('POST', '/posts/edit', true);
    request.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
    
    request.addEventListener('load', function() {
      if (this.status == 200) {
        // refresh page to current window
        window.location = window.location.href;
        
      }
    }
    );
    request.send(data); 
  } 
  );
}


function remove_file_from_preview(e, file, preview) {
  if (e.target.tagName == 'SPAN') {
    let id = e.target.parentNode.id;
    //let file = document.querySelector('input[type="file"]');
    let files = file.files;
    //let preview = document.querySelector('.files-list-preview');

    let filesArr = Array.from(files);

    let newFilesList = new DataTransfer();

    filesArr.forEach(function(file, index) {
      if (index != id) {
        newFilesList.items.add(file);
      }
    }
    );

    file.files = newFilesList.files;

    e.target.parentNode.remove();
    if (preview.children.length == 0) {
      preview.style.display = 'none';
    }
  }
}


async function fetchFiles(input, files) {
  if (files != null) {
      let newFilesList = new DataTransfer();
      let fetchPromises = Array.from(files).map(file => {
      let imageUrl = file.getAttribute('src');
      let imageName = imageUrl.split('/').pop(); 
      

      return fetch(imageUrl)
        .then(res => res.blob())
        .then(blob => new File([blob], imageName, {type: 'image/png'}));

    });


    await Promise.all(fetchPromises)
      .then(function(values) {
        values.forEach(function(value) {
          newFilesList.items.add(value);
        });

        input.files = newFilesList.files;
      });

  }
}


function inputFilesHandler(preview, finalFiles) {
  let files = this.files;

  let filesArr = Array.from(files);
  let i = 0;
  filesArr.forEach(function(file) {
    let reader = new FileReader();
    reader.onloadend = function() {
      let div = document.createElement('div');
      let span = document.createElement('span');
      span.className = 'material-symbols-outlined';
      span.innerHTML = 'close';
      div.className = 'file-preview';
      div.setAttribute('id', i);

      let img = document.createElement('img');
      img.src = reader.result;
      div.appendChild(span);
      div.appendChild(img);
      preview.appendChild(div);
      preview.style.display = 'flex';
      preview.addEventListener('click', function(e) {
        remove_file_from_preview(e, this.parentNode.querySelector('input[type="file"]'), this);
      }
      );
      i++;
    }
    reader.readAsDataURL(file);
  });

}



// LIKE POST
function likePostHandler() {
  // set class active to .post-actions .post-action:first-child
  let item = JSON.parse(this.responseText);
  if (item == null) return;

  let element = document.querySelector('.post[data-id="' + item.post_id + '"]');
  let button = element.querySelector('.post-actions .post-action:first-child');

  // To update like count
  let likeStat = element.querySelector('.post-stats .post-stat:first-child p');
  let likeCount = parseInt(likeStat.innerHTML);
  
  if (item.liked) {
    button.classList.add('active');
    likeCount++;
  }
  else {
    button.classList.remove('active');
    likeCount--;
  }
  
  likeStat.innerHTML = likeCount;    
}

let postLikeButtons = document.querySelectorAll('article.post .post-actions .post-action:first-child');

if (postLikeButtons != null) {
  postLikeButtons.forEach(function(button) {
    button.addEventListener('click', function(e) {
      let id = e.target.closest('article.post').getAttribute('data-id');
      console.log(id);
      let data = {post_id: id};
      sendAjaxRequest('POST', '/posts/like', data, likePostHandler);
      }
    );
  }
  );
}



function likeCommentHandler() {
  // set class active to .post-actions .post-action:first-child
  let item = JSON.parse(this.responseText);
  if (item == null) return;

  let element = document.querySelector('.comment[data-id="' + item.comment_id + '"]');
  let button = element.querySelector('.comment-stat');

  // To update like count
  
  let likeStat = button.querySelector('p');
  let likeCount = parseInt(likeStat.innerHTML);
  
  if (item.liked) {
    button.classList.add('active');
    likeCount++;
  }
  else {
    button.classList.remove('active');
    likeCount--;
  }
  
  likeStat.innerHTML = likeCount;    

}


let commentLikeButtons = document.querySelectorAll('article.post .comment .comment-stat');

if (commentLikeButtons != null) {
  commentLikeButtons.forEach(function(button) {
    button.addEventListener('click', function(e) {
      let id = e.target.closest('.comment').getAttribute('data-id');
      let data = {comment_id: id};
      sendAjaxRequest('POST', '/comments/like', data, likeCommentHandler);
      }
    );
  }
  );
}


// show comment box when user clicks on comment button

let commentButtons = document.querySelectorAll('article.post .post-actions .post-action:nth-child(2)');

if (commentButtons != null) {
  commentButtons.forEach(function(button) {
    button.addEventListener('click', commentButtonHandler);
  }
  );
}


function commentButtonHandler() {

  let id = this.closest('article.post').getAttribute('data-id');
  let comment = this.closest('.comment');
  let commentBox = null;

  if (comment == null) {
    commentBox = document.querySelector('article.post[data-id="' + id + '"] .comment-box');
  }
  else {  
    commentBox = comment.querySelector('.comment-box');
    if (commentBox == null) {
      commentBox = comment.parentNode.parentNode.querySelector('.comment-box');
    }
  }


  if (commentBox.style.display == 'none') {
    commentBox.style.display = 'flex';
  }
  else {
    commentBox.style.display = 'none';
  }
}



let replyButtons = document.querySelectorAll('article.post .comment .comment-actions p:nth-child(2)');
if (replyButtons != null) {
  replyButtons.forEach(function(button) {
    button.addEventListener('click', function(e) {
      let id = e.target.closest('.comment').getAttribute('data-id');
      let commentBox = document.querySelector('.comment[data-id="' + id + '"]').querySelector('.comment-box');

      if (commentBox == null) {
        commentBox = document.querySelector('.comment[data-id="' + id + '"]').parentNode.parentNode.querySelector('.comment-box');
      }
    
      if (commentBox.style.display == 'none') {
        commentBox.style.display = 'flex';
      }
      else {
        commentBox.style.display = 'none';
      }  
      }
    );
  }
  );
}

function commentPostHandler() {
  let response = JSON.parse(this.responseText);

  if (response == null) return;

  let comment = createComment(response.id, response.post_id, response.author_name, response.content, response.replyTo_id);

  // If comment is a reply, append it to the parent comment. Else, append it to the post.
  if (response.replyTo_id != null) {
    let parent = document.querySelector('.comment[data-id="' + response.replyTo_id + '"]');

    let replies = parent.querySelector('.comment-replies');
    replies.appendChild(comment);
  }
  else {
    let comments = document.querySelector('article.post[data-id="' + response.post_id + '"] .post-comments');
    comments.appendChild(comment);
  }

  // Reset Textarea
  let initialCommentBox = document.querySelectorAll('article.post[data-id="' + response.post_id + '"] .comment-box');

  if (initialCommentBox != null) {
    initialCommentBox.forEach(function(box) {
      box.querySelector('textarea[name="content"]').value = '';
      box.style.display = 'none';
    }
    );
  }
  // Update comment count on post
  let commentCount = document.querySelector('article.post[data-id="' + response.post_id + '"] .post-stats .post-stat:nth-child(2) p');
  let count = parseInt(commentCount.innerHTML) || 0;
  count++;

  commentCount.innerHTML = count + ' comments'; 
}

// comment on post
let postCommentForms = document.querySelectorAll('article.post > form.comment-box');

if (postCommentForms != null) {
  postCommentForms.forEach(function(form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      let post_id = e.target.querySelector('input[name="post_id"]').value;
      let content = e.target.querySelector('textarea[name="content"]').value;
      let data = {post_id: post_id, content: content};

      sendAjaxRequest('POST', '/posts/comment', data, commentPostHandler);
      }
    );
  }
  );
}


// comment on comment
let replyCommentForms = document.querySelectorAll('article.post .comment .comment-box');

if (replyCommentForms != null) {
  replyCommentForms.forEach(function(form) {
    form.addEventListener('submit', replyCommentFormHandler);
  }
  );
}

function replyCommentFormHandler(event) {
  event.preventDefault();
  let post_id = this.closest('.post').querySelector('input[name="post_id"]').value;
  let comment_id = this.closest('.comment').getAttribute('data-id');
  let content = this.querySelector('textarea[name="content"]').value;
  let data = {post_id: post_id, comment_id: comment_id, content: content};
  sendAjaxRequest('POST', '/posts/comment', data, commentPostHandler);
}

function editCommentFormHandler(event) {
  event.preventDefault();
  let id = this.getAttribute('data-id');
  let content = this.querySelector('textarea[name="content"]').value;
  let data = {id: id, content: content};
  console.log(data);
  sendAjaxRequest('PUT', '/posts/comment/edit', data, editCommentHandler);
}

// delete comment
let deleteCommentButtons = document.querySelectorAll('article.post .comment .comment-actions p:last-child');

if (deleteCommentButtons != null) {
  deleteCommentButtons.forEach(function(button) {
    button.addEventListener('click', function(e) {
      let id = e.target.closest('.comment').getAttribute('data-id');
      let data = {id: id};
      sendAjaxRequest('DELETE', '/posts/comment/delete', data, deleteCommentHandler);
      }
    );
  }
  );
}

function deleteCommentHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null) return;

  let comment = document.querySelector('.comment[data-id="' + response.id + '"]');
  let commentCount = comment.closest('article.post').querySelector('.post-stats .post-stat:nth-child(2) p');

  comment.remove();

  let count = parseInt(commentCount.innerHTML) || 0;
  count--;

  if (count == 0) {
    commentCount.innerHTML = '';
  }
  else {
  commentCount.innerHTML = count + ' comments';
  }
}


let editCommentButtons = document.querySelectorAll('article.post .comment .comment-actions p:nth-child(3)');
if (editCommentButtons != null) {
  editCommentButtons.forEach(function(button) {
    button.addEventListener('click', function(e) {
      let id = e.target.closest('.comment').getAttribute('data-id');
      editComment(id);
      }
    );
  }
  );
}


function editComment(id) {
  let comment = document.querySelector('.comment[data-id="' + id + '"]');
  let post_id = comment.closest('.post').getAttribute('data-id');
  let profile_picture = comment.querySelector('.comment-body img').src;
  let author_url = comment.querySelector('.comment-header a').getAttribute('href');
  let content = comment.querySelector('.comment-content p').innerHTML;

  let commentBox = createCommentBox(post_id, author_url, profile_picture, content, 'edit', id);

  comment.replaceWith(commentBox);

  commentBox.style.display = 'flex';  
}

function createComment(id, post_id, author_name, content, replyTo_id) {
  let profile_picture = document.querySelector('.comment-box-header-left img').src;
  let author_url = document.querySelector('.comment-box-header-left a').getAttribute('href');

  let comment = document.createElement('div');
  comment.className = 'comment';
  comment.setAttribute('data-id', id);

  let img = document.createElement('img');
  img.src = profile_picture;

  let commentBody = document.createElement('div');
  commentBody.className = 'comment-body';

  let commentMain = document.createElement('div');
  commentMain.className = 'comment-main';

  let innerComment = document.createElement('div');
  innerComment.className = 'inner-comment';

  let commentHeader = document.createElement('div');
  commentHeader.className = 'comment-header';

  let a = document.createElement('a');
  a.setAttribute('href', author_url);

  let p = document.createElement('p');
  p.innerHTML = author_name; 

  let span = document.createElement('span');
  span.className = 'username';
  span.innerHTML = '&#64;' + author_url.split('/').pop();

  let commentContent = document.createElement('div');
  commentContent.className = 'comment-content';

  let commentContentP = document.createElement('p');
  commentContentP.innerHTML = content; 

  // Comment-header
  a.appendChild(p);
  a.appendChild(span);
  commentHeader.appendChild(a);
  commentContent.appendChild(commentContentP);
  commentHeader.appendChild(commentContent);


  let commentStat = document.createElement('div');
  commentStat.className = 'comment-stat';

  let commentStatSpan = document.createElement('span');
  commentStatSpan.className = 'material-symbols-outlined';
  commentStatSpan.innerHTML = 'thumb_up';

  commentStat.addEventListener('click', function(e) {
    let id = e.target.closest('.comment').getAttribute('data-id');
    let data = {comment_id: id};
    sendAjaxRequest('POST', '/comments/like', data, likeCommentHandler);
    }
  );

  let commentStatP = document.createElement('p');
  commentStatP.innerHTML = '0';

  // Inner comment
  commentStat.appendChild(commentStatSpan);
  commentStat.appendChild(commentStatP);
  innerComment.appendChild(commentHeader);
  innerComment.appendChild(commentStat);

  // Comment main
  commentMain.appendChild(innerComment);

  let commentActions = document.createElement('div');
  commentActions.className = 'comment-actions';

  let commentActionsP1 = document.createElement('p');
  commentActionsP1.innerHTML = 'Just now'; 

  let commentActionsP2 = document.createElement('p');
  commentActionsP2.innerHTML = 'Reply'; 
  commentActionsP2.addEventListener('click', commentButtonHandler);

  let commentActionsP3 = document.createElement('p');
  commentActionsP3.innerHTML = 'Edit';
  commentActionsP3.addEventListener('click', function(e) {
    let id = e.target.closest('.comment').getAttribute('data-id');
    editComment(id);
    }
  );

  let commentActionsP4 = document.createElement('p');
  commentActionsP4.innerHTML = 'Delete';
  commentActionsP4.addEventListener('click', function(e) {
    let id = e.target.closest('.comment').getAttribute('data-id');
    let data = {id: id};
    sendAjaxRequest('DELETE', '/posts/comment/delete', data, deleteCommentHandler);
    }
  );

  // Comment actions
  commentActions.appendChild(commentActionsP1);
  commentActions.appendChild(commentActionsP2);
  commentActions.appendChild(commentActionsP3);
  commentActions.appendChild(commentActionsP4);

  // Comment body
  let commentReplies = document.createElement('div');
  commentReplies.className = 'comment-replies';
  commentBody.appendChild(commentMain);
  commentBody.appendChild(commentReplies);
  

  commentMain.appendChild(commentActions);


  if (replyTo_id == null) {
    let commentBox = createCommentBox(post_id, author_url, profile_picture, '', 'new');
    commentBody.appendChild(commentBox);
  }

  comment.appendChild(img);
  comment.appendChild(commentBody);

  return comment;
}


function createCommentBox(post_id, author_url, profile_picture, value, type, edit_id) { // type: new or edit
  let commentBox = document.createElement('form');
  commentBox.className = 'comment-box';
  commentBox.style.display = 'none';
  
  if (edit_id != null) {
    commentBox.setAttribute('data-id', edit_id);
  }

  if (type == 'new') {
  commentBox.addEventListener('submit', replyCommentFormHandler);
  }
  else {
    commentBox.addEventListener('submit', editCommentFormHandler);
  }

  let commentBoxInput1 = document.createElement('input');
  commentBoxInput1.setAttribute('type', 'hidden');
  commentBoxInput1.setAttribute('name', 'post_id');
  commentBoxInput1.setAttribute('value', post_id);

  let commentBoxHeader = document.createElement('div');
  commentBoxHeader.className = 'comment-box-header';

  let commentBoxHeaderLeft = document.createElement('div');
  commentBoxHeaderLeft.className = 'comment-box-header-left';

  let commentBoxHeaderLeftA = document.createElement('a');
  commentBoxHeaderLeftA.setAttribute('href', author_url);

  let commentBoxHeaderLeftImg = document.createElement('img');
  commentBoxHeaderLeftImg.src = profile_picture;

  commentBoxHeaderLeftA.appendChild(commentBoxHeaderLeftImg);
  commentBoxHeaderLeft.appendChild(commentBoxHeaderLeftA);

  let commentBoxHeaderRight = document.createElement('div');
  commentBoxHeaderRight.className = 'comment-box-header-right';

  let commentBoxHeaderRightTextarea = document.createElement('textarea');
  commentBoxHeaderRightTextarea.setAttribute('placeholder', 'Write a comment...');
  commentBoxHeaderRightTextarea.setAttribute('name', 'content');
  commentBoxHeaderRightTextarea.value = value;

  let commentBoxHeaderRightSpan1 = document.createElement('span');
  commentBoxHeaderRightSpan1.className = 'material-symbols-outlined';
  commentBoxHeaderRightSpan1.innerHTML = 'attach_file';

  let commentBoxHeaderRightInput = document.createElement('input');
  commentBoxHeaderRightInput.setAttribute('type', 'submit');
  commentBoxHeaderRightInput.setAttribute('value', 'send');
  commentBoxHeaderRightInput.className = 'material-symbols-outlined';

  commentBoxHeaderRight.appendChild(commentBoxHeaderRightTextarea);
  commentBoxHeaderRight.appendChild(commentBoxHeaderRightSpan1);
  commentBoxHeaderRight.appendChild(commentBoxHeaderRightInput);


  /* Append header left and right to header */
  commentBoxHeader.appendChild(commentBoxHeaderLeft);
  commentBoxHeader.appendChild(commentBoxHeaderRight);

  /* Append inputs  and comment box header to form */
  commentBox.appendChild(commentBoxInput1);
  commentBox.appendChild(commentBoxHeader);
  
  return commentBox;
}


function editCommentHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null) return;

  let comment = createComment(response.id, response.post_id, response.author_name, response.content, response.replyTo_id);

  // replace comment box with comment
  let commentBox = document.querySelector('.comment-box[data-id="' + response.id + '"]');

  commentBox.replaceWith(comment);
  
}

// Handle active menu based on url
let menuItems = document.querySelectorAll('nav ul li');

if (menuItems != null) {
  menuItems.forEach(function(item) {
    if (item.querySelector('a').href == window.location.href) {
      item.classList.add('active');
    }
  }
  );
}

// Handle Friend Requests using Event Delegation
document.addEventListener('click', function(e) {
  if (e.target.closest('.add-friend')) {
      handleAddFriendClick(e);
  } else if (e.target.closest('.cancel-friend-request')) {
      handleCancelFriendRequestClick(e);
  } else if (e.target.closest('.accept-friend-request')) {
      handleAcceptFriendRequestClick(e);
  }
});

function handleAddFriendClick(e) {
  let friend_id = e.target.closest('.add-friend').querySelector('input[name="friend_id"]').value;
  let data = { friend_id: friend_id };
  sendAjaxRequest('POST', '/friend/request', data, addFriendHandler);
}

function handleCancelFriendRequestClick(e) {
  let friend_id = e.target.closest('.cancel-friend-request').querySelector('input[name="friend_id"]').value;
  let data = { friend_id: friend_id };
  sendAjaxRequest('DELETE', '/friend/cancel_request', data, cancelFriendRequestHandler);
}

function handleAcceptFriendRequestClick(e) {
  let friend_id = e.target.closest('.accept-friend-request').querySelector('input[name="friend_id"]').value;
  let data = { friend_id: friend_id };
  sendAjaxRequest('POST', '/friend/accept_request', data, acceptFriendRequestHandler);
}

function addFriendHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null) return;

  let button = document.querySelector('.add-friend');
  button.classList.remove('add-friend');
  button.classList.add('cancel-friend-request');
  let iconSpan = button.querySelector('span');
  iconSpan.innerHTML = 'done';
  let input2 = button.querySelector('input[name="friend_id"]');
  button.innerHTML = '';
  button.appendChild(input2);
  button.appendChild(iconSpan);
  button.innerHTML += 'Request sent';
}

function cancelFriendRequestHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null) return;

  let button = document.querySelector('.cancel-friend-request');
  button.classList.remove('cancel-friend-request');
  button.classList.add('add-friend');
  let iconSpan = button.querySelector('span');
  iconSpan.innerHTML = 'person_add';
  let input2 = button.querySelector('input[name="friend_id"]');
  button.innerHTML = '';
  button.appendChild(input2);
  button.appendChild(iconSpan);
  button.innerHTML += 'Add friend';
}

function acceptFriendRequestHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null) return;

  let button = document.querySelector('.accept-friend-request');
  button.classList.remove('accept-friend-request');
  button.classList.add('remove-friend');
  let iconSpan = button.querySelector('span');
  iconSpan.innerHTML = 'person_remove';
  let input2 = button.querySelector('input[name="friend_id"]');
  button.innerHTML = '';
  button.appendChild(input2);
  button.appendChild(iconSpan);
  button.innerHTML += 'Remove Friend';
}

// Get all elements with class="dropbtn" and attach a click event listener
document.querySelectorAll('.dropbtn').forEach(dropbtn => {
  dropbtn.onclick = function() {
      this.nextElementSibling.classList.toggle("show");
  }
});

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {
      let dropdowns = document.getElementsByClassName("dropdown-content");
      for (let i = 0; i < dropdowns.length; i++) {
          let openDropdown = dropdowns[i];
          if (openDropdown.classList.contains('show')) {
              openDropdown.classList.remove('show');
          }
      }
  }
};
