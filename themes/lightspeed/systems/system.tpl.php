{% extends base %}

{% block pageTitle %}System Stats - {{system.system_name}}{% endblock %}

{% block content %}

<h2>System stats for {{system.system_name}}</h2>

<p>
	Faction: {{system.stats.faction}}<br />
	Location: <a href="{% url /index.php/locality %}?q={{system.quadrant}}&s={{system.sector}}&r={{system.region}}&l={{system.locality}}">{{system.location}}</a><br />
	Station? {% if system.stats.has_station == 1%}Yes{% else %}No{% endif %}<br />
	<a href="{% url /index.php/maps/colonised/7 %}/{{system.system_name}}">View {{system.system_name}} on the galaxy map</a>
</p>

{% if exists scans %}

	<h2>Scans</h2>

	{% if scans == "none" %}
		<p>No scans available for this system</p>
	{% else %}
		{% for scan in scans %}
		<p>
			{{scan.location_string|unescape}} - {{scan.submitter.username}} - Rating: {{scan.scanner_level}}
			<ul>
			{% for result in scan.scan_results %}
				<li><i>{{result.resource_string}}</i></li>
			{% endfor %}
			</ul>
		</p>
		{% endfor %}
	{% endif %}

{% endif %} 

{% if ! empty historicalStats %}
	<h3>Historical Information</h3>
	<table cellpadding="2" border="0" id="history">
		<tr>
			<th>Time</th>
			<th>Faction</th>
			<th>Has Station?</th>
		</tr>

		{% for historicalStatsSet in historicalStats %}
			<tr>
				<td>{{historicalStatsSet.set.time}}</td>
				<td>{{historicalStatsSet.faction}}</td>
				<td>{% if historicalStatsSet.has_station == 1%}Yes{% else %}No{% endif %}</td>
			</tr>
		{% endfor %}
	</table>
{% endif %}

{% endblock %}