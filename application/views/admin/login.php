<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Mosaddek">
    <meta name="keyword" content="FlatLab, Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">
    <link rel="shortcut icon" href="<?= base_url('axxests/img/favicon.html') ?>">
    <title>Axogamez | Login</title>
    <link href="<?= base_url('axxests/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('axxests/css/bootstrap-reset.css') ?>" rel="stylesheet">
    <link href="<?= base_url('axxests/assets/font-awesome/css/font-awesome.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('axxests/css/style.css') ?>" rel="stylesheet">
    <link href="<?= base_url('axxests/css/style-responsive.css') ?>" rel="stylesheet" />
</head>
  <body class="login-body">
  <div class="container">
      <form class="form-signin" action="<?= base_url('backend/login') ?>" method="post">
      <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
      <?php echo $this->session->flashdata('site_flash') ?>
  <h2 class="form-signin-heading">sign in now</h2>
  <div class="login-wrap">
      <input type="text" class="form-control" placeholder="Username" name="uname">
      <input type="password" class="form-control" placeholder="Password" name="pass">
      <label class="checkbox">
            <a data-toggle="modal" href="#myModal">Forgot Password ?</a>
      </label>
      <button class="btn btn-md btn-login btn-block" type="submit">Sign in</button>
  </div>

    <!-- Modal -->
    <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Forgot Password ?</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-dark">Enter your e-mail address below to reset your password.</p>
                    <input type="text" name="email" placeholder="Email" autocomplete="off" class="form-control placeholder-no-fix">

                </div>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                    <button class="btn btn-primary" type="button">Submit</button>
                </div>
            </div>
        </div>
    </div>
    <!-- modal -->
</form>

</div>
    <!-- js placed at the end of the document so the pages load faster -->
    <script src="<?= base_url('axxests/js/jquery.js') ?>"></script>
    <script src="<?= base_url('axxests/js/bootstrap.bundle.min.js') ?>"></script>
  </body>
</html>
