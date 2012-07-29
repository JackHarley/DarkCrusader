{% extends base %}

{% block pageTitle %}Scans{% endblock %}

{% block content %}

<h2>Scans</h2>

<p>
	Please select an option:
</p>

<p>
	<ul>
		<li><a href="{% url /index.php/scans/submit %}">Submit scan</a><br /></li>
		<li><a href="{% url /index.php/scans/search %}">Search for a resource</a></li>
		<li><a href="{% url /index.php/stats %}">Search for a locality/system</a></li>
		<li><a href="{% url /index.php/localities/scanplan %}">Create an optimized scan route for a locality</a></li>
	</ul>
</p>

<h3>Latest Submitted Scans</h3>

<p>
	<table cellpadding="0" border="0">
		<tr>
			<th>Location&nbsp;&nbsp;</th>
			<th>Submitter</th>
			<th>Scanner Level</th>
			<th>Resources</th>
		</tr>
		
		{% for scan in latestScans %}
			<tr>
				<td>{{scan.location_string|unescape}}</td>
				<td>{{scan.submitter.username}}</td>
				<td>{{scan.scanner_level}}</td>
				<td>
					{% for result in scan.scan_results %}{% if ! forloop.first %}, {% endif %}{{result.resource_string}}{% endfor %}
				</td>
			</tr>
		{% endfor %}
	</table>
</p>

{% endblock %}