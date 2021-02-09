<div class="sassnex-bc">
  <div class="intro_wrapper">
    <div class="container">
      <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
          <div class="intro_text">
            <h1 class="wow fadeInUp" data-wow-duration="2s" data-wow-delay=".2s">Sign Up</h1>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="banner_shapes">
    <img src="<?php echo base_url('assets/vendor/theme/images/shapes/app_1.png');?>" alt="" class="agency_1 img-fluid">
    <img src="<?php echo base_url('assets/vendor/theme/images/shapes/app_4.png');?>" alt="" class="agency_2">
    <img src="<?php echo base_url('assets/vendor/theme/images/shapes/app_4.png');?>" alt="" class="agency_6">
    <img src="<?php echo base_url('assets/vendor/theme/images/shapes/app_3.png');?>" alt="" class="agency_3">
  </div>
</div>

<section class="sign_in">
  <div class="container text-left">
    <div class="row">
      <div class="col-12 col-md-6 col-lg-5">
        <div class="sign_in_form">
          <div class="form_title">
            <h2>Sign Up</h2>
          </div>

          <?php if (!empty($this->session->message)) { ?>
          <div class="alert alert-<?php echo $this->session->message_status; ?>" role="alert">
            <?php echo $this->session->message; ?>
          </div>
          <?php 
              $session_data = array('message'   => '','message_status'   => '');
              $this->session->set_userdata($session_data);
            ?>
          <?php } ?>

          <form id="regForm" method="post" action="<?php echo base_url('auth/register'); ?>">
            <div class="tab">
              <p>Account Section</p>
              <div class="form-label-group mb-3">
                <input name="username" type="text" class="form-control required" placeholder="Username" required>
              </div>
              <div class="form-label-group mb-3">
                <input name="password" type="password" id="password1" class="form-control required"
                  placeholder="Password" required="">
              </div>
              <div class="form-label-group mb-3">
                <input name="password-re-entered" id="password2" onkeyup="validate()" type="password"
                  class="form-control required" placeholder="Re entered your password" required="">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="register_option">
                  <button class="btn btn-sm login_btn float-left" type="submit">Submit</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-7">
        <div class="vission_banner">
          <img src="<?php echo base_url('assets/vendor/theme/images/illustration/login.png');?>" alt="">
        </div>
      </div>
    </div>
  </div>
</section>

<script>
function validate() {
  if ($("#password1").val() === $("#password2").val()) {
    $("#nextBtn").prop('disabled', false);
    $("#prevBtn").prop('disabled', true);
  } else {
    if ($("#nextBtn").html() == 'Register') {
      $("#nextBtn").prop('disabled', true);
      $("#prevBtn").prop('disabled', true);
    } else {
      $("#nextBtn").prop('disabled', false);
    }
  }
}
</script>