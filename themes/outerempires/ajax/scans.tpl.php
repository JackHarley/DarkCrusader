<h2>Scans</h2>

<p>
	Please select an option:
</p>

<p>
	<a href="#submitscan">Submit scan</a><br />
	<a href="#searchresource">Search for a resource</a><br />
	<a href="#searchsystem">Search for a system</a><br />
	<a href="#localityinformation">Get locality information</a><br />
</p>

<h3>Latest Submitted Scans</h3>

<p>
	<table cellpadding="0" border="0">
		<tr>
			<th>Location&nbsp;&nbsp;</th>
			<th>Resource</th>
		</tr>
		
		{% for scan in latestScans %}
			<tr>
				<td>{{scan.location}}&nbsp;&nbsp;</td>
				<td>{{scan.resource}}</td>
			</tr>
		{% endfor %}
	</table>
</p>