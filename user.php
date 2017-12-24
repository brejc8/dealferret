<?php

if (isset($_SESSION["USERNAME"]))
{
    error_log ("User:$_SESSION[USERNAME] https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
}

if (!isset($_SESSION["USERID"]) && isset($_COOKIE["remember_me_cookie"]))
{
    	$query = $con->prepare("SELECT * FROM users WHERE remember_me_cookie=?");
	$query->bind_param('s', $_COOKIE["remember_me_cookie"]);
	$query->execute();
        $result = $query->get_result();
	$row = mysqli_fetch_assoc($result);
	if ($row)
	{
        $_SESSION["USERID"] = $row["id"];
        $_SESSION["USERNAME"] = $row["name"];
		if ($row["admin"] > 0)
        	$_SESSION["ADMIN"] = $row["admin"];
	}
}

?>
