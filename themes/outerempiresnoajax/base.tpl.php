<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="{% viewurl /style.css %}" />
		<!--<link href="{% viewurl /images/chat.png %}" rel="shortcut icon" type="image/png"/>!-->
		<!--<link rel="apple-touch-icon" media="screen and (resolution: 163dpi)" href="{% viewurl /images/chat57.png %}" />
		<link rel="apple-touch-icon" media="screen and (resolution: 132dpi)" href="{% viewurl /images/chat72.png %}" />
		<link rel="apple-touch-icon" media="screen and (resolution: 326dpi)" href="{% viewurl /images/chat114.png %}" />!-->
		
		<title>{{siteName}} - {% block pageTitle %}{% endblock %}</title>
		
		{% block jsOne %}
			<script type="text/javascript" src="http://code.jquery.com/jquery-1.5.1.min.js"></script>
		{% endblock %}
		
	<body>
		<div id="container">
			<div id="header">
				<img src="{% viewurl /images/logo.png %}" />
				<h2><a href="{% url /index.php/home %}">Home</a> | <a href="{% url /forums %}">Forums</a> | <a href="{% url /index.php/info %}">Info</a> | <a href="{% url /index.php/scans %}">Scans</a> | <a href="{% url /index.php/playerstats %}">Player Stats</a> | <a href="{% url /index.php/systems %}">System Stats</a> | <a href="{% url /index.php/factions %}">Faction Stats</a> {% if empty activeUser.user %} | <a href="{% url /index.php/user/register %}">Register</a> | <a href="{% url /index.php/user/login %}">Login</a>{% else %} | <a href="{% url /index.php/user/logout %}">Logout</a>{% endif %}</h2>
			</div>
			
			<div id="page">
				<div id="content">
					{% block content %}{% endblock %}
				</div>
			</div>
		</div>
	</body>
</html>