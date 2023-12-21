// make click on attach button open input file not jquery

// on dom ready

function encodeForAjax(data) {
  if (data == null) return null;
  return Object.keys(data).map(function(k){
    return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
  }).join('&');
}

let btnDanger = document.querySelector('#content .btn-danger');
if (btnDanger != null) {
  btnDanger.addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      background: '#232a37',
      color: '#fff',
      showCancelButton: true,
      confirmButtonText: 'Proceed',
      confirmButtonColor: '#663FA4',
      cancelButtonText: 'Cancel',
      cancelButtonColor: '#151b26'
    }).then((result) => {
      if (result.isConfirmed) {
        let formName = btnDanger.getAttribute('form');
        let form = document.querySelector('#' + formName);
        form.submit();
      }});
    }
  );
}

function toggleMenu(side) {
  let sideBar = document.getElementById(side + '-bar');
  sideBar.classList.toggle('active-menu');
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
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

  let element = document.querySelector('.post[data-id="' + response.id + '"]');
  element.remove();

  let content = document.querySelector('#content');

  if (content.children.length == 1) {
    window.location = '/';
  }
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
      button.addEventListener('click', postDeleteClickHandler);
    });
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

function postDeleteClickHandler(e) {
  e.preventDefault();
  let id = e.target.parentNode.parentNode.parentNode.getAttribute('data-id');
  let data = {post_id: id};
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    background: '#232a37',
    color: '#fff',
    showCancelButton: true,
    confirmButtonText: 'Proceed',
    confirmButtonColor: '#663FA4',
    cancelButtonText: 'Cancel',
    cancelButtonColor: '#151b26'
  }).then((result) => {
    if (result.isConfirmed) {
      sendAjaxRequest('DELETE', '/posts/delete', data, postDeletedHandler);
    }});
}

function filterContent(content) {
  return content.replace(/(<([^>]+)>)/ig, ''); // remove html tags
}

async function editPost(id) {
  let post = document.querySelector('.post[data-id="' + id + '"]');
  let originalPost = post.cloneNode(true); //clone the post in case the user cancels the edit
  let profile_picture = post.querySelector('.post-header-left img');
  let files = post.querySelectorAll('.post-body a img'); // TO CHANGE AFTER MODIFYING IMAGES VIEW
  let public_post = post.getAttribute('data-public');
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
  
  let createPostFooter = document.createElement('div');
  createPostFooter.className = 'create-post-footer';

  let inputCheckBox = document.createElement('input');
  inputCheckBox.setAttribute('type', 'checkbox');
  inputCheckBox.setAttribute('name', 'visibility');
  inputCheckBox.setAttribute('value', '1');

  if (public_post == 1) {
    inputCheckBox.setAttribute('checked', 'checked');
  }

  let checkboxLabel = document.createElement('label');
  checkboxLabel.setAttribute('for', 'visibility');
  checkboxLabel.innerHTML = 'Public';

  let checkboxDiv = document.createElement('div');

  checkboxDiv.appendChild(inputCheckBox);
  checkboxDiv.appendChild(checkboxLabel);

  let button = document.createElement('button');
  button.className = 'edit-button';
  button.innerHTML = 'Save';
  button.setAttribute('type', 'submit');
  form.innerHTML += '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">';
  

  let cancelButton = document.createElement('button');
  cancelButton.className = 'cancel-button';
  cancelButton.classList.add('btn-cancel');
  cancelButton.innerHTML = 'Cancel';
  cancelButton.setAttribute('type', 'button');
  cancelButton.addEventListener('click', function(e) {
    e.preventDefault();
    create_post.replaceWith(originalPost);
    postSetAllEventListeners(originalPost);
  }
  );
  
  let buttonsDiv = document.createElement('div');
  buttonsDiv.className = 'buttons-div';

  buttonsDiv.appendChild(cancelButton);
  buttonsDiv.appendChild(button);

  createPostFooter.appendChild(buttonsDiv);
  createPostFooter.appendChild(checkboxDiv);

  form.appendChild(textarea);
  form.appendChild(createPostFooter);
  
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
  if (item == null || item.success == false) return;

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
    button.addEventListener('click', commentLikeClickHandler);
  }
  );
}

