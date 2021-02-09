<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Email Verification</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url();?>assets/vendor/bootstrap-4.4.1-dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo base_url();?>assets/css/signin.css" rel="stylesheet">
  </head>

  <body>
    <div class="container">
	<?php if(!empty($status) && $status=='error'){ ?>
		<div class="alert alert-danger" role="alert">
		  <h4 class="alert-heading">Ooooppss!</h4>
		  <p><?php echo $message; ?></p>
		</div>
	<?php }elseif(!empty($status) && $status=='success'){ ?>
		<div class="alert alert-success" role="alert">
		  <h4 class="alert-heading">Well done!</h4>
		  <p><?php echo $message; ?></p>
		</div>
	<?php } ?>
    </div>
    <script src="<?php echo base_url();?>assets/js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>
