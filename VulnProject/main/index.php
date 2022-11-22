
<?php
include('includes/lib.php');

// Redirect to login if unauthenticated
if (check_logged_in() === 0) {
	header("Location: /VulnProject/test/login.php");
	exit();
}

?>

<!doctype html>
<html lang="en" class="no-js">

<!-- Header -->
<?php include('includes/header.php');?>

<body>
	<!-- Top bar -->
	<?php include('includes/topbar.php');?>

	<div class="ts-main-content">
		<!-- Left bar -->
		<div class="content-wrapper">
			<div class="container-fluid">
				<!-- Main content -->
				<?php if (isset($_GET['page']) && !empty($_GET['page'])) { 
					$page = $_GET['page'];
					include_page($page);

				} else { 
					include_page('dashboard.php'); 
				} 
				?>
			</div>
		</div>
	</div>
</body>

<!-- Footer -->
<?php include('includes/footer.php');?>
