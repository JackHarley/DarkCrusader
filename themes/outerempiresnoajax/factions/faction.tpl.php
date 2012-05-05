{% extends base %}

{% block pageTitle %}Faction Stats - {{faction.name}}{% endblock %}

{% block content %}
	<h2>Faction stats for {{faction.name}}</h2>
	
	<table cellpadding="2" border="0">
		<tr>
			<td>Number of Owned Systems:</td>
			<td>{{faction.number_of_owned_systems}}</td>
		</tr>
		<tr>
			<td>Number of Owned Station Systems:</td>
			<td>{{faction.number_of_owned_station_systems}}</td>
		</tr>
	</table>
	
	<h3>Charts</h3>
	<p>
		<img src="{% url /graphs %}/{{faction.systems_chart_url}}" style="background-color:white;"/>
	</p>
	<p>
		<img src="{% url /graphs %}/{{faction.station_systems_chart_url}}" style="background-color:white;"/>
	</p>
{% endblock %}