<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="{% viewurl /style.css %}" />
		<!--<link href="{% viewurl /images/chat.png %}" rel="shortcut icon" type="image/png"/>!-->
		<!--<link rel="apple-touch-icon" media="screen and (resolution: 163dpi)" href="{% viewurl /images/chat57.png %}" />
		<link rel="apple-touch-icon" media="screen and (resolution: 132dpi)" href="{% viewurl /images/chat72.png %}" />
		<link rel="apple-touch-icon" media="screen and (resolution: 326dpi)" href="{% viewurl /images/chat114.png %}" />!-->
		
		<title>DarkCrusader -> Admin</title>
	</head>
	
	<body>
		<div id="container">
			<div id="header">
				<img src="{% viewurl /images/logo.png %}" />
				<h2><a href="{% url /index.php %}">Site Home</a> | <a href="{% url /index.php/admin/kos %}">Manage KoS List</a> | <a href="{% url /index.php/admin/users %}">Manage Users</a>
			</div>
			
			<div id="page">
				{% block content %}{% endblock %}
			</div>
		</div>
	</body>
</html>