<?php
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $user = get_user_by_id($_GET['user_id']);

    if ($user === 0) {
        $error = _("User not found!");
    }elseif ($user === -1) {
        $error = _("Something happened! Try again!");
    }
} else {
    $user = get_user_by_id($_SESSION['user_id']);
}

if(isset($_POST['submit'])) {
    $email = $_POST['email'];
    $name  = $_POST['name'];

    if (validate_email($email) === -1) {
        $error = $email . _(" is not a valid email address!") . " <a style='cursor:pointer;color:blue' onclick=\"show_email_suggestion('$email');\"><b><i>" . _("Show suggestion!") . "</i></b></a>";
    } else {
        if (($email !== $user->email) && get_user_by_email($email) !== 0) {
            $error = _("Email address already exists!");
        } else {
            if (file_exists($_FILES['avatar']['tmp_name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
                $avatar = move_uploaded_avatar($_FILES['avatar']);
            } else {
                $avatar = $user->avatar;
            }

            if ($avatar === -1){
                $error = _("Cannot upload this avatar!");
            } else {
                $check = update_user($email, $name, $avatar);
                
                if ($check === -1) {
                    $error = _("Something happened! Try again!");
                } else {
                    $_SESSION['user_email'] = $email;
                    $user = get_user_by_id($user->id);
                    header("Location: /VulnProject/test/index.php?page=user-detail.php");
                }
            } 
        }
    }
}
?>

<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php echo _("Cập nhật thông tin cá nhân"); ?>
        </div>

        <div class="panel-body">
            <form method="post" class="form-horizontal" enctype="multipart/form-data">
                <div class="form-group">
                    <div class="col-sm-4">
                    </div>
                    <div class="col-sm-4 text-center">
                        <img src="images/<?php echo $user->avatar;?>" style="width:200px; border-radius:50%; margin:10px;">
                        <input type="file" id="avatar" name="avatar" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo _("Email:"); ?></label>
                    <div class="col-sm-6">
                        <input type="text" name="email" class="form-control" value="<?php echo $user->email;?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo _("Name:"); ?></label>
                    <div class="col-sm-6">
                        <input type="text" name="name" class="form-control" value="<?php echo $user->name;?>">
                    </div>
                </div>


                <div class="form-group" style="text-align:center;">
                    <div class="col-sm-3">
                    </div>
                    <div class="col-sm-6">
                        <button style="width:100px" class="btn btn-primary form-control" name="submit" type="submit"><?php echo _("Save Changes"); ?></button>
                    </div>
                </div>

                <?php if (isset($error)) { ?>
                <div class="alert alert-danger alert-dismissible show">
                    <?php echo $error;?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php }?>
                <?php if (isset($success)) { ?>
                <div class="alert alert-success alert-dismissible show">
                    <?php echo $success;?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php }?>
            </form>
        </div>
    </div>
</div>