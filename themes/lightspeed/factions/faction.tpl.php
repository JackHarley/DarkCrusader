{% extends base %}

{% block pageTitle %}Faction - {{faction.name}}{% endblock %}

{% block content %}
	<h2>{{faction.name}}</h2>
	
	<p>
		Number of Controlled Systems: {{faction.number_of_owned_systems}}<br />
		Number of Controlled Station Systems: {{faction.number_of_owned_station_systems}}
	</p>

	<h3>Controlled Systems</h3>
	<p>
		{% for system in controlledSystems %}{% if ! forloop.first %}, {% endif %}<a href="{% url /index.php/systems/system %}?name={{system.system_name}}">{{system.system_name}}</a>{% endfor %}
	</p>

	<h3>Charts</h3>
	<p>
		<img src="{% url /graphs %}/{{faction.systems_chart_url}}" style="background-color:white;"/>
	</p>
	<p>
		<img src="{% url /graphs %}/{{faction.station_systems_chart_url}}" style="background-color:white;"/>
	</p>
{% endblock %}