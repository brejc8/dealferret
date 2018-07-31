<?php 
require_once ('db.php');
require ('header.php');
?>
<div class="register-form">
	<div class="container">
     
       <form class="form-register" method="post" id="register-form">
      
        <h2 class="form-register-heading">Sign Up</h2><hr />
        
        <div id="error">
        <!-- error will be showen here ! -->
        </div>
        
        <div class="form-group">
        <input type="text" class="form-control" placeholder="Username" name="reg_user_name" id="reg_user_name" />
        </div>
        
        <div class="form-group">
        <input type="email" class="form-control" placeholder="Email address" name="reg_user_email" id="reg_user_email" />
        <span id="check-e"></span>
        </div>
        
        <div class="form-group">
        <input type="password" class="form-control" placeholder="Password" name="reg_password" id="reg_password" />
        </div>
        
        <div class="form-group">
        <input type="password" class="form-control" placeholder="Retype Password" name="reg_cpassword" id="ref_cpassword" />
        </div>
     	<hr />
        
        <div class="form-group">
            <button type="submit" class="btn btn-default" name="btn-register" id="btn-register">
    		<i class="fas fa-sign-in-alt"></i> &nbsp; Create Account
			</button> 
        </div>  
      </form>
    </div>
</div>

<?php
require ('footer.php');
?>
