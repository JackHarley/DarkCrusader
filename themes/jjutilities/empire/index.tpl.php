{% extends base %}

{% block pageTitle %}Empire{% endblock %}

{% block content %}
<h2>Empire Management</h2>

<p>
	<a href="{% url /index.php/empire/seller %}">See an overview of your market sales</a><br />
	<a href="{% url /index.php/empire/resources %}">See an overview of the resources you have stored around the galaxy</a>
</p>

{% endblock %}