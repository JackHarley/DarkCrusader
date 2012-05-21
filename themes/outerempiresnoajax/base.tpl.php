<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="{% viewurl /style.css %}" />
		
		<title>{{siteName}} - {% block pageTitle %}{% endblock %}</title>
		
		{% block jsOne %}
			<script type="text/javascript" src="http://code.jquery.com/jquery-1.5.1.min.js"></script>
		{% endblock %}
		
		<!-- google analytics -->
		<script type="text/javascript">
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-31924198-1']);
			_gaq.push(['_trackPageview']);

			(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script>

	</head>
	<body>
		<div id="container">
			<div id="header">
				<img src="{% viewurl /images/logo.png %}" />
				<h2><a href="{% url /index.php/home %}">Home</a> | <a href="{% url /forums %}">Forums</a> | <a href="{% url /index.php/info %}">Info</a> | <a href="{% url /index.php/scans %}">Scans</a> | <a href="{% url /index.php/stats %}">Stats</a> | <a href="{% url /index.php/bank %}">Bank</a> {% if exists userIsAdmin %}| <a href="{% url /index.php/admin %}">ACP</a>{% endif %}{% if empty activeUser.user %} | <a href="{% url /index.php/user/register %}">Register</a> | <a href="{% url /index.php/user/login %}">Login</a>{% else %} | <a href="{% url /index.php/user/logout %}">Logout</a>{% endif %}</h2>
			</div>
			
			<div id="page">
				<div id="content">
					{% if exists alert %}
						{% if alert.type == "success" %}
							<p style="color:lime">{{alert.message}}</p>
						{% else if alert.type == "error" %}
							<p style="color:red">{{alert.message}}</p>
						{% else if alert.type == "info" %}
							<p style="color:aqua">{{alert.message}}</p>
						{% else if alert.type == "warning" %}
							<p style="color:yellow">{{alert.message}}</p>
						{% endif %}
					{% endif %}

					{% block content %}{% endblock %}
				</div>
			</div>
		</div>
	</body>
</html>