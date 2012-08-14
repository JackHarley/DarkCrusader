{% extends base %}

{% block pageTitle %}{{colony.name}}{% endblock %}

{% block content %}
<h2>{{colony.name}} - {{colony.location_string|unescape}}</h2>
<h3>Primary Activity: {{colony.formatted_primary_activity}}</h3>

<div id="left">
	<h3>Workers</h3>

	<ul>
		<li>Population: {{colony.population}}</li>
		<li>Worker Costs per 25 Hours: {{colony.worker_costs_per_25_hours|numberformat}}c</li>
	</ul>

	<h3>Info</h3>

	<ul>
		<li>Size: {{colony.size}}</li>
		<li>Max Size: {{colony.max_size}}</li>
		<li>Displayed Size: {{colony.displayed_size}}</li>
		<li>Power: {{colony.power}}</li>
		<li>Free Power: {{colony.free_power}}</li>
		<li>Hangar Storage Capacity: {{colony.storage_capacity}}</li>
	</ul>
</div>

<div id="right">
	<h3>Hangar</h3>

	<ul>
		{% for item in colony.stored_items %}
			<li>x{{item.quantity}} {{item.description}}</li>
		{% endfor %}
	</ul>
</div>
{% endblock %}