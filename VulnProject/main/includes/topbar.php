
<?php
	$current_user = get_user_by_id($_SESSION['user_id']);
?>
<div class="brand clearfix">
	<a href="/VulnProject/test/"><h4 class="pull-left text-white text-uppercase" style="margin:20px 0px 0px 20px">Trang chủ</h4></a>
	<span class="menu-btn"><i class="fa fa-bars"></i></span>
	<ul class="ts-profile-nav">
		
		<li class="ts-account">
			<a href="#"><img src="images/<?php echo $current_user->avatar; ?>" class="ts-avatar hidden-side" alt=""><?php echo $current_user->name; ?></a>
			<ul>
				<li><a href="?page=user-detail.php"><?php echo _("Profile"); ?></a></li>
				<li><a href="?page=contact.php"><?php echo _("Liên hệ"); ?></a></li>
				<li><a href="logout.php"><?php echo _("Logout"); ?></a></li>
			</ul>
		</li>
	</ul>
</div>