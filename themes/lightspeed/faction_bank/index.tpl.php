{% extends base %}

{% block pageTitle %}Faction Bank{% endblock %}

{% block content %}
<h2>Faction Bank</h2>

<h3>Current Balance: <span style="color:aqua">{{bankBalance|numberformat}}c</span></h3>

<div id="leftright">
	<div id="left">

		{% if exists isBankAdmin %}
		<h3>Admin Options</h3>
		<p>
			<ul>
				<li><a href="{% url /index.php/factionbank/pastetransactionlog %}">Paste Transaction Log</a></li>
			</ul>
		<p>
		{% endif %}
	</div>

	<div id="right">
		<h3>Latest Transactions</h3>

		<p>
			<table cellpadding="0" border="0">
				<tr>
					<th>Type</th>
					<th>Player</th>
					<th>Station</th>
					<th>Amount</th>
					<th>Balance</th>
				</tr>
				
				{% for transaction in latestTransactions %}
					{% if forloop.counter0|divisibleby:2 %}
						<tr style="background-color:#333333"> 
					{% else %}
						<tr>
					{% endif %}
						
						<td>{{transaction.type|capfirst}}</td>
						<td>{% if ! empty transaction.player_name %}{{transaction.player_name}}{% else %}N/A{% endif %}</td>
						<td>{% if ! empty transaction.system_id %}{{transaction.system.system_name}} {{transaction.planet_numeral}}{% else %}N/A{% endif %}</td>

						{% if transaction.direction == "out" %}
							<td><span style="color:red">-{{transaction.amount|numberformat}}c</span></td>
						{% else if transaction.direction == "in" %}
							<td><span style="color:lime">+{{transaction.amount|numberformat}}c</span></td>
						{% endif %}

						<td>{{transaction.balance|numberformat}}c</td>
					</tr>
				{% endfor %}
			</table>
			<br />
			<a href="{% url /index.php/factionbank/transactions %}">See More...</a>	
		</p>
	</div>
</div>

<h3>Charts</h3>

<img src="{% url /graphs/bankdonors.png %}" />
{% endblock %}