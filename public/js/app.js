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

let likeButtons = document.querySelectorAll('article.post .post-actions .post-action:first-child');

if (likeButtons != null) {
  likeButtons.forEach(function(button) {
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


// show comment box when user clicks on comment button

let commentButtons = document.querySelectorAll('article.post .post-actions .post-action:nth-child(2)');

if (commentButtons != null) {
  commentButtons.forEach(function(button) {
    button.addEventListener('click', function(e) {
      let id = e.target.closest('article.post').getAttribute('data-id');
      let commentBox = document.querySelector('.post[data-id="' + id + '"] .comment-box');
    
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