function commentLikeClickHandler() {
  let id = this.closest('.comment').getAttribute('data-id');
  let data = {comment_id: id};
  sendAjaxRequest('POST', '/comments/like', data, likeCommentHandler);
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



let replyButtons = document.querySelectorAll('article.post .comment .comment-actions .reply-comment');
if (replyButtons != null) {
  replyButtons.forEach(function(button) {
    button.addEventListener('click', replyCommentClickHandler);
  }
  );
}

function replyCommentClickHandler() {
  let id = this.closest('.comment').getAttribute('data-id');
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


function commentPostHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

  let comment = createComment(response.id, response.post_id, response.author_name, response.content, response.replyTo_id);

  // If comment is a reply, append it to the parent comment. Else, append it to the post.
  if (response.replyTo_id != null) {
    let parent = document.querySelector('.comment[data-id="' + response.replyTo_id + '"]');

    let replies = parent.querySelector('.comment-replies');
    replies.appendChild(comment);
  }
  else {
    let comments = document.querySelector('article.post[data-id="' + response.post_id + '"] .post-comments');
    // append in the beginning
    comments.insertBefore(comment, comments.firstChild);
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
      commentPostClickHandler(e);
      }
    );
  }
  );
}

function commentPostClickHandler(e) {
  e.preventDefault();
  let post_id = e.target.closest('article.post').getAttribute('data-id');
  let content = e.target.closest('form').querySelector('textarea[name="content"]').value;
  let data = {post_id: post_id, content: content};
  sendAjaxRequest('POST', '/posts/comment', data, commentPostHandler);
}

// comment on comment
let replyCommentForms = document.querySelectorAll('article.post .comment .comment-box');

if (replyCommentForms != null) {
  replyCommentForms.forEach(function(form) {
    form.addEventListener('submit', replyCommentFormHandler);
  }
  );
}

function replyCommentFormHandler(e) {
  e.preventDefault();
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
  sendAjaxRequest('PUT', '/posts/comment/edit', data, editCommentHandler);
}

// delete comment
let deleteCommentButtons = document.querySelectorAll('article.post .comment .comment-actions .delete-comment');

function deleteCommentClickHandler(e) {
  let id = e.target.closest('.comment').getAttribute('data-id');
  let data = {id: id};
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    background: '#232a37',
    color: '#fff',
    showCancelButton: true,
    confirmButtonText: 'Proceed',
    confirmButtonColor: '#663FA4',
    cancelButtonText: 'Cancel',
    cancelButtonColor: '#151b26'
  }).then((result) => {
    if (result.isConfirmed) {
    sendAjaxRequest('DELETE', '/posts/comment/delete', data, deleteCommentHandler);
    }
  })}
;

if (deleteCommentButtons != null) {
  deleteCommentButtons.forEach(function(button) {
    button.addEventListener('click', deleteCommentClickHandler);
  }
  );
}

function deleteCommentHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

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


