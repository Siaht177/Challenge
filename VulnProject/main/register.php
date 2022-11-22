
<?php
include('includes/lib.php');

// Redirect to index if authenticated
if (check_logged_in() === 1) {
    header("Location: /index.php");
    exit();
}

if(isset($_POST['submit'])) {
    $name        = $_POST['name'];
    $email       = $_POST['email'];
    $password    = md5($_POST['password']);

    if (validate_email($email) === -1) {
        $error = $email . _(" Không hợp lệ!") . " <a style='cursor:pointer;color:blue' onclick=\"show_email_suggestion('$email');\"><b><i>" . _("Show suggestion!") . "</i></b></a>";;
    } elseif (check_email_exists($email)) {
        $error = _("Địa chỉ email này đã tồn tại");
    } else {
        if (file_exists($_FILES['avatar']['tmp_name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
            $avatar = move_uploaded_avatar($_FILES['avatar']);
        } else {
            $avatar = -1;
        }
        
        if ($avatar === -1){
            $error = _("Cannot upload this avatar!");
        } else {
            $secret = register($name, $email, $password, $avatar);
            
            if ($secret === -1) {
                $error = _("Đã có lỗi xảy ra, vui lòng thử lại sau!");
            } else {
                $success = _("Đăng ký thành công ");
            }
        }
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
	<link rel="stylesheet" href="css/fileinput.min.css">
	<link rel="stylesheet" href="css/style.css">
</head>

<body>
	<div class="login-page bk-img">
		<div class="form-content">
			<div class="container">
                <div class="col-md-12">
                    <h1 class="text-center text-bold mt-2x"><?php echo _("Đăng ký"); ?></h1>
                    <div class="hr-dashed"></div>
                    <div class="well pt-2x pb-3x bk-light text-center">
                    <form method="post" class="form-horizontal" enctype="multipart/form-data" onSubmit="return password_confirm();">
                        <div class="form-group">
                            <label class="col-sm-2 control-label"><?php echo _("Name"); ?><span style="color:yellow">*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            
                            <label class="col-sm-2 control-label"><?php echo _("Email"); ?><span style="color:yellow">*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="email" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label"><?php echo _("Mật khẩu"); ?><span style="color:yellow">*</span></label>
                            <div class="col-sm-4">
                                <input type="password" name="password" class="form-control" id="password" required >
                            </div>

                            <label class="col-sm-2 control-label"><?php echo _("Nhập lại mật khẩu"); ?><span style="color:yellow">*</span></label>
                            <div class="col-sm-4">
                                <input type="password" name="password-confirm" class="form-control" id="password-confirm" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo _("Ảnh đại diện"); ?><span style="color:yellow">*</span></label>
                            <div class="col-sm-4">
                                <input id="avatar" type="file" name="avatar" class="form-control">
                            </div>
                        </div>

                        <button class="btn btn-primary" name="submit" type="submit"><?php echo _("Đăng ký"); ?></button>
                    
                        <?php if (isset($error)) { ?>
                        <br>
                        <br>
                        <div class="alert alert-danger alert-dismissible show">
                            <?php echo $error;?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php }?>
                        <?php if (isset($success)) { ?>
                        <br>
                        <br>
                        <div class="alert alert-success alert-dismissible show">
                            <?php echo $success;?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php }?>
                    
                    </form>
                    <p><?php echo _("Đã có tài khoản? "); ?><a href="index.php" ><?php echo _("Đăng nhập"); ?></a></p>
                    </div>
                </div>
			</div>
		</div>
	</div>
	
	<!-- Loading Scripts -->
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap-select.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/fileinput.js"></script>
	<script src="js/main.js"></script>
</body>