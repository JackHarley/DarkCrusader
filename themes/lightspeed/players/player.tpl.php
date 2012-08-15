{% extends base %}

{% block pageTitle %}Players -> {{player.player_name}}{% endblock %}

{% block content %}

<h2>{{player.player_name}}{% if exists canEditPlayers %} - <a href="{% url /index.php/players/edit %}?name={{player.player_name}}">Edit</a>{% endif %}</h2>

<p>
	{% if ! empty player.rank %}<b>Last Known Rank:</b> {{player.rank}}<br />{% endif %}
	{% if ! empty player.faction %}<b>Current Faction:</b> {{player.faction}}{% endif %}
</p>

<h3>SWAT/FIRE Military Status: <span style="color: {{player.official_status.hex_colour}}">{{player.official_status.name}}</span></h3>

{% if ! empty comments %}
<h2>Intelligence</h2>

<h3>Comments</h3>

<ul>
{% for comment in comments %}
	<li>
		<b>CLASSIFIED LEVEL {{comment.classification_level}} - {{comment.submitter.username}} - {{comment.date_added}}</b><br />
		{{comment.comment|linebreaks}}
	</li>
{% endfor %}
</ul>
{% endif %}

{% if exists activeUser %}
{% if activeUser.clearance_level > 0 %}
<h3>Add a Comment</h3>
<p>
	<form method="post" action="">
		Classification Level 
		<select name="classification">
			{% if activeUser.clearance_level >= 1 %}<option value="1">CLASSIFIED - All SWAT/FIRE Members</option>{% endif %}
			{% if activeUser.clearance_level >= 2 %}<option value="2">SECRET - Special Activities Division Only</option>{% endif %}
			{% if activeUser.clearance_level >= 3 %}<option value="3">SECRET ULTRA - Leaders Only</option>{% endif %}
		</select><br />
		<br />
		<textarea name="comment" rows="8" cols="80"></textarea><br />
		<br />
		<input type="submit" name="submit" value="Add" />
	</form>
</p>
{% endif %}
{% endif %}

{% endblock %}