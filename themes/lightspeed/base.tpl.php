<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>SWAT/FIRE -> {% block pageTitle %}{% endblock %}</title>
	<link rel="stylesheet" type="text/css" href="{% viewurl /style.css %}" media="screen" />
</head>

<body>
<!-- begin wrap -->
<div id="wrap">
	<!-- begin header -->
	<div id="header">
		<h1><a href="{% url /index.php %}">SWAT / FIRE</a></h1>
		<h2>Official Site</h2>
		<div style="height:5px"></div>
		<ul>
			<li><a href="{% url /index.php %}">home</a></li>
			<li><a href="http://forums.tacticalresponseteam.in">forums</a></li>
			<li><a href="{% url /index.php/info %}">swat info</a></li>
			<li><a href="{% url /index.php/scans %}">scans</a></li>
			<li><a href="{% url /index.php/stats %}">stats</a></li>
			<li><a href="{% url /index.php/market %}">market</a></li>
			<li><a href="{% url /index.php/faq %}">faq</a></li>
			
			{% if exists userCanAccessFactionBank %}
				<li><a href="{% url /index.php/factionbank %}">faction bank</a></li>
			{% endif %}

			<li><a href="{% url /index.php/personalbank %}">personal bank</a> </li>

			{% if exists userIsAdmin %}
				<li><a href="{% url /index.php/admin %}">acp</a></li>
			{% endif %}

			{% if empty activeUser.user %}
				<li><a href="{% url /index.php/user/register %}">register</a></li>
				<li><a href="{% url /index.php/user/login %}">login</a></li>
			{% else %}
				<li><a href="{% url /index.php/user/logout %}">logout</a></li>
			{% endif %}
		</ul>
	</div>
	<!-- end header -->
	<!-- begin page -->
	<div id="page">
		<!-- begin content -->
		<div id="content">
			<div id="pagecontent">
				{% block alerts %}
					{% if exists alerts %}
						{% for alert in alerts %}
							<div class="alert-message {{alert.type}}">
								{{alert.message}}
							</div>
						{% endfor %}
					{% endif %}
				{% endblock %}

				{% block content %}
				{% endblock %}
			</div>
		</div>
		<!-- end content -->
		<!-- begin sidebar -->
		<div id="sidebar">
			<ul>
				{% if exists activeUser %}
				<li>
					<h2>{{activeUser.username}}</h2>
					<div class="pagecontent">
						<p>
							Site Bank Balance: {{activeUser.balance|numberformat}}c<br />
							Premium: 
							{% if exists userIsPremium %}
								<span style="color:lime">Yes</span>
							{% else %}
								<span style="color:red">No</span>
							{% endif %}
							<br />
							<br />
							See <a href="{% url /index.php/faq %}">the FAQ</a> for more info on premium and the site bank<br />
							<br />
							<a href="{% url /index.php/user %}">Account Settings</a><br />
							<a href="{% url /index.php/user/characters %}">Manage Characters</a>
						</p>
					</div>
				</li>
				{% endif %}
				<li>
					<h2>Statistics</h2>
					<ul>
						<li><a href="{% url /index.php/players %}">Player Stats</a></li>
						<li><a href="{% url /index.php/systems %}">System Stats</a></li>
						<li><a href="{% url /index.php/factions %}">Faction Stats</a></li>
						<li><a href="{% url /index.php/locality %}">Locality Stats</a></li>
					</ul>
				</li>
				<li>
					<h2>Attention!</h2>
					<div class="pagecontent">
						<p>	
							All SWAT/FIRE members need to Register on this site AND on the forums. The accounts systems for the 2 sites are separate!<br />
							Once you have signed up, PM Jedi Jackian in game to get your privileges!
						</p>
					</div>
				</li>
			</ul>
		</div>
		<!-- end sidebar -->
	</div>
	<!-- end page -->

	<div id="footer">
	</div>
	<!-- end footer -->
</div>
<!-- end wrap -->
</body>
</html>
