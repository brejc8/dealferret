<?php
    
    require_once 'db.php';

    if($_POST)
    {
        $user_name = $_POST['login_user_name'];
        $user_password = $_POST['login_password'];

	$query = $con->prepare("SELECT * FROM users WHERE name=?");
	$query->bind_param('s', $user_name);
	$query->execute();
        $result = $query->get_result();

	if ($result->num_rows == 0)
	{
	    echo "No such user";
	    exit();
	}
	$row = mysqli_fetch_assoc($result);
	if (password_verify ($user_password, $row["password"]))
	{
	    setcookie("remember_me_cookie", $row["remember_me_cookie"], time() + 60*60*24*30*12);
	    echo "Success";
            $_SESSION["USERID"] = $row["id"];
            $_SESSION["USERNAME"] = $user_name;
			if ($row["admin"] > 0)
        		$_SESSION["ADMIN"] = $row["admin"];

	}
	else
	{
	    echo "Incorrect password";
	}
	
    }

?>
