<?php
require_once ('db.php');
require_once ('user.php');

date_default_timezone_set('Europe/London');

function auto_version($file)
{
  if(strpos($file, '/') !== 0 || !file_exists($_SERVER['DOCUMENT_ROOT'] . $file))
    return $file;

  $mtime = filemtime($_SERVER['DOCUMENT_ROOT'] . $file);
  return preg_replace('{\\.([^./]+)$}', ".$mtime.\$1", $file);
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">    
    <!-- Optional theme -->
    <link rel="stylesheet" href="<?php echo auto_version('/bootstrap.min.css'); ?>">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">    
    <meta name="viewport" content="width=device-width, initial-scale=0.45">
<?php
if(isset($head_extra))
    echo $head_extra;

?>
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

<div class="navbar navbar-expand-lg navbar-dark bg-primary">
 <div class="mr-auto container">

  <a class="navbar-brand" href="https://dealferret.uk"><img src="/logo150.png" alt="Deal Ferret Logo" height="50"></a>
  <a class="navbar-brand" href="https://dealferret.uk/">Deal Ferret</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation" style="">
    <span class="navbar-toggler-icon"></span>
  </button>
    <div class="collapse navbar-collapse container" id="navbarColor01" >

    <!-- Collect the nav links, forms, and other content for toggling -->
      <form class="form-inline my-2 my-lg-0" action="search.php" method="get">
        <input type="text" class="form-control mr-sm-2" value="<?php if (isset($oldsearch)) echo htmlspecialchars($oldsearch); ?>" placeholder="Search" id="keyword" name="name" list="datalist">
        <button type="submit" class="btn btn-secondary my-2 my-sm-0">Go</button>
      </form>
      <div class="nav-item"><button type="button" class="ml-2 btn <?php echo $is_homepage ? "btn-success" : "btn-secondary";?>" id="makehomepage"><i class="fas fa-home"></i></button></div>
    
<script type="text/javascript">
$('#makehomepage').on('click', function (e) {
    $.ajax({
        url:"/setbookmark.php?uri=<?php echo urlencode($_SERVER['REQUEST_URI']);?>",
        success:function(responsedata){
            $('#makehomepage').removeClass('btn-secondary').addClass('btn-success');
        }
     })
});
</script>

      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a class="nav-link" href="https://dealferret.uk/">Deals</a>
        </li>
<?php 
if (isset($_SESSION["USERID"]))
{
?>
        <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-expanded="false" ><?php echo $_SESSION["USERNAME"]; ?> <span class="caret"></span></a>
         <div class="dropdown-menu">
          <a class="dropdown-item" href="account.php"><i class="fas fa-user"></i> &nbsp; Account</a>
          <a class="dropdown-item" href="#" onclick="Logout();" ><i class="fas fa-sign-out-alt"></i> &nbsp; Log out</a>
         </ul>

        </li>

<?php
} else {
?>
        <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" id="dropdownMenuDealButton" data-toggle="dropdown">Log-in<span class="caret"></span></a>
<div class="dropdown-menu" aria-labelledby="dropdown" >
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
        <div class="dropdown-divider"></div>
        <div class="form-group">
            <button type="submit" class="btn btn-secondary" name="login-btn" id="login-btn">
            <i class="fas fa-sign-in-alt"></i> &nbsp; Sign In
            </button> 
        </div>  
      </form>
      <div class="dropdown-divider"></div>
      <a href="register.php">Register</a>
 
</div>

        </li>

<?php
}
?>


      </ul>

    </div><!-- /.navbar-collapse -->
</div>
</div>
    

<div class="body container">
