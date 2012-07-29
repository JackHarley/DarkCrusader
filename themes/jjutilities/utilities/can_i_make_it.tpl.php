{% extends base %}

{% block pageTitle %}Can I Make It?{% endblock %}

{% block content %}

<h2>Can I Make It?</h2>

<p>
	This page can tell you whether you can make it to a system and then back to a station with your current fuel.<br />
	Cut out those annoying moments counting your fuel!
</p>

<p>
	<form method="post" action="">
		Current System:<br />
		<input type="text" name="current_system" /><br />
		<br />
		Destination System (system you want to go to):<br />
		<input type="text" name="destination_system" /><br />
		<br />
		Current Fuel (just read it from your fuel bar):<br />
		<input type="text" name="fuel" /><br />
		<br />
		Fuel Consumption per Lightyear (you can find this in your jump drive stats, basic jump drives consumpe 2 fuel/LY):<br />
		<input type="text" name="fuel_consumption_per_ly" value="2" /><br />
		<br />
		<input type="submit" name="submit" value="Can I Make It?" />
	</form>
</p>

{% endblock %}