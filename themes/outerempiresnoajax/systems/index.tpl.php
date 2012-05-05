{% extends base %}

{% block pageTitle %}System Stats{% endblock %}

{% block content %}

<h2>Get System Stats</h2>
	
<p>
	Simply enter the system name to get stats for
</p>

<form action="" method="get">
	<p>
		System Name:<br />
		<input type="text" name="name" /><br />
		
		<input type="submit" value="Get Stats" />
	</p>
</form>

{% endblock %}