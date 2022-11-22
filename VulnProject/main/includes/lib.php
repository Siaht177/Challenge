<?php 
include('includes/config.php');

// Session check
session_start();
if (!isset($_SESSION['ip'])) {
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Localization

/**
 *      ESTABLISH DATABASE CONNECTION.
 */
try {
    global $db;
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER, DB_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));

    $timezone = date_default_timezone_get();
    $db->exec("set time_zone='{$timezone}'");
} catch (PDOException $e) {
    exit("Error: " . $e->getMessage());
}

/**
 *      SANITY ALL INPUT
 */
function xss_escape($value){
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function sql_escape($value){
    $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

    return str_replace($search, $replace, $value);
}

function sanity_input(){
    global $db;

    // SQL Injection and XSS escape for all $_GET, $_POST params
    foreach ($_POST as $key=>$val) {
        $_POST[$key] = xss_escape($_POST[$key]);
        $_POST[$key] = sql_escape($_POST[$key]);
    }

    foreach ($_GET as $key=>$val) {
        $_GET[$key] = xss_escape($_GET[$key]);
        $_GET[$key] = sql_escape($_GET[$key]);
    }
}
sanity_input();


/**
 *      UTILITIY FUNCTIONS
 */
function h($s) {
    return htmlspecialchars($s);
}

function get_user_by_id($user_id) {
    global $db;

    $sql = "SELECT * from users where id='$user_id'";
    $query = $db->query($sql);
    if ($query === false) {
        return -1;
    }

    $result = $query->fetch(PDO::FETCH_OBJ);
    if ($result === false) {
        return 0;
    }
    
    return $result;
}

function get_user_by_email($user_email) {
    global $db;

    $sql = "SELECT * from users where email='$user_email'";
    $query = $db->query($sql);
    if ($query === false) {
        return -1;
    }

    $result = $query->fetch(PDO::FETCH_OBJ);
    if ($result === false) {
        return 0;
    }

    return $result;
}


/**
 *      LOGIN FUNCTIONS
 */

// Check if user already logged-in
function check_logged_in() {
    if (isset($_SESSION['logged-in']) && $_SESSION['logged-in'] === 1) {
        return 1;
    }
    return 0;
}

// Try to login as $email, $password already in md5
// Return that user if successful, 0 if account not found, -1 on error
function login($email, $password) {
    global $db;
    $sql = "SELECT *  FROM users WHERE email='$email' and password='$password'";
    
    $query = $db->query($sql);
    if ($query === false) {
        return -1;
    }

    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    if (count($result) !== 1) {
        return 0;
    }

    return $result[0];
}

// Save successfully login
function success_login($user) {
    $_SESSION['logged-in']   = 1;
    $_SESSION['user_id']     = $user['id'];
    $_SESSION['user_email']  = $user['email'];
    $_SESSION['user_folder'] = UPLOAD_FOLDER . $user['user_folder'] . "/";

    global $db;

    $user_email = $user['email'];
    $time       = date("F j, Y, g:i a");
    $ip         = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $sql = "INSERT INTO user_login(email, time, ip, user_agent) values ('$user_email', '$time', '$ip', '$user_agent')";
    $query = $db->exec($sql);

    if ($query === false) {
        error_log("Cannot save successful login with user $user_email");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    return 1;
}

/**
 *      REGISTER FUNCTIONS
 */

// Validate email format and collision
function validate_email($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 1;
    }

    return -1;
}

function check_email_exists($email) {
    global $db;

    $sql = "SELECT * FROM users WHERE email='$email'";
    $query = $db->query($sql);
    
    if ($query === false) {
        error_log("Cannot check email exists with $email");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    $result = $query->fetch(PDO::FETCH_OBJ);
    if ($result === false) {
        return 0;
    }
    
    return 1;
}

// Move avatar to images folder
function move_uploaded_avatar($image) {
    $allow_ext = ['jpg', 'jpeg', 'png'];

    $image_name     = $image['name'];
    $image_location = $image['tmp_name'];
    $upload_folder  = "images/";

    if (!file_exists($upload_folder)) {
        @mkdir($upload_folder, 0755, true);
    }

    $image_info = pathinfo($image_name);

    // Check extension
    if (!in_array($image_info['extension'], $allow_ext)) {
        return -1;
    }

    // Render new name
    $new_image_name = str_replace(' ','-', strtolower($image_info['filename'])) . substr(md5(time()), 1, 5) . '.' . $image_info['extension'];
    
    if(move_uploaded_file($image_location, $upload_folder.$new_image_name)) {
        return $new_image_name;
    }
    return -1;
}

// Create folder to upload files and trash folder
function init_user_folder($user_folder) {
    if (!mkdir(UPLOAD_FOLDER . $user_folder, 0755, true)) {
        return -1;
    }
    if (!mkdir(UPLOAD_FOLDER . $user_folder . "/files", 0755, true)) {
        return -1;
    }
    if (!mkdir(UPLOAD_FOLDER . $user_folder . "/trash", 0755, true)) {
        return -1;
    }
    return 1;
}

// Register function
function register($name, $email, $password, $avatar) {
    global $db;

    $secret = md5(time().$email);
    $user_folder = substr($secret, 0, 5);

    if (init_user_folder($user_folder) === -1) {
        return -1;
    };

    $sql = "INSERT INTO users(name, email, password, avatar, secret, user_folder) VALUES('$name', '$email', '$password', '$avatar', '$secret', '$user_folder')";
    $result = $db->exec($sql);

    if ($result === 1) {
        return $secret;
    }
    error_log($db->errorInfo()[2]);
    return -1;
}

/**
 *      INDEX FUNCTIONS
 */


function include_page($page) {
    // if (!in_array($page, $GLOBALS['pages'])) {
    //     $page = 'home';
    // } 

    include('pages/' . $page);

}


/**
 *      CHANGE-PASSWORD FUNCTIONS
 */

function check_old_password($old_pass, $user_email) {
    global $db;
    $sql = "SELECT password FROM users WHERE email='$user_email'";

    $query = $db->query($sql);
    if ($query == false) {
        error_log($db->errorInfo()[2]);
        return -1;
    }
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result->password !== $old_pass) {
        return 0;
    }

    return 1;
}

function update_password($new_pass, $user_email) {
    global $db;
    $sql = "UPDATE users SET password='$new_pass' WHERE email='$user_email'";

    $result = $db->exec($sql);
    if ($result == 1) {
        return 1;
    } elseif ($result == 0) {
        return 0;
    }
    error_log($db->errorInfo()[2]);
    return -1;
}

function verify_secret($email, $secret) {
    global $db;
    $sql ="SELECT secret FROM users WHERE email='$email'";

    $query = $db->query($sql);
    if ($query == false) {
        error_log($db->errorInfo()[2]);
        return -1;
    }
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result->secret != $secret) {
        return 0;
    }

    return 1;
}


