{% extends base %}

{% block pageTitle %}System Stats{% endblock %}

{% block content %}

<h2>System Stats</h2>

<h3>Individual System Statistics</h3>

<form action="" method="get">
	<p>
		System Name:<br />
		<input type="text" name="name" /><br />
		
		<input type="submit" value="Get Stats" />
	</p>
</form>

<h3>Global System Statistics</h3>

<p>
	Coming Soon
</p>

<h3>Charts</h3>

<p>
	The below chart breaks down all the colonised systems into which factions control them, note that factions controling less than 5% of colonised systems are listed as "Other Factions"
</p>

<img src="{% url /graphs/controlledsystems.png %}" />

<br />

{% endblock %}