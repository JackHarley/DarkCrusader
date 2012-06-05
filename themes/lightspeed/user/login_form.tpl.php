{% extends base %}

{% block pageTitle %}Login{% endblock %}

{% block content %}

<h2>Login</h2>

<p>
	<form action="" method="post">
		Username:<br />
		<input type="text" name="username" /><br />
		<br />
		Password:<br />
		<input type="password" name="password" /><br />
		<br />
		Remember Me? <input type="checkbox" name="remember_me" value="yes" /><br />
		<br />
		<input type="submit" name="submit" value="Login" />
	</form>
</p>
{% endblock %}