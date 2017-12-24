<?php
    
    require_once 'db.php';

    if($_POST)
    {
        $user_name = $_POST['reg_user_name'];
        $user_email = $_POST['reg_user_email'];
        $user_password = $_POST['reg_password'];
        $password = password_hash($user_password, PASSWORD_DEFAULT);
        
        try
        {
	    $query = $con->prepare("SELECT * FROM users WHERE email=?");
	    $query->bind_param('s', $user_email);
	    $query->execute();
            $result = $query->get_result();
            $count = $result->num_rows;
            
            if($count!=0){
                echo "Email already registered";
                }
            else{
		$query = $con->prepare("SELECT * FROM users WHERE name=?");
		$query->bind_param('s', $user_name);
		$query->execute();
        	$result = $query->get_result();

                $count = $result->num_rows;
                if($count!=0){
                    echo "Username already registered";
                    }
                else {
		    $referer = NULL;
		    if (isset($_COOKIE["referer"])) {
			$query = $con->prepare("SELECT id FROM users WHERE name=?");
			$query->bind_param('s', $_COOKIE["referer"]);
			$query->execute();
        		$result = $query->get_result();
			$row = mysqli_fetch_assoc($result);
			$referer = $row["id"];
			}
		    
		    
                    $query = $con->prepare("INSERT INTO users (name,email,password,referer,remember_me_cookie) VALUES(?, ?, ?, ?, MD5(RAND()*RAND()*1000000))");
		    $query->bind_param('sssi', $user_name, $user_email, $password, $referer);
		    $query->execute();
                    $userid = mysqli_insert_id ($con);
                    echo "Success";
                    $_SESSION["USERID"] = $userid;
                    $_SESSION["USERNAME"] = $user_name;
                    }
                }
        }
        catch(PDOException $e){
            echo $e->getMessage();
        }
    }

?>
