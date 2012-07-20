{% extends base %}

{% block pageTitle %}Players -> {{player.player_name}}{% endblock %}

{% block content %}

<h2>{{player.player_name}}{% if exists canEditPlayers %} - <a href="{% url /index.php/players/edit %}?name={{player.player_name}}">Edit</a>{% endif %}</h2>

<p>
	{% if ! empty player.rank %}<b>Last Known Rank:</b> {{player.rank}}<br />{% endif %}
	{% if ! empty player.faction %}<b>Current Faction:</b> {{player.faction}}{% endif %}
</p>

<h3>SWAT/FIRE Military Status: <span style="color: {{player.official_status.hex_colour}}">{{player.official_status.name}}</span></h3>
{% endblock %}