<h2>KoS List</h2>
	
<p>
	Faction members are permitted to engage in a shoot on sight policy for the following players, and their colonies are fair game for explosives tests.
</p>
	
<table cellpadding="2" border="0">
	<tr>
		<th>Player Name</th>
		<th>Reason for Condemnation</th>
	</tr>
	
	{% for KoSEntry in KoSList %}
		<tr>
			<td>{{KoSEntry.player_name}}</td>
			<td>{{KoSEntry.reason}}</td>
		</tr>
	{% endfor %}
</table>