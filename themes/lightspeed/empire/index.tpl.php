{% extends base %}

{% block pageTitle %}Empire{% endblock %}

{% block content %}
<h2>Empire Management</h2>

{% if exists userIsPremium %}
	<h3>Finances</h3>
	<ul>
		<li>Worker Costs in Past Week: {{workerCostsLastWeek|numberformat}}c</li>
		<li>Market Sales in Past Week: {{marketSalesLastWeek|numberformat}}c</li>
		{% if profitOrLossLastWeek > 0 %}
			<li>Profit in Past Week: <span style="color:lime">{{profitOrLossLastWeek|numberformat}}c</span></li>
		{% else %}
			<li>Loss in Past Week: <span style="color:red">{{profitOrLossLastWeek|numberformat}}c</span></li>
		{% endif %}
	</ul>
{% endif %}

<p>
	{% if exists userIsPremium %}
		<a href="{% url /index.php/empire/seller %}">See an overview of your market sales</a><br />
	{% endif %}
	<a href="{% url /index.php/empire/resources %}">See an overview of the resources you have stored around the galaxy</a><br />
	<a href="{% url /index.php/empire/colonies %}">See an overview of your colonies</a>
</p>

{% endblock %}