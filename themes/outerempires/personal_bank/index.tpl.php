{% extends base %}

{% block pageTitle %}Personal Bank{% endblock %}

{% block content %}
<h2>Personal Bank</h2>

<div id="leftright">
	<div id="left">
		<h3>Current Balance: <span style="color:aqua">{{bankBalance|numberformat}}c</span></h3>

		<h3>Stats</h3>

		<p>
			<ul>
				<li>Your Richest Moment was on {{richestMoment.date|date:"d/m/Y"}} when you had {{richestMoment.balance|numberformat}}c</li>
			</ul>
		<p>

		<h3>Options</h3>
		<p>
			<ul>
				<li><a href="{% url /index.php/personalbank/pastetransactionlog %}">Paste Transaction Log</a></li>
			</ul>
		<p>
	</div>

	<div id="right">
		<h3>Latest Transactions</h3>

		<p>
			<table cellpadding="0" border="0">
				<tr>
					<th>Type</th>
					<th>Amount</th>
					<th>Balance</th>
				</tr>
				
				{% for transaction in latestTransactions %}
					{% if forloop.counter0|divisibleby:2 %}
						<tr style="background-color:#333333"> 
					{% else %}
						<tr>
					{% endif %}
						
						<td>{{transaction.type}}</td>
						
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
			<a href="{% url /index.php/personalbank/transactions %}">See More...</a>
		</p>
	</div>
</div>

<h3>Charts</h3>

<img src="{% url /graphs %}/{{incomeGraph}}" />

<br />
<br />
<br />

<img src="{% url /graphs %}/{{expenditureGraph}}" />


{% endblock %}