let editCommentButtons = document.querySelectorAll('article.post .comment .comment-actions .edit-comment');
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
  let originalComment = comment.cloneNode(true); //clone the comment in case the user cancels the edit
  let post_id = comment.closest('.post').getAttribute('data-id');
  let profile_picture = comment.querySelector('a img').src;
  let author_url = comment.querySelector('.comment-header a').getAttribute('href');
  let content = comment.querySelector('.comment-content p').innerHTML;

  let commentBox = createCommentBox(post_id, author_url, profile_picture, content, 'edit', id);


  let cancelEditButton = document.createElement('button');
  cancelEditButton.className = 'cancel-button';
  cancelEditButton.classList.add('btn-cancel');
  cancelEditButton.innerHTML = 'Cancel';
  cancelEditButton.setAttribute('type', 'button');
  cancelEditButton.addEventListener('click', function(e) {
    e.preventDefault();
    commentBox.replaceWith(originalComment);
    commentSetAllEventListeners(originalComment);
  }
  );  

  commentBox.appendChild(cancelEditButton);

  comment.replaceWith(commentBox);

  commentBox.style.display = 'block';  
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
  commentActionsP2.className = 'reply-comment';
  commentActionsP2.addEventListener('click', commentButtonHandler);

  let commentActionsP3 = document.createElement('p');
  commentActionsP3.className = 'edit-comment';
  commentActionsP3.innerHTML = 'Edit';
  commentActionsP3.addEventListener('click', function(e) {
    let id = e.target.closest('.comment').getAttribute('data-id');
    editComment(id);
    }
  );

  let commentActionsP4 = document.createElement('p');
  commentActionsP4.className = 'delete-comment';
  commentActionsP4.innerHTML = 'Delete';
  commentActionsP4.addEventListener('click', function(e) {
    deleteCommentClickHandler(e);
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

  let imgHref = document.createElement('a');
  imgHref.setAttribute('href', author_url);
  imgHref.appendChild(img);

  comment.appendChild(imgHref);
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
  if (response == null || response.success == false) return;

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
let addFriend = document.querySelector('.add-friend');
if (addFriend != null) {
  addFriend.addEventListener('click', handleAddFriendClick);

}

let cancelFriendRequest = document.querySelector('.cancel-friend-request');
if (cancelFriendRequest != null) {
  cancelFriendRequest.addEventListener('click', handleCancelFriendRequestClick);
}

let acceptFriendRequest = document.querySelector('.accept-friend-request');
if (acceptFriendRequest != null) {
  acceptFriendRequest.addEventListener('click', handleAcceptFriendRequestClick);
}

let removeFriend = document.querySelector('.remove-friend');
if (removeFriend != null) {
  removeFriend.addEventListener('click', handleRemoveFriendClick);
}

let acceptFriendRequestNotification = document.querySelector('.accept-friend-request-notification');
if (acceptFriendRequestNotification != null) {
  acceptFriendRequestNotification.addEventListener('click', function(e) {
    e.preventDefault();
    handleAcceptFriendRequestNotificationClick(e);
  }
  );
}

let rejectFriendRequestNotification = document.querySelector('.reject-friend-request-notification');
if (rejectFriendRequestNotification != null) {
  rejectFriendRequestNotification.addEventListener('click', function(e) {
    e.preventDefault();
    handleRejectFriendRequestNotificationClick(e);
  }
  );
}


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
  let sender_id = e.target.closest('.accept-friend-request').querySelector('input[name="sender_id"]').value;
  let data = { sender_id: sender_id };
  sendAjaxRequest('POST', '/friend/accept_request', data, acceptFriendRequestHandler);
}

function handleRemoveFriendClick(e) {
  let friend_id = e.target.closest('.remove-friend').querySelector('input[name="friend_id"]').value;
  let data = { friend_id: friend_id };
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    background: '#232a37',
    color: '#fff',
    showCancelButton: true,
    confirmButtonText: 'Proceed',
    confirmButtonColor: '#663FA4',
    cancelButtonText: 'Cancel',
    cancelButtonColor: '#151b26'
  }).then((result) => {
    if (result.isConfirmed) {
      sendAjaxRequest('DELETE', '/friend/remove', data, removeFriendHandler);
    }});
}

function handleAcceptFriendRequestNotificationClick(e) {
  let sender_id = e.target.closest('.accept-friend-request-notification').querySelector('input[name="sender_id"]').value;
  let data = { sender_id: sender_id };
  sendAjaxRequest('POST', '/friend/accept_request', data, acceptFriendRequestNotificationHandler);
}

function handleRejectFriendRequestNotificationClick(e) {
  let sender_id = e.target.closest('.reject-friend-request-notification').querySelector('input[name="sender_id"]').value;
  let data = { sender_id: sender_id };
  sendAjaxRequest('DELETE', '/friend/reject_request', data, rejectFriendRequestNotificationHandler);
}


function addFriendHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

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

  button.removeEventListener('click', handleAddFriendClick);
  button.addEventListener('click', handleCancelFriendRequestClick);
}

function cancelFriendRequestHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

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

  button.removeEventListener('click', handleCancelFriendRequestClick);
  button.addEventListener('click', handleAddFriendClick);
}


function acceptFriendRequestHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

  let button = document.querySelector('.accept-friend-request');
  button.classList.remove('accept-friend-request');
  button.classList.add('remove-friend');
  let iconSpan = button.querySelector('span');
  iconSpan.innerHTML = 'person_remove';
  let input2 = button.querySelector('input[name="sender_id"]');
  if (input2) {
    input2.setAttribute('name', 'friend_id');
  }
  button.innerHTML = '';
  button.appendChild(input2);
  button.appendChild(iconSpan);
  button.innerHTML += 'Remove Friend';

  // If there is a visible notification, remove it.
  acceptFriendRequestNotificationHandler.call(this);

  button.removeEventListener('click', handleAcceptFriendRequestClick);
  button.addEventListener('click', handleRemoveFriendClick);
}

function removeFriendHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

  let button = document.querySelector('.remove-friend');
  button.classList.remove('remove-friend');
  button.classList.add('add-friend');
  let iconSpan = button.querySelector('span');
  iconSpan.innerHTML = 'person_add';
  let input2 = button.querySelector('input[name="friend_id"]');
  button.innerHTML = '';
  button.appendChild(input2);
  button.appendChild(iconSpan);
  button.innerHTML += 'Add friend';

  button.removeEventListener('click', handleRemoveFriendClick);
  button.addEventListener('click', handleAddFriendClick);
}

function acceptFriendRequestNotificationHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

  let notification_id = response.notification_id;
  let notification = document.querySelector('.notification[data-id="' + notification_id + '"]');
  if (notification) {
    notification.remove();
  }

  // Get the friend button on profile page
  let button = document.querySelector(' .accept-friend-request');
  if (button) {
    let check_notification = button.querySelector('input[name="sender_id"]');
    if (check_notification.value == response.sender_id) {
      acceptFriendRequestHandler.call(this);
    }
  }

}

function rejectFriendRequestNotificationHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

  let notification_id = response.notification_id;
  let notification = document.querySelector('.notification[data-id="' + notification_id + '"]');
  if (notification) {
    notification.remove();
  }

  // Maybe it's better to create handler for this
  let button = document.querySelector('.accept-friend-request');
  if (button) {
    let check_notification = button.querySelector('input[name="sender_id"]');
    if (check_notification.value == response.sender_id) {
      button.classList.remove('accept-friend-request');
      button.classList.add('add-friend');

      let iconSpan = button.querySelector('span');
      iconSpan.innerHTML = 'person_add';

      let input2 = button.querySelector('input[name="sender_id"]');
      input2.setAttribute('name', 'friend_id');

      button.innerHTML = '';

      button.appendChild(input2);
      button.appendChild(iconSpan);

      button.innerHTML += 'Add friend';
    }
  }
}


//Hande group requests with event delegation

let joinGroup = document.querySelector('.join-group');
if (joinGroup != null) {
  joinGroup.addEventListener('click', handleJoinGroupRequestClick);
}

let cancelJoinRequest = document.querySelector('.cancel-join-request');
if (cancelJoinRequest != null) {
  cancelJoinRequest.addEventListener('click', handleCancelJoinGroupRequestClick);
}

let acceptJoinGroupRequestNotification = document.querySelector('.accept-join-request-notification');
if (acceptJoinGroupRequestNotification != null) {
  acceptJoinGroupRequestNotification.addEventListener('click', function(e) {
    e.preventDefault();
    handleAcceptJoinGroupRequestNotificationClick(e);
  }
  );
}

let rejectJoinGroupRequestNotification = document.querySelector('.reject-join-request-notification');
if (rejectJoinGroupRequestNotification != null) {
  rejectJoinGroupRequestNotification.addEventListener('click', function(e) {
    e.preventDefault();
    handleRejectJoinGroupRequestNotificationClick(e);
  }
  );
}

let leaveGroup = document.querySelector('.leave-group');
if (leaveGroup != null) {
  leaveGroup.addEventListener('click', handleLeaveGroupClick);
}

let removeMember = document.querySelectorAll('.remove-member');
if (removeMember != null) {
  removeMember.forEach(function(button) {
    button.addEventListener('click', handleRemoveMemberClick);
  }
  );
}

let removeOwner = document.querySelectorAll('.remove-owner');
if (removeOwner != null) {
  removeOwner.forEach(function(button) {
    button.addEventListener('click', handleRemoveOwnerClick);
  }
  );
}

function handleJoinGroupRequestClick(e) {
  let group_id = e.target.closest('.join-group').querySelector('input[name="group_id"]').value;
  let data = { group_id: group_id };
  sendAjaxRequest('POST', '/group/join-request', data, joinGroupRequestHandler);
}

function handleCancelJoinGroupRequestClick(e) {
  let group_id = e.target.closest('.cancel-join-request').querySelector('input[name="group_id"]').value;
  let data = { group_id: group_id };
  sendAjaxRequest('DELETE', '/group/cancel-join-request', data, cancelJoinGroupRequestHandler);
}

function handleAcceptJoinGroupRequestNotificationClick(e) {
  let sender_id = e.target.closest('.accept-join-request-notification').querySelector('input[name="sender_id"]').value;
  let group_id = e.target.closest('.accept-join-request-notification').querySelector('input[name="group_id"]').value;
  let data = { sender_id: sender_id , group_id: group_id};
  sendAjaxRequest('POST', '/group/accept-join-request', data, acceptJoinGroupRequestNotificationHandler);
}

