{% extends base %}

{% block pageTitle %}Stored Resources{% endblock %}

{% block content %}
<h2>{{activeUser.default_character.character_name}}'s Stored Resources</h2>

<table border="0" cellpadding="0">
	<tr>
		<th>Resource</th>
		<th>Total Quantity</th>
		<th>Locations</th>
	</tr>

	{% for resource in resources %}
		<tr {% if forloop.counter0|divisibleby:2 %}style="background-color:#333333{% endif %}"> 
			<td>{{resource.name}}</td>
			<td>{{resource.totalQuantity|numberformat}}</td>
			<td>{% for location in resource.locations %}{% if ! forloop.first %}, {% endif %}{{location}}{% endfor %}</td>
		</tr>
	{% endfor %}
</table>

{% endblock %}