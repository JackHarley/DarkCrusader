<div id="form">
	<h2>Get System Stats</h2>
	
	<p>
		Simply enter the system name to get stats for
	</p>
	
	<p>
		System Name:<br />
		<input type="text" id="system" /><br />
		
		<input type="submit" onclick="getSystemStats();" value="Get Stats" />
	</p>
</div>

<div id="results" style="display:none;">
	<h2>System stats for <span id="systemName"></span></h2>
	
	<table cellpadding="2" border="0">
		<tr>
			<td>Faction:</td>
			<td><span id="owner"></span></td>
		</tr>
		<tr>
			<td>Location:</td>
			<td><span id="location"></span></td>
		</tr>
	</table>
	
	<h3>Historical Information</h3>
	<table cellpadding="2" border="0" id="history">
		<tr>
			<th>Time</th>
			<th>Faction</th>
			<th>Has Station?</th>
		</tr>
	</table>
	
	<p>
		<a onclick="goToCurrentPage();">Search for another system</a>
	</p>
</div>