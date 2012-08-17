{% extends base %}

{% block pageTitle %}Faction Research{% endblock %}

{% block content %}
<h2>Faction Research Centre</h2>

<p>
	Every 15 minutes, all faction members' stored items lists are scraped via their API keys and the blueprints found are added to this database. Therefore it is important that all members make sure they have their API keys inputted into the site. You can add yours in the Manage Characters area, there is a link in the sidebar.<br />
	If your research has just dropped and you want to immediately start another round without waiting for the 15 minute scrape, simply load the Empire area and the blueprint will be scraped immediately.<br />
	Make sure your research colonies have Communications Arrays so that the site can see the blueprints in them.
</p>

<h3>Latest Member Blueprints</h3>
<p>
	<table border="0" cellpadding="0" width="100%">
		<tr style="height:20px">
			<th>Researcher</th>
			<th>MK</th>
			<th>Description</th>
		</tr>

		{% for blueprint in latestBlueprints %}
			<tr style="height:20px{% if forloop.counter0|divisibleby:2 %};background-color:#333333{% endif %}"> 
				<td><a href="{% url /index.php/factionresearch/researcher %}?name={{blueprint.researcher_player_name}}">{{blueprint.researcher_player_name}}</a></td>
				<td>MK{{blueprint.research_mark}}</td>
				<td>{{blueprint.description}}</td>
			</tr>
		{% endfor %}
	</table>
</p>

<h3>Browse Research by Researcher</h3>
<p>
	<ul>
		{% for researcher in researchers %}
			<li><a href="{% url /index.php/factionresearch/researcher %}?name={{researcher}}">{{researcher}}</a></li>
		{% endfor %}
	</ul>
</p>
{% endblock %}