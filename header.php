<?php
require_once ('db.php');
require_once ('user.php');

function auto_version($file)
{
  if(strpos($file, '/') !== 0 || !file_exists($_SERVER['DOCUMENT_ROOT'] . $file))
    return $file;

  $mtime = filemtime($_SERVER['DOCUMENT_ROOT'] . $file);
  return preg_replace('{\\.([^./]+)$}', ".$mtime.\$1", $file);
}



function page_karma($karma)
{
    global $con;
    if (isset($_SESSION["USERID"]))
    {
	$query = $con->prepare("SELECT page_karma_quota FROM users WHERE id=?");
	$query->bind_param('i', $_SESSION["USERID"]);
	$query->execute();
	$result = $query->get_result();
	$quot = mysqli_fetch_assoc($result)["page_karma_quota"];
	$sub = $quot * $karma;
	$quot = $quot - $sub;

	$query = $con->prepare("UPDATE users SET page_karma_quota = ? WHERE id=?");
	$query->bind_param('di', $quot, $_SESSION["USERID"]);
	$query->execute();

	add_karma_id($_SESSION["USERID"], $sub);
    }
    else
    {
	if (isset($_COOKIE["referer"]))
	{
	$query = $con->prepare("SELECT id, page_karma_quota FROM users WHERE name=?");
	$query->bind_param('s', $_COOKIE["referer"]);
	$query->execute();
	$result = $query->get_result();
    $r = mysqli_fetch_assoc($result);
	$quot = $r["page_karma_quota"];
	$sub = $quot * $karma;
	$quot = $quot - $sub;

	$query = $con->prepare("UPDATE users SET page_karma_quota = ? WHERE id=?");
	$query->bind_param('di', $quot, $r["id"]);
	$query->execute();

	add_karma_id($r["id"], $sub);
    }
    }
}


function product_image($id)
{
  return "image.php?id=".$id;
}

function karma_to_delay($karma)
{
  $delay = 60*60*60 /($karma);
  return $delay;
}

function votes_to_rating($level)
{
  $temp = 100/(1+pow(0.90,$level));
  return $temp;
}

function rating_to_votes($rating)
{
  if ($rating < 1) return -10000000;
  if ($rating > 99) return 10000000;
  $temp = log(100/$rating-1,0.9);
  return $temp;
}


function add_karma_id($user, $inc)
{
    global $con;
    $query = $con->prepare("UPDATE users SET karma = karma + ? WHERE id = ?");
    $query->bind_param('ds', $inc, $user);
    $query->execute();
    $query = $con->prepare("SELECT referer FROM users WHERE id=?");
    $query->bind_param('i', $user);
    $query->execute();
    $result = $query->get_result();
    $ref = mysqli_fetch_assoc($result)["referer"];
    if (!is_null($ref))
    {
    	add_karma_id($ref, $inc/2);
    }
}


if (isset($_GET["ref"]))
{
    setcookie("referer", $_GET["ref"], time() + 60*60*24*30);
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo auto_version('validation.min.js'); ?>"></script>
    <script src="<?php echo auto_version('/header.js'); ?>" type="text/javascript"></script>
    <script src="<?php echo auto_version('/autocomplete.js'); ?>"></script>
    <script>
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o)
                , m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');
        ga('create', 'UA-83679568-1', 'auto');
        ga('send', 'pageview');
    </script>
    <title>Deal Ferret</title>
    <link rel="apple-touch-icon" sizes="57x57" href="/icon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/icon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/icon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/icon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/icon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/icon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/icon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/icon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icon/favicon-16x16.png">
    <link rel="manifest" href="/icon/manifest.json">
    <link rel="stylesheet" href="<?php echo auto_version('/main.css'); ?>">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="<?php echo auto_version('/bootstrap.min.css'); ?>">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <meta name="viewport" content="width=device-width, initial-scale=0.45">
</head>

<body>
<?php

if(!isset($_COOKIE["comply_cookie"])) {
    echo '<div id="cookies"><p>Our website uses cookies. By continuing we assume your permission to deploy cookies.';
    echo '<span class="cookie-accept" title="Okay, close"><img src="img/close.png" alt="Close"></span></p>  </div>';
    }

$is_homepage = 0;
if (isset($_SESSION["USERID"]))
{
 $query = $con->prepare("SELECT uri FROM `bookmark` where bookmark.user = ?");
 $query->bind_param('s', $_SESSION["USERID"]);
 $query->execute();
 $result = $query->get_result();
 $row = mysqli_fetch_assoc($result);

 if ($row != NULL)
 {
    if ($row["uri"] == $_SERVER['REQUEST_URI'])
        $is_homepage = 1;
 }

}
?>

<nav class="navbar navbar-default">
  <div class="container">
    <div class="navbar-header">
      <img src="/logo150.png" alt="Deal Ferret Logo" height="50">
    </div>
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <a class="navbar-brand" href="https://dealferret.uk/">Deal Ferret</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <form class="navbar-form navbar-left" action="search.php" method="get">
        <div class="form-group">
          <input type="text" class="form-control" value="<?php if (isset($oldsearch)) echo htmlspecialchars($oldsearch); ?>" placeholder="Search" id="keyword" name="name" list="datalist">
        </div>
        <button type="submit" class="btn btn-default">Go</button>
      </form>
      <div class="navbar-form navbar-left">
      <button class="btn <?php echo $is_homepage ? "btn-success" : "btn-default";?>" id="makehomepage"><i class="cr-icon glyphicon glyphicon-home"></i></button> 
      </div>
<script type="text/javascript">

$('#makehomepage').on('click', function (e) {
    $.ajax({
        url:"/setbookmark.php?uri=<?php echo urlencode($_SERVER['REQUEST_URI']);?>",
        success:function(responsedata){
            $('#makehomepage').removeClass('btn-default').addClass('btn-success');
        }
     })
});


</script>

      <ul class="nav navbar-nav navbar-right">
        <li>
            <a href="https://dealferret.uk/">Deals</a>
        </li>
<?php 
if (isset($_SESSION["USERID"]))
{
?>
        <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" ><?php echo $_SESSION["USERNAME"]; ?> <span class="caret"></span></a>
         <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenuUserButton">
          <li><a href="account.php">Account</a></li>
          <li><a href="#" onclick="Logout();" >Log out</a></li>
         </ul>

        </li>

<?php
} else {
?>
        <li class="dropdown">
        <a class="dropdown-toggle" role="button" id="dropdownMenuDealButton" data-toggle="dropdown">Log-in<span class="caret"></span></a>
        <div class="dropdown-menu" >
        <div class="container col-md-12">
        <h1>Log In</h1>
        <hr/>
        <form class="form-signin" method="post" id="login-form">
        <div id="login-error">
        <!-- error will be shown here ! -->
        </div>
        <div class="form-group">
        <input type="text" class="form-control" placeholder="Username" name="login_user_name" id="login_user_name" />
        <span id="check-e"></span>
        </div>
        <div class="form-group">
        <input type="password" class="form-control" placeholder="Password" name="login_password" id="login_password" />
        </div>
        <hr />
        <div class="form-group">
            <button type="submit" class="btn btn-default" name="login-btn" id="login-btn">
            <span class="glyphicon glyphicon-log-in"></span> &nbsp; Sign In
            </button> 
        </div>  
      </form>
      <div>
      <a href="register.php">Register</a>
      </div>
 
    </div>
    
</div>

        </li>

<?php
}
?>


      </ul>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
    

<div class="body container">
