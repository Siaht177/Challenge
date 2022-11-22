
<?php
include('includes/lib.php');

// Redirect to index if authenticated
if (check_logged_in() === 1) {
    header("Location: /index.php");
    exit();
}

if(isset($_POST['login'])) {
    $email    = $_POST['email'];
    $password = md5($_POST['password']);

    $user = login($email, $password);

    if ($user === -1) {
        $error = _("Có lỗi xảy ra, xin hãy thử lại sau");
    } elseif ($user === 0) {
        $error = _("Thông tin đăng nhập không hợp lệ");
    } else {
        success_login($user);
        header("Location: index.php");
    }
}

?>
<!doctype html>
<html lang="en" class="no-js">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    
    <link rel="stylesheet" href="css/fontawesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <div class="login-page bk-img">
        <div class="form-content">
            <div class="container">
                <div class="col-md-6 col-md-offset-3">
                    <h1 class="text-center text-bold mt-4x"><?php echo _("Đăng nhập"); ?></h1>
                    <div class="well row pt-2x pb-3x bk-light">
                        <div class="col-md-8 col-md-offset-2">
                            <form method="post">

                                <label for="" class="text-uppercase text-sm"><?php echo _("Email của bạn"); ?></label>
                                <input type="text" placeholder="Email" name="email" class="form-control mb" required>

                                <label for="" class="text-uppercase text-sm"><?php echo _("Mật khẩu"); ?></label>
                                <input type="password" placeholder="Password" name="password" class="form-control mb" required>
                                <button class="btn btn-primary btn-block" name="login" type="submit"><?php echo _("Đăng nhập"); ?></button>
                            </form>
                            <br>
                            <?php if (isset($error)) { ?>
                            <div class="alert alert-danger alert-dismissible show">
                                <?php echo $error;?>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                            <?php } ?>
                            <p style="margin-bottom:0px"><?php echo _("Đăng ký tài khoản mới? "); ?><a href="register.php" ><?php echo _("Đăng ký"); ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/fontawesome.min.js"></script>
    <script src="js/piexif.js"></script>
    <script src="js/fileinput.min.js"></script>
    <script src="js/main.js"></script>

</body>
