<form class="block" action="{uri:./register}" method="post">
	<h6 class="no-top-padding">Register</h6>
	<p class="error" style="display: {data:register_error_message!=null?'block':'none'};">{data:register_error_message}</p>
	<p class="notice" style="display: {data:register_message!=null?'block':'none'};">{data:register_message}</p>
	<div style="display: {data:register_message!=null?'none':'block'};">
		<label>Email</label>
		<input type="email" name="email" value="{post:email==''?'':post:email}" maxlength="255" placeholder="Email" tabindex="101" required autofocus>
		<label>First Name</label>
		<input type="text" name="firstname" value="{post:firstname}" maxlength="128" placeholder="First Name" tabindex="102" required>
		<label>Last Name</label>
		<input type="text" name="lastname" value="{post:lastname}" maxlength="128" placeholder="Last Name" tabindex="103" required>
		<label>Password</label>
		<input type="password" name="password" tabindex="104" autocomplete="off" required>
		<label>Confirm Password</label>
		<input type="password" name="confirm_password" tabindex="105" autocomplete="off" required>
		<input type="hidden" name="register" value="1">
		<button type="submit" tabindex="106">Register</button>
		<div class="clear"></div>
	</div>
</form>
