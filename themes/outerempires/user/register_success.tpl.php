{% extends base %}

{% block pageTitle %}Register{% endblock %}

{% block content %}
<meta http-equiv="refresh" content="3;{% url /index.php/user/login %}">

<p>
	Registration Successful!<br />
	You can now login, redirecting you to the login page<br />
	<br />
	Redirecting...
</p>
{% endblock %}