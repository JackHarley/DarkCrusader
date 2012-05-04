<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="{% viewurl /style.css %}" />
		<!--<link href="{% viewurl /images/chat.png %}" rel="shortcut icon" type="image/png"/>!-->
		<!--<link rel="apple-touch-icon" media="screen and (resolution: 163dpi)" href="{% viewurl /images/chat57.png %}" />
		<link rel="apple-touch-icon" media="screen and (resolution: 132dpi)" href="{% viewurl /images/chat72.png %}" />
		<link rel="apple-touch-icon" media="screen and (resolution: 326dpi)" href="{% viewurl /images/chat114.png %}" />!-->
		
		<title>DarkCrusader -> {% block pageTitle %}{% endblock %}</title>
		
		{% block jsOne %}
			<script type="text/javascript" src="http://code.jquery.com/jquery-1.5.1.min.js"></script>
		{% endblock %}
		
		{% block jsTwo %}
			<script type="text/javascript">
				$(document).ready(goToCurrentPage());
				
				function goToCurrentPage() {
					newHash = (window.location.hash == '') ? '#home' : window.location.hash;
					$('#content').fadeOut();
					
					setTimeout(function() {
						$('#content').empty();
						$.post('{% url /index.php/ajax/getpage %}', {hash: newHash}, processPage, 'html');
					}, 250);
				}
				
				function processPage(response) {
					$('#content').html(response);
					$('#content').fadeIn();
					
					if (response == '<h2>You have been logged out...</h2>') {
						setTimeout(function() {
							window.location = '{% url /index.php %}';
						}, 2000);
					}
				}
				
				function login() {
					username = $('#username').val();
					password = $('#password').val();
					
					$.post('{% url /index.php/ajax/login %}', {username: username, password: password}, processLoginResult, 'json');
				}
				
				function register() {
					username = $('#username').val();
					password = $('#password').val();
					
					$.post('{% url /index.php/ajax/register %}', {username: username, password: password}, processRegisterResult, 'json');
				}
					
				function processLoginResult(response) {
					if (response.result == 1) {
						$('#status').html('<span style="color:lime">Login successful! Redirecting...</span>');
						
						setTimeout(function() {
							window.location = '{% url /index.php %}';
						}, 2000);
					}
					if (response.result == 0) {
						$('#status').html('<span style="color:red">Login failed, please try again</span>');
						
						setTimeout(function() {
							$('#status').fadeOut("slow");
							
							setTimeout(function() {
								$('#status').empty();
								$('#status').show();
							}, 1000);
							
						}, 2000);
					}
				}
				
				function processRegisterResult(response) {
					if (response.result == 1) {
						$('#status').html('<span style="color:lime">Registration successful! Redirecting to login...</span>');
						
						setTimeout(function() {
							window.location = '{% url /index.php %}#login';
						}, 2000);
					}
					if (response.result == 0) {
						$('#status').html('<span style="color:red">Registration failed, please try again</span>');
						
						setTimeout(function() {
							$('#status').fadeOut("slow");
							
							setTimeout(function() {
								$('#status').empty();
								$('#status').show();
							}, 1000);
							
						}, 2000);
					}
				}
				
				function toggleScanInstructions() {
					toggletext = $('#scaninstructionslink').text();
					
					if (toggletext == 'Click here to see full instructions.') {
						$('#scaninstructions').slideDown();
						$('#scaninstructionslink').text('Close instructions');
					}
					else {
						$('#scaninstructions').slideUp();
						$('#scaninstructionslink').text('Click here to see full instructions.');
					}
				}
				
				function submitScan() {
					paste = $('#scanpaste').val();
					
					$.post('{% url /index.php/ajax/addscan %}', {paste: paste}, processSubmitScanResult, 'json');
				}
				
				function processSubmitScanResult(response) {
					if (response.moon == 0) {
						html = 'Scan for '+response.system+' '+response.planet+' submitted successfully!<br />';
					}
					else {
						html = 'Scan for '+response.system+' '+response.planet+' M'+response.moon+' submitted successfully!<br />';
					}
					
					for(n in response.resources) {
						
						html = html+response.resources[n].name+' '+response.resources[n].quality+' ('+response.resources[n].rate+'/hour)<br />';
					}
					
					$('#scansubmissionresult').fadeOut();

					setTimeout(function() {
						$('#scanpaste').val('');
						$('#scansubmissionresult').html(html);
						$('#scansubmissionresult').fadeIn();
					}, 500);
				}
				
				function searchResource() {
					resource = $('#resource').val();
				
					$.post('{% url /index.php/ajax/searchresource %}', {name: resource}, processSearchResource, 'json');
				}

				function processSearchResource() {

				}
				
				function getPlayerStats() {
					player = $('#player').val();
				
					$.post('{% url /index.php/ajax/getplayerstats %}', {name: player}, processPlayerStats, 'json');
				}
				
				function processPlayerStats(response) {
					$("#playerName").text(response.player_name);
					$("#leaderboardPositionXP").text(response.leaderboard_position_xp);
					$("#currentRank").text(response.current_rank);
					$("#totalXP").text(response.total_xp);
					
					$("#credits").text(response.credits);
					$("#leaderboardPositionCredit").text(response.leaderboard_position_credit);
					
					$("#colonies").text(response.colonies);
					$("#population").text(response.population);
					$("#leaderboardPositionEmpire").text(response.leaderboard_position_empire);
					
					$("#bounty").text(response.bounty);
					$("#leaderboardPositionBounty").text(response.leaderboard_position_bounty);
					
					$("#latestXPChange").text(response.latest_xp_change);
					
					$("#form").fadeOut();
					$("#results").fadeIn();
				}
				
				function getSystemStats() {
				system = $('#system').val();
				
					$.post('{% url /index.php/ajax/getsystemstats %}', {name: system}, processSystemStats, 'json');
				}
				
				function processSystemStats(response) {
					$("#systemName").text(response.system_name);
					$("#owner").text(response.owner);
					$("#location").text(response.location);
					
					for(record in response.history) {
						if (response.history[record].has_station == 1) {
							hasStation = "Yes";
						}
						else {
							hasStation = "No";
						}
						
						faction = response.history[record].faction;
						time = response.history[record].time;
						
						html = '<tr><td>'+time+'</td><td>'+faction+'</td><td>'+hasStation+'</td></tr>';
						$(html).appendTo("#history");
					}
					
					$("#form").fadeOut();
					$("#results").fadeIn();
				}
				
				function getFactionStats() {
					faction = $('#faction').val();
				
					$.post('{% url /index.php/ajax/getfactionstats %}', {name: faction}, processFactionStats, 'json');
				}
				
				function processFactionStats(response) {
					$("#factionName").text(response.faction_name);
					$("#systems").text(response.number_of_owned_systems);
					$("#stationSystems").text(response.number_of_owned_station_systems);
					
					factionName = response.faction_name;
					factionName = factionName.split(' ').join('_')
					systemsChart = "{% url /graphs %}/"+factionName+"-Systems.png";
					stationSystemsChart = "{% url /graphs %}/"+factionName+"-StationSystems.png";
					$("#systemsChart").attr("src", systemsChart);
					$("#stationSystemsChart").attr("src", stationSystemsChart);
					
					$("#form").fadeOut();
					$("#results").fadeIn();
				}
				
				function getLocalityInformation() {
					quadrant = $('#quadrant').val();
					sector = $('#sector').val();
					region = $('#region').val();
					locality = $('#locality').val();
				
					$.post('{% url /index.php/ajax/getlocalityinformation %}', {quadrant: quadrant, sector: sector, region: region, locality: locality}, processLocalityInformation, 'json');
				}
				
				function processLocalityInformation(response) {
					$("#location").text(response.location);
					
					for(system in response.systems) {
						planetsScanned = response.systems[system].planetsScanned;
						planetsScannedByUser = response.systems[system].planetsScannedByUser;
						
						if (planetsScanned > 0) {
							scannedColor = 'lime';
						}
						else {
							scannedColor = 'red';
						}
						
						if (planetsScannedByUser > 0) {
							scannedByUserColor = 'lime';
						}
						else {
							scannedByUserColor = 'red';
						}
						
						html = '<tr><td>'+system+'</td><td style="color:'+scannedColor+'">'+planetsScanned+'</td><td style="color:'+scannedByUserColor+'">'+planetsScannedByUser+'</td>';
						$(html).appendTo('#systeminfo');
					}
					
					$("#form").fadeOut();
					$("#results").fadeIn();
				}
				
				window.onhashchange = goToCurrentPage;
			</script>
		{% endblock %}
	<body>
		<div id="container">
			<div id="header">
				<img src="{% viewurl /images/logo.png %}" />
				<h2><a href="#home">Home</a> | <a href="#info">Info</a> | <a href="#kos">KoS</a> | <a href="#blog">Blog</a> | <a href="#scans">Scans</a> | <a href="#playerstats">Player Stats</a> | <a href="#systems">Systems</a> | <a href="#factions">Factions</a> {% if empty activeUser.user %} | <a href="#register">Register</a> | <a href="#login">Login</a>{% else %} | <a href="#logout">Logout</a>{% endif %}</h2>
			</div>
			
			<div id="page">
				<div id="content">
				</div>
			</div>
		</div>
	</body>
</html>