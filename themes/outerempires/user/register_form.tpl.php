{% extends base %}

{% block pageTitle %}Login{% endblock %}

{% block content %}

{% if !empty error %}
	<p style="color:red;text-align:center">
		{{error}}
	</p>
{% endif %}

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