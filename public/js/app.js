// make click on attach button open input file not jquery

// on dom ready

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


});