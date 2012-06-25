{% extends base %}

{% block pageTitle %}Galaxy Maps{% endblock %}

{% block content %}
<h2>Galaxy Maps</h2>

<p>
	Please choose from one of the maps below, all maps update on a daily basis automatically<br />
	A few tips and tricks for using the maps:
	<ul>
		<li>If you adjust the final number in the URL you can change the map scale (/maps/colonised/8 would be 1:8 scale)</li>
		<li>Hovering over a system dot or name will give you the full system name and faction controlling it</li>
		<li>Clicking on a dot or system name will take you to that system's overview page with more info on it</li>
	</ul>
</p>

<p>
	Maps:
	<ul>
		<li><a href="{% url /index.php/maps/colonised/7 %}">All Colonised Systems and Stations on a 1:7 Scale</a></li>
		<li><a href="{% url /index.php/maps/stations/8 %}">All Stations on a 1:8 Scale</a></li>
		<li><a href="{% url /index.php/maps/government/2 %}">All Government Systems on a 1:2 Scale (Recommended for New Players)</a></li>
		<li><a href="{% url /index.php/maps/government/1 %}">All Government Systems on a 1:1 Scale (Recommended for New Players)</a></li>
	</ul>
</p>
{% endblock %}