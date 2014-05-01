<div class="forgotPass">
	<button class="close">CLOSE</button>
	<h1>Forgot Your Password?</h1>
	<p>We will send password reset instructions to the email address associated with your account.</p>
	<div>
		<label for="fpEmail">Please type your email address:</label>
		<input id="fpEmail" type="email" placeholder="Email Address" />
		<label class="hidden" for="fpToken">Please copy and paste your password reset token:</label>
		<input class="hidden" id="fpToken" type="text" placeholder="Reset Token" />
		<label class="hidden" for="fpNewPass">
			<span>All passwords are required to have six (6) characters and at least one (1) of each of the following:</span>
			<ul>
				<li>A capital letter, A - Z</li>
				<li>A number, 0 - 9</li>
				<li>A special character, e.g. ! @ # $ & * etc.</li>
			</ul>
			Please type your new password:
		</label>
		<input class="hidden" id="fpNewPass" type="password" placeholder="New Password" />
		<label class="hidden" for="fpConfPass">Please confirm your new password:</label>
		<input class="hidden" id="fpConfPass" type="password" placeholder="Confirm Password" />
		<button class="send">SEND</button>
		<button class="cancel">CANCEL</button>
		<br />
		<a class="next token">Already have a token?</a>
	</div>
</div>
