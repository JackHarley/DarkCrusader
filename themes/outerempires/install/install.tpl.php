{% extends base %}

{% block pageTitle %}Install{% endblock %}

{% block content %}
<h2>Install Dark Crusader Database</h2>

<p>
	Ready to install...<br />
	This will wipe your database clean before installing
</p>

<p>
	Please enter the details for the first administrator user
</p>

<p>
	<form action="" method="post">
		Username:<br />
		<input type="text" name="username" /><br />
		Password:<br />
		<input type="password" name="password" /><br />
		<br />
		<input type="submit" name="submit" value="Install" />
	</form>
</p>
{% endblock %}