<div id="form">
	<h2>Get Faction Stats</h2>
	
	<p>
		Simply enter the faction name to get stats for
	</p>
	
	<p>
		Faction Name:<br />
		<input type="text" id="faction" /><br />
		
		<input type="submit" onclick="getFactionStats();" value="Get Stats" />
	</p>
</div>

<div id="results" style="display:none;">
	<h2>Faction stats for <span id="factionName"></span></h2>
	
	<table cellpadding="2" border="0">
		<tr>
			<td>Number of Owned Systems:</td>
			<td><span id="systems"></span></td>
			<td><span id="stats"></span></td>
		</tr>
		<tr>
			<td>Number of Owned Station Systems:</td>
			<td><span id="stationSystems"></span></td>
			<td><span id="stationStats"></span></td>
		</tr>
	</table>
	
	<h3>Charts</h3>
	<p>
		<img src="" id="systemsChart" style="background-color:white;"/>
	</p>
	<p>
		<img src="" id="stationSystemsChart" style="background-color:white;"/>
	</p>
	
	<p>
		<a onclick="goToCurrentPage();">Search for another system</a>
	</p>
</div>