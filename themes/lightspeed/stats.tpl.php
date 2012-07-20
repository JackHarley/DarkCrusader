{% extends base %}

{% block pageTitle %}Stats{% endblock %}

{% block content %}
<h2>Stats & Intelligence</h2>

<p>
	This section gives you statistics and information on players, systems, factions and localities.
	Faction members can also see intelligence on players and systems.
</p>

{% if exists canAccessPlayerStatistics %}
<h3>Players</h3>
<p>
	Players lets you see some basic information about different players, the players section relies entirely on input from our members so may not be 100% up to date.<br />
	SWAT/FIRE members can also see intelligence on players including any high value targets we know they own and information on their colonies and ship builds that have been gleaned from chat by our attentive intelligence analysts.<br />
	We have {{numberOfPlayersOnFile}} players on file.<br />
	<form action="{% url /index.php/players/player %}" method="get">
		Lookup Player: <input type="text" name="name" /> <input type="submit" value="Lookup Player" />
	</form>
</p>
{% endif %}

{% if exists canAccessSystemStatistics %}
<h3>Systems</h3>
<p>
	Systems lets you see the locality a system is in, the nearest station system and information on historical ownership of the system. You can also click on a link to see the system on our galaxy map.<br />
	SWAT/FIRE members can also see intelligence on systems such as what colonies we know exist there and if there are any high value targets there.<br />
	<a href="{% url /index.php/systems/stats %}">Click here to see graphs and extended system statistics</a><br />
	<form action="{% url /index.php/systems/system %}" method="get">
		Lookup System: <input type="text" name="name" /> <input type="submit" value="Lookup System" />
	</form>
</p>
{% endif %}

{% if exists canAccessFactionStatistics %}
<h3>Factions</h3>
<p>
	Factions allows you to see the number of stations and systems a faction controls, and a graph of that information over time. You can also see the members of the faction which have been enetred into our DB.<br />
	There are {{numberOfFactionsWeKnowOf}} factions operating that we know about (a faction must control at least 1 system to be listed here)<br />
	<form action="{% url /index.php/factions/faction %}" method="get">
		Lookup Faction: <select name="name">{% for faction in factions %}<option>{{faction}}</option>{% endfor %}</select> <input type="submit" value="Lookup Faction" />
	</form>
</p>
{% endif %}

{% if exists canAccessLocalityStatistics %}
<h3>Localities</h3>
<p>
	Localities allows you to see a list of systems in each locality and a few other pieces of info about the locality.<br />
	SWAT/FIRE members can also see which systems have been scanned into the DB by other people or them and see progress towards the scanning of the entire locality.<br />
	<form action="{% url /index.php/localities/locality %}" method="get">
		Lookup Locality: 
		<select name="q">
			<option>1</option>
			<option>2</option>
			<option>3</option>
			<option>4</option>
		</select>:<select name="s">
			<option>1</option>
			<option>2</option>
			<option>3</option>
			<option>4</option>
		</select>:<select name="r">
			<option>1</option>
			<option>2</option>
			<option>3</option>
			<option>4</option>
		</select>:<select name="l">
			<option>1</option>
			<option>2</option>
			<option>3</option>
			<option>4</option>
		</select>
		<input type="submit" value="Lookup Loclity" />
	</form>
</p>
{% endif %}

{% endblock %}