{% extends base %}

{% block pageTitle %}Players -> {{player.player_name}}{% endblock %}

{% block content %}

<h2>{{player.player_name}}{% if exists canEditPlayers %} - <a href="{% url /index.php/players/edit %}?name={{player.player_name}}">Edit</a>{% endif %}</h2>

<p>
	{% if ! empty player.rank %}Last Known Rank: {{player.rank}}<br />{% endif %}
	{% if ! empty player.faction %}Current Faction: {{player.faction}}{% endif %}
</p>

<h3>SWAT/FIRE Military Status: {{player.official_status}}</h3>
{% endblock %}