{% extends base %}

{% block pageTitle %}Personal Bank{% endblock %}

{% block content %}
<h2>Latest 300 Transactions</h2>

<table cellpadding="0" border="0">
	<tr>
		<th>Date</th>
		<th>Type</th>
		<th>Description</th>
		<th>Amount</th>
		<th>Balance</th>
	</tr>
	
	{% for transaction in transactions %}
		{% if forloop.counter0|divisibleby:2 %}
			<tr style="background-color:#333333"> 
		{% else %}
			<tr>
		{% endif %}
			<td width="100px">{{transaction.date|date:"d/m h:i"}}</td>
			<td>{{transaction.type}}</td>
			<td>{{transaction.description}}</td>
			
			{% if transaction.direction == "out" %}
				<td><span style="color:red">-{{transaction.amount|numberformat}}c</span></td>
			{% else if transaction.direction == "in" %}
				<td><span style="color:lime">+{{transaction.amount|numberformat}}c</span></td>
			{% endif %}

			<td>{{transaction.balance|numberformat}}c</td>
		</tr>
	{% endfor %}
</table>
{% endblock %}