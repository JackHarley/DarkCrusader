{% extends base %}

{% block pageTitle %}Systems{% endblock %}

{% block content %}

<h2>System Statistics</h2>

<h3>Graphs</h3>

<p>
	The below chart shows the number of colonised systems versus the number of uncolonised systems. (Government systems are included as colonised)
</p>

<img src="{% url /graphs/controlledsystems.png %}" />

<br />
<br />

<p>
	The below chart breaks down all the colonised systems into which factions control them, note that factions controling less than 5% of colonised systems are listed as "Other Factions"
</p>

<img src="{% url /graphs/controlledsystemsbyfaction.png %}" />

<br />

{% endblock %}