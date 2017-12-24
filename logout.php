<?php
session_start();
setcookie("remember_me_cookie", "", time() + 60);
session_destroy();
?>