function handleRejectJoinGroupRequestNotificationClick(e) {
  let sender_id = e.target.closest('.reject-join-request-notification').querySelector('input[name="sender_id"]').value;
  let group_id = e.target.closest('.reject-join-request-notification').querySelector('input[name="group_id"]').value;
  let data = { sender_id: sender_id , group_id: group_id};
  sendAjaxRequest('DELETE', '/group/reject-join-request', data, rejectJoinGroupRequestNotificationHandler);
}

function handleLeaveGroupClick(e) {
  let group_id = e.target.closest('.leave-group').querySelector('input[name="group_id"]').value;
  let data = { group_id: group_id };
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    background: '#232a37',
    color: '#fff',
    showCancelButton: true,
    confirmButtonText: 'Proceed',
    confirmButtonColor: '#663FA4',
    cancelButtonText: 'Cancel',
    cancelButtonColor: '#151b26'
  }).then((result) => {
    if (result.isConfirmed) {
    sendAjaxRequest('DELETE', '/group/leave', data, leaveGroupHandler);
    }
  })}
;

function handleRemoveMemberClick(e) {
  let user_id = e.target.closest('.remove-member').querySelector('input[name="user_id"]').value;
  let group_id = e.target.closest('.remove-member').querySelector('input[name="group_id"]').value;
  let data = { user_id: user_id, group_id: group_id };
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    background: '#232a37',
    color: '#fff',
    showCancelButton: true,
    confirmButtonText: 'Proceed',
    confirmButtonColor: '#663FA4',
    cancelButtonText: 'Cancel',
    cancelButtonColor: '#151b26'
  }).then((result) => {
    if (result.isConfirmed) {
    sendAjaxRequest('DELETE', '/group/removeMember', data, removeMemberHandler);
    }
  })}
;

function handleRemoveOwnerClick(e) {
  let user_id = e.target.closest('.remove-owner').querySelector('input[name="user_id"]').value;
  let group_id = e.target.closest('.remove-owner').querySelector('input[name="group_id"]').value;
  let data = { user_id: user_id, group_id: group_id };
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    background: '#232a37',
    color: '#fff',
    showCancelButton: true,
    confirmButtonText: 'Proceed',
    confirmButtonColor: '#663FA4',
    cancelButtonText: 'Cancel',
    cancelButtonColor: '#151b26'
  }).then((result) => {
    if (result.isConfirmed) {
    sendAjaxRequest('DELETE', '/group/removeOwner', data, removeOwnerHandler);
    }
  })}
;

function joinGroupRequestHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

  let button = document.querySelector('.join-group');
  button.classList.remove('join-group');
  button.classList.add('cancel-join-request');
  let iconSpan = button.querySelector('span');
  iconSpan.innerHTML = 'done';
  let input2 = button.querySelector('input[name="group_id"]');
  button.innerHTML = '';
  button.appendChild(input2);
  button.appendChild(iconSpan);
  button.innerHTML += 'Request sent';

  button.removeEventListener('click', handleJoinGroupRequestClick);
  button.addEventListener('click', handleCancelJoinGroupRequestClick);
}

function cancelJoinGroupRequestHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

  let button = document.querySelector('.cancel-join-request');
  button.classList.remove('cancel-join-request');
  button.classList.add('join-group');
  let iconSpan = button.querySelector('span');
  iconSpan.innerHTML = 'person_add';
  let input2 = button.querySelector('input[name="group_id"]');
  button.innerHTML = '';
  button.appendChild(input2);
  button.appendChild(iconSpan);
  button.innerHTML += 'Join Group';

  button.removeEventListener('click', handleCancelJoinGroupRequestClick);
  button.addEventListener('click', handleJoinGroupRequestClick);
}

function acceptJoinGroupRequestNotificationHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

  let notification_id = response.notification_id;
  let notification = document.querySelector('.notification[data-id="' + notification_id + '"]');
  if (notification) {
    notification.remove();
  }
}

function rejectJoinGroupRequestNotificationHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

  let notification_id = response.notification_id;
  let notification = document.querySelector('.notification[data-id="' + notification_id + '"]');
  if (notification) {
    notification.remove();
  }
}

function leaveGroupHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

  let button = document.querySelector('.leave-group');
  button.classList.remove('leave-group');
  button.classList.add('join-group');
  let iconSpan = button.querySelector('span');
  iconSpan.innerHTML = 'person_add';
  let input2 = button.querySelector('input[name="group_id"]');
  button.innerHTML = '';
  button.appendChild(input2);
  button.appendChild(iconSpan);
  button.innerHTML += 'Join Group';

  //also find the edit-group button and remove it
  let editGroupButton = document.querySelector('.edit-group');
  if (editGroupButton) {
    editGroupButton.remove();
  }

  button.removeEventListener('click', handleLeaveGroupClick);
  button.addEventListener('click', handleJoinGroupRequestClick);
}


function removeMemberHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

  //remove the user-card
  let user_id = response.user_id;
  let userCard = document.querySelector('.user-card[data-id="' + user_id + '"]');
  if (userCard) {
    userCard.remove();
  }
}

function removeOwnerHandler() {
  let response = JSON.parse(this.responseText);
  if (response == null || response.success == false) return;

  //remove the user-card
  let user_id = response.user_id;
  let userCard = document.querySelector('.user-card[data-id="' + user_id + '"]');
  if (userCard) {
    userCard.remove();
  }
}

// drop down menu

document.querySelectorAll('.dropbtn').forEach(dropbtn => {
  dropbtn.onclick = function() {
      this.nextElementSibling.classList.toggle("show");
  }
});

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


if (window.location.hash) {
  let hash = window.location.hash.substring(1).split('-')[1];
  let scrollContainer = document.querySelector('#content');
  let comment = document.querySelector('.comment[data-id="' + hash + '"]');

  if (comment) {
    scrollContainer.scrollTop = comment.offsetTop;
    comment.classList.add('highlight');
    setTimeout(function() {
      comment.classList.remove('highlight');
    }, 1000);

  }
}


let banButton = document.querySelector('.ban-user'); 
if (banButton != null) {
  banButton.addEventListener('click', function(e) {
    e.preventDefault();
    let username = document.querySelector('.username').innerHTML.split('@')[1];
    let data = {username: username};
    sendAjaxRequest('POST', '/admin/' + username + '/ban', data, banUserHandler);
    }
  );
}

function banUserHandler() {
  let response = JSON.parse(this.responseText);
  if (response.success == false) return;

  let span = document.createElement('span');
  span.className = 'material-symbols-outlined';
  span.innerHTML = 'person_add_disabled';

  let button = document.querySelector('.ban-user');
  button.addEventListener('click', function(e) {
    e.preventDefault();
    let username = document.querySelector('.username').innerHTML.split('@')[1];
    let data = {username: username};
    sendAjaxRequest('POST', '/admin/' + username + '/unban', data, unbanUserHandler);
    }
  );
  button.innerHTML = '';
  button.appendChild(span);
  button.innerHTML += 'Unban';
  button.setAttribute('href', '/admin/' + response.username + '/unban');
  button.classList.remove('ban-user');
  button.classList.add('unban-user');
}


let unbanButton = document.querySelector('.unban-user');
if (unbanButton != null) {
  unbanButton.addEventListener('click', function(e) {
    e.preventDefault();
    let username = document.querySelector('.username').innerHTML.split('@')[1];
    let data = {username: username};
    sendAjaxRequest('POST', '/admin/' + username + '/unban', data, unbanUserHandler);
    }
  );
};

function unbanUserHandler() {
  let response = JSON.parse(this.responseText);
  if (response.success == false) return;

  let span = document.createElement('span');
  span.className = 'material-symbols-outlined';
  span.innerHTML = 'block';

  let button = document.querySelector('.unban-user');

  button.addEventListener('click', function(e) {
    e.preventDefault();
    let username = document.querySelector('.username').innerHTML.split('@')[1];
    let data = {username: username};
    sendAjaxRequest('POST', '/admin/' + username + '/ban', data, banUserHandler);
    }
  );

  button.innerHTML = '';
  button.appendChild(span);
  button.innerHTML += 'Ban';
  button.setAttribute('href', '/admin/' + response.username + '/ban');
  button.classList.remove('unban-user');
  button.classList.add('ban-user');
}



// click on notification marks it as viewed
let notifications = document.querySelectorAll('.notification.active');

