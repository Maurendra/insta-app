<div class="sassnex-bc">
  <div class="intro_wrapper pt-6 pb-0">
    <div class="container">
    </div>
  </div>
</div>

<section class="pricing_payment pt-5" id="pricing_payment_inner">
  <div class="container text-left">
    <div>
      <div class="row">
        <div class="col-md-6">
          <h2><?php echo $user['username'] ?></h2>
        </div>
      </div>
      <div class="mb-3">
        <h5><?php echo count($posts) ?> posts</h5>
      </div>
      <div>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAddPost">
          Create a new post
        </button>
      </div>
    </div>
    <hr>
    <div>
      <div class="row text-center">
        <?php foreach ($posts as $key => $post) { ?>
        <div class="col-md-4 pl-2 pr-2 pt-3 pb-3 pointer" onclick='post(<?php echo json_encode($post); ?>)'>
          <div class="post"
            style="background-image: url(<?php echo base_url("photos/post/".$this->session->userdata('id')."/".$post['file_name']) ?>);">

          </div>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>
</section>

<!-- Modal Add -->
<div class="modal fade" id="modalAddPost" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Post</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?php echo base_url("Apps/addPost")?>" method="post" id="form-add-post"
        enctype="multipart/form-data">
        <div class="modal-body text-left">
          <div class="form-group text-center">
            <label class="form-label">Select a photo</label>
            <div class="imgUp">
              <div class="imagePreview mb-2" style="display: none;"></div>
              <br>
              <label class="btn btn-primary btn-sm" id="label-upload">
                Choose File<input name="photo" type="file" class="uploadFile img" value="Upload Photo"
                  style="width: 0px;height: 0px;overflow: hidden;">
              </label>
            </div>
          </div>
          <div class="form-group">
            <label for="">Title</label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="">Description</label>
            <textarea name="description" cols="70" rows=5 class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cs1 btn-dark" data-dismiss="modal">Tutup</button>
          <button type="submit" onclick="submit()" class="btn btn-cs1 btn-primary">Kirim</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Post -->
<div class="modal fade" id="modalPost" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-left">
        <div class="row">
          <div class="col-md-7">
            <div id="img-detail" class="post">

            </div>
          </div>
          <div class="col-md-5">
            <div>
              <p id="title-detail" class="mb-1 text-dark"></p>
              <p id="description-detail" class="mb-1 text-muted"></p>
            </div>
            <hr>
            <div>
              <div>
                <div class="d-flex mb-3">
                  <div class="mr-2 d-flex">
                    <span id="count-like-id">2 <i class="far fa-heart"></i></span>
                  </div>
                  <div class="d-flex">
                    <span id="count-comment-id">2 <i class="far fa-comment-dots"></i></span>
                  </div>
                </div>
                <div class="scrollable">
                  <div id="list-comment">
                  </div>
                </div>
              </div>
              <div class="d-flex mt-3">
                <form action="<?php echo base_url("apps/addComment") ?>" method="post" id="form-add-comment">
                  <input id="detail-user-id" type="text" name="user-id" value="0" hidden>
                  <input id="detail-post-id" type="text" name="post-id" value="0" hidden>
                  <input class="float-left form-control form-control-sm" style="width: 75%;" type="text" name="comment"
                    placeholder="....">
                  <button type="submit" class="float-right btn btn-sm btn-dark" onclick="submitComment()">Kirim</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
var globalUrl = "<?php echo base_url("photos/post/".$this->session->userdata('id')."/") ?>";
var uId = <?php echo $this->session->userdata('id') ?>;

function post(object) {
  console.log(object);
  console.log(uId);
  console.log(object.id);
  $('#modalPost').modal('toggle');
  $("#img-detail").css("background-image", "url('" + globalUrl +
    object.file_name + "')");
  $('#title-detail').html(object.title);
  $('#description-detail').html(object.description);
  $('#detail-user-id').val(uId);
  $('#detail-post-id').val(object.id);
  $('#count-comment-id').html(object.count_comment + ' <i class="far fa-comment-dots"></i>');

  // Check have been liked or not by user
  if (object.count_like >= 1) {
    var isAny = false;
    var likes = object.like;
    likes.forEach(element => {
      if (element.user_id == uId) {
        isAny = true;
      }
    });

    if (isAny == true) {
      $('#count-like-id').html(object.count_like + ' <i onclick="unlike(' + object.id +
        ')" class="fas fa-heart icon-heart pointer"></i>');
    } else {
      $('#count-like-id').html(object.count_like + ' <i onclick="like(' + object.id +
        ')" class="far fa-heart pointer"></i>');
    }
  } else {
    $('#count-like-id').html(object.count_like + ' <i onclick="like(' + object.id +
      ')" class="far fa-heart pointer"></i>');
  }

  var comments = object.comment;
  document.getElementById("list-comment").innerHTML = "";

  comments.forEach(element => {
    document.getElementById("list-comment").innerHTML +=
      '<h6>' + element.username + '</h6><p class="text-sm mb-0">' + element.comment + '</p><hr class="mb-1 mt-1">';
  });
}

function like(object) {
  window.location.href = '<?php echo base_url("apps/like/") ?>' + object;
}

function unlike(object) {
  window.location.href = '<?php echo base_url("apps/unlike/") ?>' + object;
}

function submit() {
  document.getElementById("form-add-post").submit();
}

function submitComment() {
  document.getElementById("form-add-comment").submit();
}

$(function() {
  $(document).on("change", ".uploadFile", function() {
    var uploadFile = $(this);
    console.log(uploadFile);
    var files = !!this.files ? this.files : [];
    if (!files.length || !window.FileReader) return;

    if (/^image/.test(files[0].type)) {
      var reader = new FileReader();
      reader.readAsDataURL(files[0]);

      reader.onloadend = function() {
        $("#address-image-id").addClass("text-center");
        uploadFile.closest(".imgUp").find('.imagePreview').css({
          "background-image": "url(" + this.result + ")",
          "display": "inline-block"
        });
        $('#label-upload').contents().first()[0].textContent = 'Change Image';
      }
    }

  });
});
</script>