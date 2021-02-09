<div class="sassnex-bc">
  <div class="intro_wrapper">
    <div class="container">
      <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
          <div class="intro_text">
            <h1 class="wow fadeInUp" data-wow-duration="2s" data-wow-delay=".2s">Login</h1>
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
            <h2>Log In</h2>
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

          <form method="post" action="<?php echo base_url('auth/login'); ?>">
            <div class="row">
              <div class="col-12 col-lg-12 col-md-12 col-lg-12">
                <div class="form-group">
                  <label class="control-label">Username</label>
                  <input type="text" class="form-control" placeholder="Username" name="username" required>
                </div>
              </div>
              <div class="col-12 col-lg-12 col-md-12 col-lg-12">
                <div class="form-group">
                  <label class="control-label">Password</label>
                  <input type="password" class="form-control" placeholder="Password" name="password" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12 col-lg-12 col-md-12 col-lg-12">
                <div class="login_option">
                  <button type="submit" class="btn btn-default login_btn">Log In</button><br>
                  <span>New User?<a href="<?php echo base_url('auth/register'); ?>" title="" class="forget_pass">Sign
                      Up</a></span>
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