{% extends base %}

{% block pageTitle %}Colonies{% endblock %}

{% block content %}
<h2>{{activeUser.default_character.character_name}}'s Colonies</h2>

{% if ! empty miningColonies %}
<h3>Mining Colonies</h3>

<table border="0" cellpadding="0" width="100%">
	<tr style="height:20px">
		<th>Name</th>
		<th>Location</th>
		<th>Population</th>
		<th>Worker Costs*</th>
		<th>Resources</th>
		<th>Free Space**</th>
	</tr>

	{% for colony in miningColonies %}
		<tr style="height:20px{% if forloop.counter0|divisibleby:2 %};background-color:#333333{% endif %}"> 
			<td><a href="{% url /index.php/empire/colonies/colony %}?id={{colony.id}}">{{colony.name}}</a></td>
			<td>{{colony.location_string|unescape}}</td>
			<td>{{colony.population|numberformat}}</td>
			<td>{{colony.worker_costs_per_25_hours|numberformat}}c</td>
			<td>{% for resource in colony.resources %}{% if ! forloop.first %}, {% endif %}x{{resource.quantity}} {{resource.description}}{% endfor %}</td>
			<td><span {% if colony.free_capacity == 0 %}style="color:red"{% endif %}>{{colony.free_capacity}}</span></td>
		</tr>
	{% endfor %}
</table>

<br />
<br />
{% endif %}

{% if ! empty processingColonies %}
<h3>Processing Colonies</h3>

<table border="0" cellpadding="0" width="100%">
	<tr style="height:20px">
		<th>Colony Name</th>
		<th>Location</th>
		<th>Population</th>
		<th>Worker Costs*</th>
		<th>Resources</th>
	</tr>

	{% for colony in processingColonies %}
		<tr style="height:20px{% if forloop.counter0|divisibleby:2 %};background-color:#333333{% endif %}"> 
			<td><a href="{% url /index.php/empire/colonies/colony %}?id={{colony.id}}">{{colony.name}}</a></td>
			<td>{{colony.location_string|unescape}}</td>
			<td>{{colony.population|numberformat}}</td>
			<td>{{colony.worker_costs_per_25_hours|numberformat}}c</td>
			<td>{% for resource in colony.resources %}{% if ! forloop.first %}, {% endif %}x{{resource.quantity}} {{resource.description}}{% endfor %}</td>
		</tr>
	{% endfor %}
</table>

<br />
<br />
{% endif %}

{% if ! empty refiningColonies %}
<h3>Refining Colonies</h3>

<table border="0" cellpadding="0" width="100%">
	<tr style="height:20px">
		<th>Colony Name</th>
		<th>Location</th>
		<th>Population</th>
		<th>Worker Costs*</th>
		<th>Resources</th>
	</tr>

	{% for colony in refiningColonies %}
		<tr style="height:20px{% if forloop.counter0|divisibleby:2 %};background-color:#333333{% endif %}"> 
			<td><a href="{% url /index.php/empire/colonies/colony %}?id={{colony.id}}">{{colony.name}}</a></td>
			<td>{{colony.location_string|unescape}}</td>
			<td>{{colony.population|numberformat}}</td>
			<td>{{colony.worker_costs_per_25_hours|numberformat}}c</td>
			<td>{% for resource in colony.resources %}{% if ! forloop.first %}, {% endif %}x{{resource.quantity}} {{resource.description}}{% endfor %}</td>
		</tr>
	{% endfor %}
</table>

<br />
<br />
{% endif %}

{% if ! empty researchColonies %}
<h3>Research Colonies</h3>

<table border="0" cellpadding="0" width="100%">
	<tr style="height:20px">
		<th>Colony Name</th>
		<th>Location</th>
		<th>Population</th>
		<th>Worker Costs*</th>
	</tr>

	{% for colony in researchColonies %}
		<tr style="height:20px{% if forloop.counter0|divisibleby:2 %};background-color:#333333{% endif %}"> 
			<td><a href="{% url /index.php/empire/colonies/colony %}?id={{colony.id}}">{{colony.name}}</a></td>
			<td>{{colony.location_string|unescape}}</td>
			<td>{{colony.population|numberformat}}</td>
			<td>{{colony.worker_costs_per_25_hours|numberformat}}c</td>
		</tr>
	{% endfor %}
</table>

<br />
<br />
{% endif %}

{% if ! empty manufacturingColonies %}
<h3>Manufacturing Colonies</h3>

<table border="0" cellpadding="0" width="100%">
	<tr style="height:20px">
		<th>Colony Name</th>
		<th>Location</th>
		<th>Population</th>
		<th>Worker Costs*</th>
		<th>Status</th>
	</tr>

	{% for colony in manufacturingColonies %}
		<tr style="height:20px{% if forloop.counter0|divisibleby:2 %};background-color:#333333{% endif %}">
			<td>{{colony.name}}</td>
			<td>{{colony.location_string|unescape}}</td>
			<td>{{colony.population|numberformat}}</td>
			<td>{{colony.worker_costs_per_25_hours|numberformat}}c</td>
			<td>
				{% if colony.status == "active" %}<span style="color:lime">Manufacturing...</span>{% endif %}
				{% if colony.status == "idle" %}<span style="color:yellow">Idle</span>{% endif %}
			</td>
		</tr>
	{% endfor %}
</table>

<br />
<br />
{% endif %}

{% if ! empty unclassifiedColonies %}
<h3>Unclassified Colonies</h3>

<table border="0" cellpadding="0" width="100%">
	<tr style="height:20px">
		<th>Colony Name</th>
		<th>Location</th>
		<th>Population</th>
		<th>Worker Costs*</th>
		<th>Classify</th>
	</tr>

	{% for colony in unclassifiedColonies %}
		<tr style="height:20px{% if forloop.counter0|divisibleby:2 %};background-color:#333333{% endif %}"> 
			<td>{{colony.name}}</td>
			<td>{{colony.location_string|unescape}}</td>
			<td>{{colony.population|numberformat}}</td>
			<td>{{colony.worker_costs_per_25_hours|numberformat}}c</td>
			<td>
				<form method="post" action="{% url /index.php/empire/colonies/classify %}">
					<select name="primary_activity"><option value="mining">Mining</option><option value="processing">Processing</option><option value="refining">Refining</option><option value="manufacturing">Manufacturing</option><option value="research">Research</option></select>
					<input type="hidden" name="id" value="{{colony.id}}" />
					<input type="submit" name="submit" value="Classify" />
				</form>
			</td>
		</tr>
	{% endfor %}
</table>

<br />
<br />
{% endif %}

<i>* Worker costs are per 25 hours calculated at the normal rate of 6c per worker</i><br />
<i>** Free Space means how much free space you have in your hangar for resources, food, water, scans, etc.</i>

{% endblock %}