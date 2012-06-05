{% extends base %}

{% block pageTitle %}Faction Stats - {{faction.name}}{% endblock %}

{% block content %}
	<h2>Faction stats for {{faction.name}}</h2>
	
	<p>
		Number of Controlled Systems: {{faction.number_of_owned_systems}}<br />
		Number of Controlled Station Systems: {{faction.number_of_owned_station_systems}}
	</p>

	<h2>Charts</h2>
	<p>
		<img src="{% url /graphs %}/{{faction.systems_chart_url}}" style="background-color:white;"/>
	</p>
	<p>
		<img src="{% url /graphs %}/{{faction.station_systems_chart_url}}" style="background-color:white;"/>
	</p>
{% endblock %}