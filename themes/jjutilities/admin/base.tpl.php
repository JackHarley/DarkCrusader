<html>
	<head>
		{% block mobilesettings %}
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1;">
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
		{% endblock %}
		<title>{{siteName}} - {% block title %}{% endblock %}</title>
		{% block favicon %}
		<link rel="apple-touch-icon" href="{% viewurl /resources/images/favicon.ico  %}">
		{% endblock %}
		{% block css %}
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/js/fancybox/jquery.fancybox-1.3.4.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/js/uniform/css/uniform.default.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/js/tipsy/css/tipsy.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/js/tinyeditor/style.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/js/jqueryFileTree/jqueryFileTree.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/js/slidernav/slidernav.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/js/syntax_highlighter/styles/shCore.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/js/syntax_highlighter/styles/shThemeDefault.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/js/uitotop/css/ui.totop.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/css/reset.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/css/text.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/css/grid.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/css/jqueryui.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/css/main.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/css/buttons.css %}" media="screen">
		<link rel="stylesheet" type="text/css" href="{% viewurl /admin/resources/css/theme/isr.css %}" media="screen">
		{% endblock %}

		{% block js %}
		<script type="text/javascript" src="{% viewurl /admin/resources/js/jquery/jquery-1.5.1.min.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/jquery/jquery-ui.min.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/uniform/jquery.uniform.js %}" charset="utf-8"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/livevalidation/livevalidation_standalone.js %}" charset="utf-8"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/tipsy/jquery.tipsy.js %}" charset="utf-8"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/iPhone/jquery.iphoneui.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/iPhone/iPad.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/fancybox/jquery.fancybox-1.3.4.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/quicksand/jquery.quicksand.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/quicksand/custom_sorter.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/quicksand/dash_sorter.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/quicksand/jquery-css-transform.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/quicksand/jquery-animate-css-rotate-scale.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/tinyeditor/tinyeditor.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/jqueryFileTree/jqueryFileTree.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/DataTables/jquery.dataTables.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/slidernav/slidernav.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/isr/isr_ui.js %}"></script>
		<script type="text/javascript" src="{% viewurl /admin/resources/js/jscolor/jscolor.js %}"></script>
		{% endblock %}
	</head>
	<body>
		<div id="wrapper">


			<div id="sidebar">
				<a href="index.html" class="logo"><span>Project Nebula</span></a>
				<div class="user_box round_all clearfix">
					<img src="{% viewurl /admin/resources/images/avatar.jpg %}" width="55" alt="Profile Pic" />

					<h2>{{activeUser.group.description}}</h2>

					<h3><a class="text_shadow" href="{% url /index.php/admin/user/user/lookup %}?id={{activeUser.id}}">{{activeUser.username}}</a></h3>

					<ul>
						<li><a href="{% url /index.php/user/logout %}">logout</a></li>
					</ul>
				</div><!-- #user_box -->

				<ul id="accordion">

					<li><a href="{% url /index.php/admin %}"><img src="{% viewurl /admin/resources/images/icons/small/grey/Home.png %}"/>Home</a></li>

					<li><a href="#" class="top_level"><img src="{% viewurl /admin/resources/images/icons/small/grey/Users.png %}"/>Members</a>
						<ul class="drawer">
							<li><a href="{% url /index.php/admin/user/user/list %}">Users</a></li>
							<li><a href="{% url /index.php/admin/user/group/list %}">User Groups</a></li>
						</ul>
					</li>

					<li><a href="#" class="top_level"><img src="{% viewurl /admin/resources/images/icons/small/grey/Robot.png %}"/>Maintenance</a>
						<ul class="drawer">
							<li><a href="{% url /index.php/admin/maintenance/cache %}">Cache</a></li>
						</ul>
					</li>

					<li><a href="#" class="top_level"><img src="{% viewurl /admin/resources/images/icons/small/grey/Cog%202.png %}"/>Settings</a>
						<ul class="drawer">
							<li><a href="{% url /index.php/admin/settings/account %}">General</a></li>
						</ul>
					</li>
				</ul>
				<ul id="side_links" class="text_shadow" >
					<li><a href="{% url /index.php/ %}">Go to Main Site</a></li>
				</ul>
			</div><!-- #sidebar -->

		<div id="main_container" class="main_container container_16 clearfix">

			{% block navbar %}{% endblock %}
			{% block body %}{% endblock %}

		</div>
	</body>
</html>