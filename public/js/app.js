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

  preview.addEventListener('click', function(e) {
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
  );


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

}
);

