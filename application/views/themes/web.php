<html lang="en">

<head>
  <title><?php echo $title; ?></title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="<?php echo base_url('favicon.ico'); ?>">

  <!-- Meta Tags -->
  <meta name="description"
    content="Bio-X Touchless Attendance& is A Mobile Base Application Free To Use As The Contribution Of Excelsoft To The World." />
  <meta name="keywords" content="Bio-X Touchless Attendance, Attendance, Technology, Touchless Apps, QR Code" />
  <meta name="author" content="PT Excelsoft Technology" />
  <meta name="date" content="<?php echo $date ?>" />
  <meta name="last-modified" content="<?php echo $date ?>" />
  <meta http-equiv="last-modified" content="<?php echo $date ?>" />

  <meta property="og:type" content="product" />
  <meta property="og:url" content="<?php echo $url ?>" />
  <meta property="og:title" content="<?php echo $title ?>" />
  <meta property="og:description"
    content="Bio-X Touchless Attendance& is A Mobile Base Application Free To Use As The Contribution Of Excelsoft To The World." />
  <meta property="og:image" content="<?php echo base_url('assets/vendor/theme/images/logo_biox.png');?>" />

  <?php
      foreach($css as $file){
        echo "\n\t\t";
        ?>
  <link rel="stylesheet" href="<?php echo $file; ?>" type="text/css" /><?php
      } echo "\n\t";
    ?>

  <?php
      foreach($js as $file){
        echo "\n\t\t";
        ?><script src="<?php echo $file; ?>"></script><?php
      } echo "\n\t";
    ?>
</head>

<body data-gr-c-s-loaded="true" class="loaded text-center">
  <div id="loader-wrapper">
    <div id="loader"></div>
    <div class="loader-section section-left"></div>
    <div class="loader-section section-right"></div>
  </div>
  <header id="header" class="header_app">
    <div class="header-top">
      <div class="sassnex_nav">
        <div class="container">
          <nav class="navbar navbar-expand-md navbar-light bg-faded">
            <a class="navbar-brand" href="<?php echo base_url('/');?>"><strong class="white">Insta App</strong></a>
            <div class="collapse navbar-collapse mean_menu" id="navbarSupportedContent" style="display: block;">
              <ul class="navbar-nav nav ml-auto">
                <li class="nav-item">
                  <a href="<?php echo base_url('/'); ?>"
                    class="nav-link <?php echo ($this->uri->segment(1) == 'web' || $this->uri->segment(1) == '') && $this->uri->segment(2) == '' ? 'active' : ''; ?>">Home</a>
                </li>
                <?php if (!$this->session->userdata('login')) { ?>
                <li>
                  <a href="<?php echo base_url('auth/register'); ?>" class="nav-link">Register</a>
                </li>
                <li class="nav-item">
                  <a href="<?php echo base_url('auth/login'); ?>" class="nav-link">Login</a>
                </li>
                <?php } else { ?>
                <li>
                  <a href="<?php echo base_url('apps'); ?>" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                  <a href="<?php echo base_url('auth/logout'); ?>" class="nav-link">Logout</a>
                </li>
                <?php } ?>
              </ul>
            </div>
          </nav><!-- END NAVBAR -->
        </div>
      </div>
    </div>
  </header> <!-- End Header -->

  <div class="main">
    <?php echo $output;?>
  </div>

  <footer class="mastfoot mt-auto pt-4 pb-4">
    <div class="inner">
      <p class="">Insta App <a href="" class=" no-underline">@2021</a>.</p>
    </div>
  </footer>

  <section id="scroll-top" class="scroll-top">
    <div class="to-top">
      <a href="#"><i class="fa fa-fw fa-arrow-right" style="margin-top: 18px;"></i></a>
    </div>
  </section>
  <script src="<?php echo base_url('assets/vendor/theme/js/owl.carousel.min.js');?>"></script>
</body>

</html>