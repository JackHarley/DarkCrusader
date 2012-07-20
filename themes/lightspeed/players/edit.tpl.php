{% extends base %}

{% block pageTitle %}Edit Player -> {{player.player_name}}{% endblock %}

{% block content %}
<h2>Edit Player - {{player.player_name}}</h2>

<form method="post" action="">
	Rank: 
	<select name="rank">
		<option {% if empty player.rank %}selected{% endif %}>Unknown</option>
		{% for rank in ranks %}
			<option {% if ! empty player.rank %}{% if player.rank == rank %}selected{% endif %}{% endif %}>{{rank}}</option>
		{% endfor %}
	</select><br />
	<br />

	Faction:
	<select name="faction">
		<option {% if empty player.faction %}selected{% endif %}>Unknown</option>
		<option {% if ! empty player.faction %}{% if player.faction == "None" %}selected{% endif %}{% endif %}>None</option>
		{% for faction in factions %}
			<option {% if ! empty player.faction %}{% if player.faction == faction %}selected{% endif %}{% endif %}>{{faction}}</option>
		{% endfor %}
	</select><br />
	<br />

	{% if exists canEditOfficialMilitaryStatuses %}
		Official SWAT/FIRE Military Status:
		<select name="official_status">
			{% for militaryStatus in statuses %}
				<option {% if ! empty player.official_status %}
							{% if player.official_status == militaryStatus %}
								selected
							{% endif %}
						{% else %}
							{% if militaryStatus == "Neutral" %}
								selected
							{% endif %}
						{% endif %}>{{militaryStatus}}</option>
			{% endfor %}
		</select><br />
		<br />
	{% endif %}

	<input type="submit" name="submit" value="Update Player" />
</form>

{% endblock %}