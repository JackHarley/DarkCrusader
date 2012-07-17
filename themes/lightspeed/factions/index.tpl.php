{% extends base %}

{% block pageTitle %}Faction Stats{% endblock %}

{% block content %}

<h2>Get Faction Stats</h2>
	
<p>
	Simply choose the faction get stats for
</p>

<form action="" method="get">
	<p>
		Faction Name:<br />
		<select name="name">
			{% for faction in factions %}
				<option>{{faction}}</option>
			{% endfor %}
		</select><br />
		
		<input type="submit" value="Get Stats" />
	</p>
</form>

{% endblock %}