<div id="form">
	<h2>Get Player Stats</h2>
	
	<p>
		Simply enter the player name you wish to search for, they must be Light Class C or above to be in the database. Stats are refreshed midnight GMT.
	</p>
	
	<p>
		Player Name:<br />
		<input type="text" id="player" /><br />
		
		<input type="submit" onclick="getPlayerStats();" value="Get Stats" />
	</p>
</div>

<div id="results" style="display:none;">
	<h2>Player stats for <span id="playerName"></span></h2>
	
	<table cellpadding="2" border="0">
		<tr>
			<td>Rank/XP Leaderboard Position:</td>
			<td><span id="leaderboardPositionXP"></span></td>
		</tr>
		<tr>
			<td>Current Rank:</td>
			<td><span id="currentRank"></span></td>
		</tr>
		<tr>
			<td>Total XP:</td>
			<td><span id="totalXP"></span></td>
		</tr>
		
		<tr><td>&nbsp;</td></tr>
		
		<tr>
			<td>Credits Leaderboard Position:</td>
			<td><span id="leaderboardPositionCredit"></span></td>
		</tr>
		<tr>
			<td>Credits:</td>
			<td><span id="credits"></span></td>
		</tr>
		
		<tr><td>&nbsp;</td></tr>
		
		<tr>
			<td>Empire Leaderboard Position:</td>
			<td><span id="leaderboardPositionEmpire"></span></td>
		</tr>
		<tr>
			<td>Colonies:</td>
			<td><span id="colonies"></span></td>
		</tr>
		<tr>
			<td>Population:</td>
			<td><span id="population"></span></td>
		</tr>
		
		<tr><td>&nbsp;</td></tr>
		
		<tr>
			<td>Bounty Leaderboard Position:</td>
			<td><span id="leaderboardPositionBounty"></span></td>
		</tr>
		<tr>
			<td>Bounty in Credits:</td>
			<td><span id="bounty"></span></td>
		</tr>
		
		<tr><td>&nbsp;</td></tr>
		
		<tr>
			<td>Latest XP Change (correct to ~24 hours):</td>
			<td><span id="latestXPChange"></span></td>
		</tr>
	</table>
	
	<p>
		<a onclick="goToCurrentPage();">Search for another player</a>
	</p>
</div>