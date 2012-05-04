{% extends base %}

{% block pageTitle %}Scans{% endblock %}

{% block content %}

<h2>Scans</h2>

<p>
	Please select an option:
</p>

<p>
	<a href="{% url /index.php/scans/submit %}">Submit scan</a><br />
	<a href="{% url /index.php/scans/resource %}">Search for a resource</a><br />
	<a href="{% url /index.php/scans/system %}">Search for a system</a><br />
	<a href="{% url /index.php/scans/locality %}">Get locality information</a><br />
</p>

<h3>Latest Submitted Scans</h3>

<p>
	<table cellpadding="0" border="0">
		<tr>
			<th>Location&nbsp;&nbsp;</th>
			<th>Submitter</th>
			<th>Time</th>
			<th>Planetary Scan Level</th>
		</tr>
		
		{% for scan in latestScans %}
			<tr>
				<td>{{scan.location_string}}&nbsp;&nbsp;</td>
				<td>{{scan.submitter.username}}</td>
				<td>{{scan.date_submitted}}</td>
				<td>{{scan.scanner_level}}</td>
			</tr>
		{% endfor %}
	</table>
</p>

{% endblock %}