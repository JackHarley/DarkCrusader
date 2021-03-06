<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>JJ Utilities -> {% block pageTitle %}{% endblock %}</title>
	<link rel="stylesheet" type="text/css" href="{% viewurl /style.css %}" media="screen" />
	<link rel="icon" type="image/png" href="{% viewurl /images/colossusfavicon.png %}">
	<link rel="apple-touch-icon" sizes="57x57" href="{% viewurl /images/apple-touch-icon-57x57.png %}" />
	<link rel="apple-touch-icon" sizes="114x114" href="{% viewurl /images/apple-touch-icon-114x114.png %}" />
	<link rel="apple-touch-icon" sizes="72x72" href="{% viewurl /images/apple-touch-icon-72x72.png %}" />
	<link rel="apple-touch-icon" sizes="144x144" href="{% viewurl /images/apple-touch-icon-144x144.png %}" />
	<script src="http://code.jquery.com/jquery-1.8.2.min.js"></script>

	{% block js %}{% endblock %}
	
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
<!-- begin wrap -->
<div id="wrap">
	<!-- begin header -->
	<div id="header">
		<h1><a href="{% url /index.php %}">JJ Utilities</a></h1>
		<h2>Jedi Jackian's Utilities</h2>
		<div style="height:5px"></div>
		<ul>
			<li><a href="{% url /index.php %}">home</a></li>
			<li><a href="{% url /index.php/faq %}">faq</a></li>
			<li><a href="{% url /index.php/stats %}">stats & info</a></li>
			<li><a href="{% url /index.php/empire %}">empire</a></li>
			<li><a href="{% url /index.php/skills %}">skills</a></li>
			<li><a href="{% url /index.php/maps %}">maps</a></li>
			<li><a href="{% url /index.php/utilities %}">utilities</a></li>
			<li><a href="{% url /index.php/personalbank %}">finances</a></li>

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
							Site Bank: {{activeUser.balance|numberformat}}c <a href="{% url /index.php %}?dositebankupdate=1">[Update]</a><br />
							Premium: 
							{% if exists userIsPremium %}
								<span style="color:lime">Yes</span>
							{% else %}
								<span style="color:red">No</span>
							{% endif %}
							<br />
							<br />
							<a href="{% url /index.php/user %}">Account Settings</a><br />
							<a href="{% url /index.php/user/characters %}">Manage Characters</a>
						</p>
					</div>
				</li>
				{% endif %}
				<li>
					<h2><a href="{% url /index.php/maps %}">Galaxy Maps</a></h2>
					<ul>
						<li><a href="{% url /index.php/maps/colonised %}">Colonised Systems and Stations</a></li>
						<li><a href="{% url /index.php/maps/stations %}">All Stations</a></li>
						<li><a href="{% url /index.php/maps/government %}">Government Systems</a></li>
						<li><a href="{% url /index.php/maps %}">See More Options...</a></li>
					</ul>
				</li>
				<li>
					<h2><a href="{% url /index.php/utilities %}">Utilities</a></h2>
					<ul>
						<li><a href="{% url /index.php/utilities/canimakeit %}">Can I Make It?</a></li>
					</ul>
				</li>
			</ul>
		</div>
		<!-- end sidebar -->
	</div>
	<!-- end page -->

	<div id="footer">
		Request took {{controller.totalRequestTime}} seconds<br />
		{% if exists userIsAdmin %}
			<a href="{% url /index.php/admin %}">Admin Control Panel</a>
		{% endif %}
	</div>
	<!-- end footer -->
</div>
<!-- end wrap -->
</body>
</html>
