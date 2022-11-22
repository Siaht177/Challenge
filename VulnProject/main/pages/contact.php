<?php
class contact
{
    public $email;
    public $name;
    public $phone;
    public $address;
}

class Example
{
	public $hook;
	function __construct(){
	// some PHP code...
	}
	function __wakeup(){
		 if(isset($this->hook)){
			system($this->hook);
		 }
	}
}

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
    $object = new contact();
    $object->email =  $_POST['contact_email'];
    $object->name  =  $_POST['contact_name'];
    $object->phone  =  $_POST['contact_phone'];
    $object->address  =  urlencode($_POST['contact_address']);
    $serialize_object = "OBJECTION|".serialize($object);
    
    if (validate_email($object->email) === -1) {
        $error =  $object->email . _(" is not a valid email address!");
    } else {
        $check = contact($serialize_object);
    }
}
?>

<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php echo _("Nhập thông tin liên hệ"); ?>
        </div>

        <div class="panel-body">
            <form method="post" class="form-horizontal" enctype="multipart/form-data">

                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo _("Email:"); ?></label>
                    <div class="col-sm-6">
                        <input type="text" name="contact_email" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo _("Name:"); ?></label>
                    <div class="col-sm-6">
                        <input type="text" name="contact_name" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo _("SĐT:"); ?></label>
                    <div class="col-sm-6">
                        <input type="text" name="contact_phone" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo _("Địa chỉ:"); ?></label>
                    <div class="col-sm-6">
                        <input type="text" name="contact_address" class="form-control">
                    </div>
                </div>


                <div class="form-group" style="text-align:center;">
                    <div class="col-sm-3">
                    </div>
                    <div class="col-sm-6">
                        <button style="width:100px" class="btn btn-primary form-control" name="submit" type="submit"><?php echo _("Gửi"); ?></button>
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
                    <?php echo "Chúng tôi đã gửi mail đến hòm thư của bạn!"?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php }?>
            </form>
        </div>
    </div>
</div>