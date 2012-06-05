{% extends base %}

{% block pageTitle %}Faction Stats{% endblock %}

{% block content %}

<h2>Get Faction Stats</h2>
	
<p>
	Simply enter the faction name to get stats for
</p>

<form action="" method="get">
	<p>
		Faction Name:<br />
		<input type="text" name="name" /><br />
		
		<input type="submit" value="Get Stats" />
	</p>
</form>

{% endblock %}