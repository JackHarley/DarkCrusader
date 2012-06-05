{% extends base %}

{% block pageTitle %}Register{% endblock %}

{% block content %}

<h2>Register</h2>

<p>
	<form action="" method="post">
		Username:<br />
		<input type="text" name="username" /><br />
		<br />
		Password:<br />
		<input type="password" name="password" /><br />
		<br />
		<input type="submit" name="submit" value="Register" />
	</form>
</p>
{% endblock %}