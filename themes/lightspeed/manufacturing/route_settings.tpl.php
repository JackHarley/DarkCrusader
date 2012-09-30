{% extends base %}

{% block pageTitle %}Manufacturing{% endblock %}

{% block content %}
<h2>Manufacturing Route Planner</h2>

<p>
	Please tick all the colonies you are willing to collect resources from, it is advised that you do not waste time picking up small quantities
</p>

<p>
	<ul>
		{% for resourceOccurence in resourceOccurences %}
			<li>x{{resourceOccurence.quantity}} {{resourceOccurence.description}} - {{resourceOccurence.colony.name}} @ {{resourceOccurence.colony.location_string|unescape}}</li>
		{% endfor %}
	</ul>
</p>
{% endblock %}