
<?php
	if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
		$user = get_user_by_id($_GET['user_id']);
		if ($user === 0) {
			$error = _("User not found!");
		}elseif ($user === -1) {
			$error = _("Something happened! Try again!");
		}
	} else {
		$user = get_user_by_email($_SESSION['user_email']);
	}
?>

<div class="col-md-12">
	<div class="panel panel-default">
		<div class="panel-heading">
			<?php echo _("Thông tin cá nhân"); ?>
		</div>

		<div class="panel-body">
			<?php if (isset($error)) { ?>
			<div class="alert alert-danger alert-dismissible show">
				<?php echo $error;?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
			<?php }?>
			<?php if (isset($user) && ($user !== 0 && $user !== -1)) { ?>
			<div class="form-horizontal">
				<div class="form-group">
					<div class="col-sm-3">
					</div>
					<div class="col-sm-6 text-center">
						<img src="images/<?php echo $user->avatar;?>" style="width:200px; border-radius:50%; margin:10px;">
					</div>
				</div>

				<?php if ($user->id === $_SESSION['user_id']) { ?>
				<div class="form-group">
					<label class="col-sm-3 control-label"><?php echo _("Email:"); ?></label>
					<div class="col-sm-6">
						<span type="text" name="email" class="form-control"><?php echo $user->email;?></span>
					</div>
				</div>
				<?php } ?>

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php echo _("Name:"); ?></label>
					<div class="col-sm-6">
						<span type="text" name="email" class="form-control"><?php echo $user->name;?></span>
					</div>
				</div>



				<?php if ($user->id === $_SESSION['user_id']) { ?>
				<div class="form-group" style="text-align:center;">
					<div class="col-sm-3">
					</div>
					<div class="col-sm-6">
						<a href="/VulnProject/test/index.php?page=update-profile.php" style="width:100px" class="btn btn-primary form-control" name="submit" type="submit"><?php echo _("Update"); ?></a>
					</div>
				</div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
	</div>
</div>