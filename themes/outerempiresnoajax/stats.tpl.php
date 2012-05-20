{% extends base %}

{% block pageTitle %}Stats{% endblock %}

{% block content %}
<h2>Stats</h2>

<p>
	Choose which stats you wish to look up
</p>

<p>
	<ul>
		<li><a href="{% url /index.php/playerstats %}">Player Stats</a></li>
		<li><a href="{% url /index.php/systems %}">System Stats</a></li>
		<li><a href="{% url /index.php/factions %}">Faction Stats</a></li>
		<li><a href="{% url /index.php/locality %}">Locality Stats</a></li>
	<ul>
</p>

{% endblock %}