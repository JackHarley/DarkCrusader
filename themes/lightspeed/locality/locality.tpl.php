{% extends base %}

{% block pageTitle %}Locality - {{location}}{% endblock %}

{% block content %}
<h2>Locality information for {{location}}</h2>
	
<h3>System Info</h3>

{% if exists hasAccessToScans %}
<p>
	Number of Systems With At Least One Scan Submitted:<br />
	{{number_of_systems_with_scan}}/{{number_of_systems}}<br />
	<progress value="{{number_of_systems_with_scan}}" max="{{number_of_systems}}" />
</p>

<p>
	Number of Systems With At Least One Scan Submitted By You:<br />
	{{number_of_systems_with_scan_by_user}}/{{number_of_systems}}<br />
	<progress value="{{number_of_systems_with_scan_by_user}}" max="{{number_of_systems}}" />
</p>
{% endif %}

<p>
	<table cellpadding="2" border="0">
		<tr>
			<th>System Name</th>
			{% if exists hasAccessToScans %}
				<th>Objs Scanned</th>
				<th>Objs Scanned by You</th>
			{% endif %}
			<th>Faction</th>
		</tr>

		{% for system in systems %}
			{% if forloop.counter0|divisibleby:2 %}
				<tr style="background-color:#333333">
			{% else %}
				<tr>
			{% endif %}
				<td><a href="{% url /index.php/systems %}?name={{system.system_name}}">{{system.system_name}}</a></td>
				{% if exists hasAccessToScans %}
					<td>
						{% if system.objects_scanned > 0 %}
							<span style="color:lime">{{system.objects_scanned}}</span>
						{% else %}
							{{system.objects_scanned}}
						{% endif %}
					</td>
					<td>
						{% if system.objects_scanned_by_user > 0 %}
							<span style="color:lime">{{system.objects_scanned_by_user}}</span>
						{% else %}
							{{system.objects_scanned_by_user}}
						{% endif %}
					</td>
				{% endif %}
				<td><a href="{% url /index.php/factions %}?name={{system.stats.faction|urlencode}}">{{system.stats.faction}}</a></td>
			</tr>
		{% endfor %}
	</table>
</p>
{% endblock %}