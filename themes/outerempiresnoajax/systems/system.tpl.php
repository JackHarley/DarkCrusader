{% extends base %}

{% block pageTitle %}System Stats - {{system.system_name}}{% endblock %}

{% block content %}

<h2>System stats for {{system.system_name}}</h2>
	
<table cellpadding="2" border="0">
	<tr>
		<td>Faction:</td>
		<td>{{system.stats.faction}}</td>
	</tr>
	<tr>
		<td>Location:</td>
		<td>{{system.location}}</td>
	</tr>
	<tr>
		<td>Station?</td>
		<td>{% if system.stats.has_station == 1%}Yes{% else %}No{% endif %}</td>
	</tr>
</table>

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

{% endblock %}