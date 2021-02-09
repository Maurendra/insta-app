<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Change Password</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url();?>assets/vendor/bootstrap-4.4.1-dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo base_url();?>assets/css/signin.css" rel="stylesheet">
  </head>

  <body>
    <div class="container">
	<?php if(!empty($invalid)){ ?>
		<div class="alert alert-danger" role="alert">
		  <h4 class="alert-heading">This link invalid!</h4>
		  <p>Your link invalid because has been expired or already use for change password. Please get link again from app.</p>
		</div>
	<?php }elseif(!empty($success)){ ?>
		<div class="alert alert-success" role="alert">
		  <h4 class="alert-heading">Well done!</h4>
		  <p>Your password has been reset.</p>
		</div>
	<?php }else{ ?>
      <form class="form-signin" action="" method="post">
        <h4 class="form-signin-heading">Please Enter New Pasword</h4>
		<?php if(!empty($message)){ ?>
		<div class="alert alert-danger" role="alert">
		  <?php echo $message; ?>
		</div>
		<?php } ?>
        <label for="inputEmail" class="sr-only">Password</label>
        <input name="password" type="password" id="inputPassword" class="form-control" placeholder="Password" required autofocus>
        <label for="inputPassword" class="sr-only">Repeate Password</label>
        <input name="cpassword" type="password" id="inputPassword" class="form-control" placeholder="Repeate Password" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Change</button>
      </form>
	  
	<?php } ?>
    </div>
    <script src="<?php echo base_url();?>assets/js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>
