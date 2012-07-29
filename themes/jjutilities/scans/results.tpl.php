{% extends base %}

{% block pageTitle %}Search Results{% endblock %}

{% block content %}

<h2>Search Results</h2>

<table cellpadding="2" border="0">
	<tr>
		<th>Locality</th>
		<th>Location</th>
		<th>Resource</th>
		<th>Quality</th>
		<th>Rate</th>
		<th>Scanned by</th>
	</tr>

	{% for scan in scans %}
		{% if forloop.counter0|divisibleby:2 %}
			<tr style="background-color:#333333"> 
		{% else %}
			<tr>
		{% endif %}
			<td><a href="{% url /index.php/localities/locality %}?q={{scan.scan.system.quadrant}}&s={{scan.scan.system.sector}}&r={{scan.scan.system.region}}&l={{scan.scan.system.locality}}">{{scan.scan.system.location}}</a></td>
			<td>{{scan.scan.location_string|unescape}}</td>
			<td>{{scan.resource_name}}</td>
			<td>{{scan.resource_quality}}</td>
			<td>{{scan.resource_extraction_rate}}/hour</td>
			<td>{{scan.scan.submitter.username}}</td>
		</tr>
	{% endfor %}
</table>

{% endblock %}