{% extends base %}

{% block pageTitle %}Manufacturing{% endblock %}

{% block content %}
<h2>Manufacturing Route Planner</h2>

<h3><i>{{blueprintDescription}}</i></h3>

<form method="post" action="">
	<input type="hidden" name="blueprint" value="{{blueprintDescription}}" />
	
	<h3 style="margin-bottom:0">Please tick all the colonies you are willing to collect resources from, it is advised that you do not waste time picking up small quantities</h3><br />
	<p>
		{% for resourceOccurence in resourceOccurences %}
			<input {% if resourceOccurence.quantity > 500 %}checked{% endif %} type="checkbox" name="{{resourceOccurence.colony.name}}" value="yes" /> x{{resourceOccurence.quantity}} {{resourceOccurence.description}} - {{resourceOccurence.colony.name}} @ {{resourceOccurence.colony.location_string|unescape}}<br />
		{% endfor %}
	</p>

	
	<h3 style="margin-bottom:0">Please choose the manufacturing colony you wish to use, if your manuafacturing colony does not show up, ensure you have classified it as a Manufacturing Colony in the colony manager</h3><br />
	<p>
		<select name="manufacturing_colony_name">
			{% for colony in manufacturingColonies %}
				<option value="{{colony.name}}">{{colony.name}} - {{colony.location_string|unescape}}</option>
			{% endfor %}
		</select>
	</p>

	<h3 style="margin-bottom:0">Please fill in your ship information</h3><br />
	<p>
		Cargo Capacity (ensure you empty your ship before starting the route!):<br /> 
		<input type="text" name="ship_storage_capacity" /><br />
		<br />
		Fuel Capacity:<br /> 
		<input type="text" name="fuel" /><br />
		<br />
		Jump Drive Fuel Per Lightyear (this is 2 unless you have a researched jump drive):<br />
		<input type="text" name="fuel_per_lightyear" value="2" />
	</p>

	<h3 style="margin-bottom:0">Please choose your starting location (must be a station)</h3><br />
	<p>
		System Name To Start Route At:<br />
		<input type="text" name="start_system" />
	</p>

	<p>
		<input type="submit" name="submit2" value="Create Route!" />
	</p>
</form>

{% endblock %}