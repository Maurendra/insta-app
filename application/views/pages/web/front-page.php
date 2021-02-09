<div class="sassnex-bc">
  <div class="intro_wrapper pt-6 pb-0">
    <div class="container">
    </div>
  </div>
</div>

<section class="pricing_payment pt-5" id="pricing_payment_inner">
  <div class="container text-center">
    <div>
      <?php foreach ($posts as $key => $post) { ?>
      <div class="post-container rounded border border-warning pointer mb-4 pt-2 pb-2 pl-2 pr-2">
        <div class="post-header">
          <h4 class="text-left"><?php echo $post['username'] ?></h4>
        </div>
        <hr>
        <div class="post-body d-flex justify-content-center" onclick='post(<?php echo json_encode($post); ?>)'>
          <div class="post"
            style="background-image: url(<?php echo base_url("photos/post/".$post['user_id']."/".$post['file_name']) ?>);">

          </div>
        </div>
        <hr class="mb-1">
        <div class="post-footer pl-2">
          <div class="d-flex mb-3">
            <div class="mr-2 d-flex">
              <span><?php echo $post['count_like'] ?>
                <?php echo $post['is_liked'] ? '<i onclick="unlike('.$post['id'].')" class="fas fa-heart icon-heart pointer"></i>' : '<i onclick="like('.$post['id'].')" class="far fa-heart pointer"></i>' ?>
              </span>
            </div>
            <div class="d-flex">
              <span><?php echo $post['count_comment'] ?> <i class="far fa-comment-dots"></i></span>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
</section>

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
                <form action="<?php echo base_url("web/addComment") ?>" method="post" id="form-add-comment">
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
var globalUrl = "<?php echo base_url("photos/post/") ?>";
var uId = '<?php echo $this->session->userdata('id') ?>';

function post(object) {
  $('#modalPost').modal('toggle');
  $("#img-detail").css("background-image", "url('" + globalUrl + object.user_id + '/' +
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
  if (uId == '') {
    window.location.href = '<?php echo base_url("auth/login/") ?>';
  } else {
    window.location.href = '<?php echo base_url("web/like/") ?>' + object;
  }
}

function unlike(object) {
  if (uId == '') {
    window.location.href = '<?php echo base_url("auth/login/") ?>';
  } else {
    window.location.href = '<?php echo base_url("web/unlike/") ?>' + object;
  }
}

function submitComment() {
  if (uId == '') {
    window.location.href = '<?php echo base_url("auth/login/") ?>';
  } else {
    document.getElementById("form-add-comment").submit();
  }
}
</script>