{% extends base %}

{% block pageTitle %}Locality Scan Plan{% endblock %}

{% block content %}
<h2>Create an Optimal Scan Plan for a Locality</h2>
	
	<p>
		Simply fill out the form below and hit Create Route and you'll get a set of instructions to optimally scan that locality with minimal refuel stops
	</p>
	
	<p>
		<form action="" method="post">
			Locality:<br />
			<input type="text" name="q" />:<input type="text" name="s" />:<input type="text" name="r" />:<input type="text" name="l" /><br />
			<br />
			Start Location: (system you are currently in, must have a station and you should have refuelled)<br />
			<input type="text" name="start_location" /><br />
			<br />
			Fuel Capacity: (hover over the green bar on the left bottom side of your screen)<br />
			<input type="text" name="fuel_capacity" /><br />
			<br />
			Fuel Consumption per LY: (depends on your jump drive, basic jump drives have a fuel consumption of 2 per LY)<br />
			<input type="text" name="fuel_consumption_per_lightyear" value="2" /><br />
			<br />
			Display Systems You Already Have A Scan Submitted From? <input type="checkbox" name="display_systems_scanned_by_user" value="yes" /><br />
			<br />
			<input type="submit" value="Create Route" />
		</form>
	</p>
{% endblock %}