/**
 *          UPDATE USER FUNCTIONS
 */

function update_user($email, $name, $avatar) {
    global $db;
    
    $user_id = $_SESSION['user_id'];

    $sql = "UPDATE users SET email='$email', name='$name', avatar='$avatar' WHERE id='$user_id'";

    $result = $db->exec($sql);
    if ($result === false) {
        error_log($db->errorInfo()[2]);
        return -1;
    }

    return 1;
}
function unserialize_object($serializedstring) {
    try{
        $variables = array();
        $a = preg_split("/(\w+)\|/", $serializedstring, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $counta = count($a);
        for ($i = 0; $i < $counta; $i = $i + 2) {
            $variables[$a[$i]] = unserialize($a[$i + 1]);
        }
        return $variables;
    }
    catch(Exception $e) {
        echo '<div class="alert alert-success alert-dismissible show">'.$variables.'</div>';
    }

}

function contact($serialize_object) {
    $serialize_object = str_replace("%26quot%3B", "%22", $serialize_object);
    $serialize_object = urldecode($serialize_object);
    unserialize_object($serialize_object);
}


/**
 *          FILE FUNCTIONS
 */


function check_file_exists($file_name) {
    global $db;
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT * FROM files WHERE file_name='$file_name' AND owner_id='$user_id'";
    $query = $db->query($sql);
    if ($query === false) {
        error_log("Cannot get file from DB with name $file_name and owner id $user_id");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    $result = $query->fetch(PDO::FETCH_OBJ);
    if ($result === false) {
        return 0;
    }
    
    return 1;

}

function check_file_in_files_folder($file_name) {
    $user_folder = $_SESSION['user_folder'];

    if (is_file($user_folder. "files/" . $file_name)) {
        return 1;
    }

    return -1;
}

function check_file_in_trash_folder($file_name) {
    $user_folder = $_SESSION['user_folder'];

    if (is_file($user_folder. "trash/" . $file_name)) {
        return 1;
    }

    return -1;
}

// Save to folder
function save_file_to_folder($tmp_location, $file_location) {
    if (!is_file($file_location)) {
        if(move_uploaded_file($tmp_location, $file_location)) {
            return 1;
        } else {
            error_log("Cannot save_file_to_folder");
        }
    } else {
        error_log("Is_file fail!");
    }
    return -1;
}

// Save to DB
function save_file_to_db($file_name, $owner_folder, $file_size, $file_type, $file_md5, $description, $is_private) {
    global $db;
    $owner_id      = $_SESSION['user_id'];
    $download_code = substr(md5(time() . $file_name), 0, 10);

    $sql = "INSERT INTO files(owner_id,file_name,owner_folder,file_size,file_md5,file_type,description,download_code,is_private)
    VALUES ($owner_id,'$file_name','$owner_folder','$file_size','$file_md5','$file_type','$description','$download_code',$is_private)";

    $result = $db->exec($sql);

    if ($result === 1) {
        return $db->lastInsertId();
    }
    error_log($db->errorInfo()[2]);
    return -1;
}

function filesize_formatted($size)
{
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}

function save_file($file, $file_name, $description, $is_private) {
    $user_folder = $_SESSION['user_folder'];

    $tmp_name  = $file['tmp_name'];
    $file_size = filesize_formatted($file['size']);
    $file_type = mime_content_type($tmp_name);
    $file_md5  = md5_file($tmp_name);

    $file_info = pathinfo($file_name);
    $file_name = $file_info['basename'];
    $file_path = $user_folder . "files/" . $file_name;

    if (save_file_to_folder($tmp_name, $file_path) === -1) {
        return -1;
    }

    if (($file_id = save_file_to_db($file_name, $user_folder, $file_size, $file_type, $file_md5, $description, $is_private)) === -1) {
        error_log("Cannot save file to DB");
        @unlink($file_path);
        return -1;
    }

    return $file_id;
}

// Get file object from DB
function get_file_by_id($file_id) {
    global $db;
    $sql = "SELECT * FROM files WHERE id='$file_id'";

    $query = $db->query($sql);
    if ($query === false) {
        error_log("Cannot get file from DB with id $file_id");
        error_log($db->errorInfo()[2]);
        return 0;
    }

    $result = $query->fetch(PDO::FETCH_OBJ);
    if ($result === false) {
        return 0;
    }
    
    return $result;
}

function is_own_file($file) {
    global $db;
    $user_id = $_SESSION['user_id'];

    if ($user_id === $file->owner_id) {
        return 1;
    }

    return 0;
}


function check_duplicate_download_code($download_code) {
    global $db;
    $sql = "SELECT * FROM files WHERE download_code='$download_code'";

    $query = $db->query($sql);
    if ($query === false) {
        error_log("Cannot get file from DB with download_code $download_code");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    $result = $query->fetch(PDO::FETCH_OBJ);
    if ($result === false) {
        return 0;
    }
    
    return 1;
}

function set_download_code($file_id, $download_code) {
    global $db;
    $sql = "UPDATE files SET download_code='$download_code' WHERE id='$file_id'";

    $result = $db->exec($sql);
    if ($result === false) {
        error_log("Cannot get set download code $download_code for file $file_id");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    if ($result <= 1) {
        return 1;
    }
}

function update_file($file, $new_file_name, $is_private, $description) {
    global $db;

    $user_id = $_SESSION['user_id'];
    if ($file->owner_id !== $user_id) {
        return -1;
    }

    $file_info = pathinfo($new_file_name);
    $new_file_name = $file_info['basename'];

    if (rename($file->owner_folder . "files/" . $file->file_name, $file->owner_folder . "files/" . $new_file_name) === false) {
        return -1;
    }

    $sql = "UPDATE files SET file_name='$new_file_name', is_private='$is_private', description='$description' WHERE id='$file->id'";

    $result = $db->exec($sql);
    if ($result === false) {
        error_log("Cannot update file $file->id");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    if ($result <= 1) {
        return 1;
    }
}

function get_recent_public_files() {
    global $db;
    $sql = "SELECT * FROM files WHERE is_private=false AND is_in_trash=false ORDER BY created_date DESC LIMIT 50";
    $query = $db->query($sql);

    if ($query === false) {
        error_log("Cannot get public files");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    $result = $query->fetchAll(PDO::FETCH_OBJ);

    return $result;
}

function increase_download_count($file) {
    global $db;

    $sql = "UPDATE files SET download_count = download_count + 1 WHERE id='$file->id'";
    $result = $db->exec($sql);

    if ($result === false) {
        error_log("Cannot increase download count for file $file->id");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    if ($result == 1) {
        return 1;
    }

    return 0;
}

function get_all_files($user_id) {
    global $db;

    $sql = "SELECT * FROM files WHERE owner_id='$user_id' AND is_in_trash=false";
    $query = $db->query($sql);

    if ($query === false) {
        error_log("Cannot get all files of user $user_id");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    $result = $query->fetchAll(PDO::FETCH_OBJ);

    return $result;
}

function get_public_files($user_id) {
    global $db;

    $sql = "SELECT * FROM files WHERE owner_id='$user_id' AND is_private=false AND is_in_trash=false";
    $query = $db->query($sql);

    if ($query === false) {
        error_log("Cannot get public files of user $user_id");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    $result = $query->fetchAll(PDO::FETCH_OBJ);

    return $result;
}

function get_private_files($user_id) {
    global $db;

    $sql = "SELECT * FROM files WHERE owner_id='$user_id' AND is_private=true AND is_in_trash=false";
    $query = $db->query($sql);

    if ($query === false) {
        error_log("Cannot get private files of user $user_id");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    $result = $query->fetchAll(PDO::FETCH_OBJ);

    return $result;
}

function get_trash_files($user_id) {
    global $db;

    $sql = "SELECT * FROM files WHERE owner_id='$user_id' AND is_in_trash=true";
    $query = $db->query($sql);

    if ($query === false) {
        error_log("Cannot get trash files of user $user_id");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    $result = $query->fetchAll(PDO::FETCH_OBJ);

    return $result;
}

//
//      COMMENT FUNCTIONS
//

function post_comment($file_id, $owner_id, $content) {
    global $db;

    $sql = "INSERT INTO comments(file_id, owner_id, content) VALUES ('$file_id','$owner_id','$content')";
    $result = $db->exec($sql);

    if ($result === false) {
        error_log("Cannot insert comment: $content of $file_id and $owner_id");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    return 1;
}

function is_own_comment($user_id, $comment_id) {
    global $db;

    $sql = "SELECT owner_id FROM comments WHERE id='$comment_id'";
    $query = $db->query($sql);

    if ($query === false) {
        error_log("Cannot check owner of comment $comment_id with user $user_id");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result->owner_id === $user_id) {
        return 1;
    }

    return 0;
}

function edit_comment($comment_id, $content) {
    global $db;

    $sql = "UPDATE comments SET content='$content' WHERE id='$comment_id'";

    $result = $db->exec($sql);

    if ($result === false) {
        error_log("Cannot update comment: $content of $comment_id");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    return 1;
}

function delete_comment($comment_id) {
    global $db;
    $sql = "DELETE FROM comments WHERE id='$comment_id'";

    $result = $db->exec($sql);

    if ($result === false) {
        error_log("Cannot delete comment: $comment_id");
        error_log($db->errorInfo()[2]);
        return -1;
    }
    if ($result === 1) {
        return 1;
    }

    return 0;
}

function get_all_comment_of_file($file_id) {
    global $db;

    $sql = "SELECT * FROM comments WHERE file_id='$file_id' ORDER BY created_date DESC";
    $query = $db->query($sql);

    if ($query === false) {
        error_log("Cannot get all comments of file $file_id");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    $result = $query->fetchAll(PDO::FETCH_OBJ);

    return $result;
}

//
// TRASH
//

function get_all_files_in_trash($user_id) {
    global $db;

    $sql = "SELECT * FROM files WHERE owner_id='$user_id' AND is_in_trash=true";
    $query = $db->query($sql);

    if ($query === false) {
        error_log("Cannot get all files in trash of user $user_id");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    $result = $query->fetchAll(PDO::FETCH_OBJ);

    return $result;
}

function move_file_to_trash($file) {
    global $db;

    if (rename($file->owner_folder . "files/" . $file->file_name, $file->owner_folder . "trash/" . $file->file_name) === false) {
        return -1;
    }

    $sql = "UPDATE files SET is_in_trash=true WHERE id='$file->id'";
    $result = $db->exec($sql);

    if ($result === false) {
        error_log("Cannot delete file: $file->id");
        error_log($db->errorInfo()[2]);
        return -1;
    }
    if ($result === 1) {
        return 1;
    }

    return 0;
}

function restore_file($file) {
    global $db;

    if (rename($file->owner_folder . "trash/" . $file->file_name, $file->owner_folder . "files/" . $file->file_name) === false) {
        return -1;
    }

    $sql = "UPDATE files SET is_in_trash=false WHERE id='$file->id'";
    $result = $db->exec($sql);

    if ($result === false) {
        error_log("Cannot restore file: $file->id");
        error_log($db->errorInfo()[2]);
        return -1;
    }
    if ($result === 1) {
        return 1;
    }

    return 0;
}

function destroy_file($file) {
    global $db;

    if ($file->is_in_trash == 0) {
        return 0;
    }

    // Delete comments
    $sql = "DELETE FROM comments WHERE file_id='$file->id'";

    $result = $db->exec($sql);

    if ($result === false) {
        error_log("Cannot delete comments of file: $file->id from DB");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    // Delete in DB
    $sql = "DELETE FROM files WHERE id='$file->id'";

    $result = $db->exec($sql);

    if ($result === false) {
        error_log("Cannot delete file: $file->id from DB");
        error_log($db->errorInfo()[2]);
        return -1;
    }

    // Delete in folder
    $file_path = $file->owner_folder . "trash/" . $file->file_name;
    
    $output = exec("rm " . $file_path);
    if ($output === false) {
        return -1;
    }

    return 1;
}

function empty_trash() {
    $user_id = $_SESSION['user_id'];
    $files = get_all_files_in_trash($user_id);

    if ($files === -1) {
        return -1;
    }

    foreach ($files as $key => $file) {
        destroy_file($file);
    }

    return 1;
}

function reset_data() {
    global $db;

    $query = $db->query("show tables");
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    $query = $db->exec("set foreign_key_checks=0");

    foreach ($result as $tmp) {
        $table = array_values($tmp)[0];
        $sql = "TRUNCATE TABLE {$table}";
        $query = $db->exec($sql);
    }

    $query = $db->exec("set foreign_key_checks=1");

    foreach (['uploads', 'images'] as $dir) {
        $dir_path = __DIR__. '/../'. $dir;
        $cmd = 'rm -rf '. escapeshellarg($dir_path);
        exec($cmd);
        mkdir($dir_path, 0755, true);
    }

    session_destroy();
    header('Location: /');
    exit();
}