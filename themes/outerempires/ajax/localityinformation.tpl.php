<div id="form">
	<h2>Get Locality Information</h2>
	
	<p>
		Simply enter the locality you want to search for
	</p>
	
	<p>
		Locality:<br />
		<input type="text" id="quadrant" />:<input type="text" id="sector" />:<input type="text" id="region" />:<input type="text" id="locality" /><br />
		
		<input type="submit" onclick="getLocalityInformation();" value="Get Locality" />
	</p>
</div>

<div id="results" style="display:none;">
	<h2>Locality information for <span id="location"></span></h2>
	
	<h3>System Info</h3>
	<table cellpadding="2" border="1" id="systeminfo">
		<tr>
			<th>System Name</th>
			<th>Number of Planets Scanned</th>
			<th>Number of Planets Scanned by You</th>
		</tr>
	</table>
	
	<p>
		<a onclick="goToCurrentPage();">Get information for another locality</a>
	</p>
</div>