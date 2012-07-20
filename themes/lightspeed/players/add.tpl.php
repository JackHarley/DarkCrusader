{% extends base %}

{% block pageTitle %}Players -> Add{% endblock %}

{% block content %}

<h2>Add Player</h2>

<p>
	This will allow you to add a player to the database, you can then look them up and add information on them.<br />
</p>

<h3>Please make sure you spell the players name correctly and that you use correct capitalisation! Incorrect spelling and capitalisation will result in the removal of your ability to add players!</h3>

<form method="post" action="">
	Player Name:<br />
	<input type="text" name="name" {% if exists playerName %}value="{{playerName}}"{% endif %} /><br />
	<br />
	<input type="submit" name="submit" value="Add Player" />
</form>

{% endblock %}