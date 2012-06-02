<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="{% viewurl /style.css %}" />
		
		<title>{{siteName}} - {% block pageTitle %}{% endblock %}</title>
		
		{% block jsOne %}
			<script type="text/javascript" src="http://code.jquery.com/jquery-1.5.1.min.js"></script>
		{% endblock %}
		
		<!-- google analytics -->
		{% if ! exists userIsAdmin %}
			{% if exists googleAnalyticsCode %}
				<script type="text/javascript">
					var _gaq = _gaq || [];
					_gaq.push(['_setAccount', '{{googleAnalyticsCode}}']);
					_gaq.push(['_trackPageview']);

					(function() {
					var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
					ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
					var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
					})();
				</script>
			{% endif %}
		{% endif %}

	</head>
	<body>
		<div id="container">
			<div id="header">
				<img src="{% viewurl /images/logo.png %}" />
				<h2><a href="{% url /index.php/home %}">Home</a> | <a href="http://forums.tacticalresponseteam.in">Forums</a> | <a href="{% url /index.php/info %}">Info</a> | <a href="{% url /index.php/scans %}">Scans</a> | <a href="{% url /index.php/stats %}">Stats</a> | {% if exists userCanAccessFactionBank %}<a href="{% url /index.php/factionbank %}">Faction Bank</a> |{% endif %} <a href="{% url /index.php/personalbank %}">Personal Bank</a> {% if exists userIsAdmin %}| <a href="{% url /index.php/admin %}">ACP</a>{% endif %}{% if empty activeUser.user %} | <a href="{% url /index.php/user/register %}">Register</a> | <a href="{% url /index.php/user/login %}">Login</a>{% else %} | <a href="{% url /index.php/user/logout %}">Logout</a>{% endif %}</h2>
			</div>
			
			<div id="page">
				<div id="content">
					{% block alerts %}
						{% if exists alerts %}
							{% for alert in alerts %}
								<div class="alert-message {{alert.type}}">
									{{alert.message}}
								</div>
							{% endfor %}
						{% endif %}
					{% endblock %}

					{% block content %}{% endblock %}
				</div>
			</div>
		</div>
	</body>
</html>