if (notifications != null) {
  notifications.forEach(function(notification) {
    notification.addEventListener('click', function(e) {
      let id = notification.getAttribute('data-id');
      let data = {notification_id: id};
      sendAjaxRequest('PUT', '/notifications/markAsRead', data, markAsReadHandler);
      }
    );
  }
  );
}

function markAsReadHandler() {
  let response = JSON.parse(this.responseText);
  if (response.success == false) return;

  let notification = document.querySelector('.notification[data-id="' + response.id + '"]');
  
  if (notification) {
    notification.classList.remove('active');
    notification.querySelector('.notification-checkbox').checked = false;
  }
}


let markAllAsRead = document.querySelector('.mark-as-read');

if (markAllAsRead != null) {
  markAllAsRead.addEventListener('click', function(e) {
    e.preventDefault();
    sendAjaxRequest('PUT', '/notifications/markAllAsRead', {}, markAllAsReadHandler);
    }
  );
}


function markAllAsReadHandler() {
  let response = JSON.parse(this.responseText);
  if (response.success == false) return;

  let notifications = document.querySelectorAll('.notification.active');
  notifications.forEach(function(notification) {
    notification.classList.remove('active');
  }
  );

  let markAllAsRead = document.querySelector('.mark-as-read');
  markAllAsRead.blur();

  let newNotifications = document.querySelector('.new-notification');
  if (newNotifications != null) newNotifications.remove();
}


let hideNotifications = document.querySelector('#notifications');

if (hideNotifications != null) {
  hideNotifications.addEventListener('click', function(e) {
    e.preventDefault();
    let notifications = document.querySelector('.notifications');
    let icon = hideNotifications.querySelector('span');
    if (notifications != null) {
      if (notifications.style.display == 'none') {
        notifications.style.display = 'flex';
        icon.innerHTML = 'arrow_drop_down';
      }
      else {
        notifications.style.display = 'none';
        icon.innerHTML = 'arrow_right';
      }
    }
    hideNotifications.blur();
    }
  );
}
    

let searchTabs = document.querySelectorAll('.search-tabs a');

if (searchTabs != null) {
  searchTabs.forEach(function(tab) {
    if (tab.href == window.location.href) {
      tab.classList.add('active');
    }
  }
  );
}



let searchDateFilter = document.querySelector('.search-sort select[name="date"]');
if (searchDateFilter != null) {
  searchDateFilter.addEventListener('change', function() {
    let url = window.location.href;
    
    // check if url already has date filter
    let index = url.indexOf('&date=');
    if (index != -1) {
      url = url.substring(0, index);
    }

    // add date filter to url
    url += '&date=' + this.value;

    window.location = url;

  }
  );
}

let searchPostTypeFilter = document.querySelector('.search-sort select[name="popularity"]');
if (searchPostTypeFilter != null) {
  searchPostTypeFilter.addEventListener('change', function() {
    let url = window.location.href;
    
    // check if url already has date filter
    let index = url.indexOf('&popularity=');
    if (index != -1) {
      url = url.substring(0, index);
    }

    // add date filter to url
    url += '&popularity=' + this.value;

    window.location = url;

  }
  );
}

let helpIcons = document.querySelectorAll('.help-icon');
  console.log(helpIcons)
  if (helpIcons != null) {
    helpIcons.forEach(function(icon) {
      icon.addEventListener('mouseover', function() {
         let tooltip = icon.nextElementSibling;
         console.log(tooltip)
         tooltip.style.display = 'block';
      });

      icon.addEventListener('mouseout', function() {
        let tooltip = icon.nextElementSibling;
        tooltip.style.display = 'none';
        }
      );
    }
  );
}



let nextPostsButton = document.querySelector('.posts-pagination nav :nth-child(2)');

