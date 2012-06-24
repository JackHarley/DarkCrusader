{% extends base %}

{% block pageTitle %}Galaxy Maps{% endblock %}

{% block content %}
<h2>Galaxy Maps</h2>

<p>
	Please choose from one of the maps below, all maps update on a daily basis automatically
</p>

<p>
	<ul>
		<li><a href="{% url /index.php/maps/colonised/7 %}">All Colonised Systems and Stations on a 1:7 Scale (Recommended)</a></li>
		<li><a href="{% url /index.php/maps/government/2 %}">All Government Systems on a 1:2 Scale (Recommended for New Players)</a></li>
		<li><a href="{% url /index.php/maps/government/1 %}">All Government Systems on a 1:1 Scale (Recommended for New Players)</a></li>
	</ul>
</p>
{% endblock %}