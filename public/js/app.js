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
  if (this.status != 200) window.location = '/';
  let item = JSON.parse(this.responseText);
  let element = document.querySelector('.post[data-id="' + item.id + '"]');
  element.remove();
}



document.addEventListener('DOMContentLoaded', function() {

  let button = document.querySelector('#attach-button');

  button.addEventListener('click', function() {
      document.querySelector('input[type="file"]').click();
  }
  );

  // if there are files, show them in preview div .list-files-preview

  let input = document.querySelector('input[type="file"]');
  let preview = document.querySelector('.files-list-preview');

  input.addEventListener('change', function() {
    let files = input.files;
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
        i++;
      }
      reader.readAsDataURL(file);
    });
  }
  );

  // remove file from preview and input on click on close button

  preview.addEventListener('click', remove_file_from_preview);
  


  // when user clicks on delete button, perform ajax request to delete post
  let postDeleteButtons = document.querySelectorAll('.post-header-right span:last-child');
  postDeleteButtons.forEach(function(button) {
    button.addEventListener('click', function(e) {
      let id = e.target.parentNode.parentNode.parentNode.getAttribute('data-id');
      let data = {post_id: id};
      sendAjaxRequest('DELETE', '/posts/delete', data, postDeletedHandler);
      }
    );
  }
  );


  // when user clicks on edit button, replace article with create_post div
  let postEditButtons = document.querySelectorAll('.post-header-right span:first-child');
  postEditButtons.forEach(function(button) {
    button.addEventListener('click', function(e) {
      let id = e.target.parentNode.parentNode.parentNode.getAttribute('data-id');
      editPost(id);
      }
    );
  }
  );


  // when user clicks on edit button, perform ajax request to edit post
  let editButtons = document.querySelectorAll('.edit-button');
  // get the files

}
);

function filterContent(content) {
  return content.replace(/(<([^>]+)>)/ig, ''); // remove html tags
}

function editPost(id) {
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

  let post_files = document.createElement('div');
  post_files.className = 'post-files';
  post_files.setAttribute('id', 'attach-button');

  let input = document.createElement('input');
  input.setAttribute('type', 'file');
  input.setAttribute('name', 'files[]');
  input.setAttribute('multiple', 'multiple');
  input.style.display = 'none';
  post_files.appendChild(input);

  /* MISSING GETTING FILES FROM POST AND PUT THEM IN INPUT */

  let files_list_preview = document.createElement('div');
  files_list_preview.className = 'files-list-preview';
  files_list_preview.style.display = 'flex';

  // create as many file-preview divs as there are files
  if (files != null) {
    files.forEach(function(file) {
      let div = document.createElement('div');
      div.className = 'file-preview';
      let span = document.createElement('span');
      span.addEventListener('click', remove_file_from_preview);
      span.className = 'material-symbols-outlined';
      span.innerHTML = 'close';
      div.appendChild(span);
      let a = document.createElement('a');
      let img = file;
      a.appendChild(img);
      div.appendChild(a);
      files_list_preview.appendChild(div);
    });
  }

  /* */


  /* Elements to append */
  let textarea = document.createElement('textarea');
  textarea.className = 'post-textarea';
  textarea.setAttribute('cols', '25');
  textarea.setAttribute('rows', '5');

  textarea.value = (content == null) ? '' : filterContent(content.innerHTML);

  let button = document.createElement('button');
  button.className = 'edit-button';
  button.innerHTML = 'Edit';
  post_text.appendChild(textarea);
  post_text.appendChild(button);

  post_header.appendChild(profile_picture);
  post_header.appendChild(post_text);

  post_header.appendChild(post_files);
  post_files.innerHTML = "<span class='material-symbols-outlined'>attach_file</span>";

  create_post.appendChild(post_header);
  create_post.appendChild(files_list_preview);


  // replace article with create_post div
  post.replaceWith(create_post);

}


function remove_file_from_preview(e) {
  if (e.target.tagName == 'SPAN') {
    let id = e.target.parentNode.id;
    let file = document.querySelector('input[type="file"]');
    let files = file.files;
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