if (nextPostsButton != null) {
  let pageHref = nextPostsButton.href;

  if (pageHref.startsWith('http://')) {
    // Replace 'http://' with '//'
    pageHref = pageHref.replace('http://', '//');
  }
  // Update the href attribute
  nextPostsButton.href = pageHref;

  let scrollContainer = document.querySelector('#posts');
  let loading = document.querySelector('.loader');
  if (pageHref != null) {
    scrollContainer.addEventListener('scroll', function() {
      if (scrollContainer.scrollTop + scrollContainer.clientHeight >= scrollContainer.scrollHeight && pageHref != null) {
        loading.style.display = 'block';
        setTimeout(function() {
        fetch(pageHref)
        .then(response => response.text())
        .then(data => {
          let parser = new DOMParser();
          let doc = parser.parseFromString(data, 'text/html');  
          let posts = doc.querySelectorAll('#posts article.post');
          let postsContainer = document.querySelector('#posts');

          loading.style.display = 'none';
          posts.forEach(function(post) {
            // Don't add duplicate posts due to pagination bug
            let duplicate = postsContainer.querySelector('.post[data-id="' + post.getAttribute('data-id') + '"]');
            if (duplicate) return;
            postSetAllEventListeners(post);
            postsContainer.appendChild(post);
          }
          );
          let newPageHref = doc.querySelector('.posts-pagination nav :nth-child(2)').href;
          if (newPageHref) {
            if (newPageHref.startsWith('http://')) {
              // Replace 'http://' with '//'
              newPageHref = newPageHref.replace('http://', '//');
            }
            pageHref = newPageHref;
          } else {
            pageHref = null;
          }
        });
      }, 600);
      }
    });
  }
}

function postSetAllEventListeners(post) {
  let likeButton = post.querySelector('.post-actions .post-action:first-child');
  let commentButton = post.querySelector('.post-actions .post-action:nth-child(2)');
  let postCommentForm = post.querySelector('form.comment-box');
  let deletePost = post.querySelector('.post-header-right span:last-child');
  let editPostButton = post.querySelector('.post-header-right span:first-child');
  let comments = post.querySelectorAll('.comment');

  if (likeButton != null) {
    likeButton.addEventListener('click', function(e) {
      let id = e.target.closest('.post').getAttribute('data-id');
      let data = {post_id: id};
      sendAjaxRequest('POST', '/posts/like', data, likePostHandler);
      }
    );
  }

  if (commentButton != null) {
    commentButton.addEventListener('click', commentButtonHandler);
  }


  if (postCommentForm != null) {
    postCommentForm.addEventListener('submit', commentPostClickHandler);
  }

  if (deletePost != null) {
    deletePost.addEventListener('click', postDeleteClickHandler);
  }

  if (editPostButton != null) {
    editPostButton.addEventListener('click', function(e) {
      let id = e.target.parentNode.parentNode.parentNode.getAttribute('data-id');
      editPost(id);
      }
    );
  }

  // Comment related event listeners (like, edit, delete, reply)
  let likeCommentButtons = post.querySelectorAll('.comment .comment-stat');
  let editCommentButtons = post.querySelectorAll('.comment .comment-actions .edit-comment');
  let deleteCommentButtons = post.querySelectorAll('.comment .comment-actions .delete-comment');
  let replyCommentForms = post.querySelectorAll('.comment .comment-box');
  let replyCommentButtons = post.querySelectorAll('.comment .comment-actions .reply-comment');

  if (likeCommentButtons != null) {
    likeCommentButtons.forEach(function(button) {
      button.addEventListener('click', commentLikeClickHandler);
    }
    );
  }

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

  if (deleteCommentButtons != null) {
    deleteCommentButtons.forEach(function(button) {
      button.addEventListener('click', deleteCommentClickHandler);
    }
    );
  }

  if (replyCommentForms != null) {
    replyCommentForms.forEach(function(form) {
      form.addEventListener('submit', replyCommentFormHandler);
    }
    );
  }

  if (replyCommentButtons != null) {
    replyCommentButtons.forEach(function(button) {
      button.addEventListener('click', replyCommentClickHandler);
    }
    );
  }
}

function commentSetAllEventListeners(comment) {
  let likeButton = comment.querySelector('.comment-stat');
  let editButton = comment.querySelector('.comment-actions .edit-comment');
  let deleteButton = comment.querySelector('.comment-actions .delete-comment');
  let replyButton = comment.querySelector('.comment-actions .reply-comment');
  let replyForm = comment.querySelector('.comment-box');

  if (likeButton != null) {
    likeButton.addEventListener('click', commentLikeClickHandler);
  }

  if (editButton != null) {
    editButton.addEventListener('click', function(e) {
      let id = e.target.closest('.comment').getAttribute('data-id');
      editComment(id);
      }
    );
  }
  
  if (deleteButton != null) {
    deleteButton.addEventListener('click', deleteCommentClickHandler);
  }

  if (replyButton != null) {
    replyButton.addEventListener('click', replyCommentClickHandler);
  }

  if (replyForm != null) {
    replyForm.addEventListener('submit', replyCommentFormHandler);
  }  
}




