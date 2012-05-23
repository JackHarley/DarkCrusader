{% extends base %}

{% block pageTitle %}Personal Bank{% endblock %}

{% block content %}
<h2>Welcome to your Personal Bank!</h2>

<p>
	Personal bank allows you to track your finances and see where you're spending and earning your credits.<br />
	It generates pretty graphs, and in future will let you see your profitability in things like colonies, by tracking sale of goods you choose, and costs of workers.<br />
	There's also loads of other features in the work which you'll see appear soon!
</p>

<p>
	To get started with Personal Bank, follow the instructions below:

	<ul>
		<li>Log into Outer Empires and open your Transaction Log</li>
		<li>Change the Time Frame dropdown to 1 Month</li>
		<li>Click the Display Data button</li>
		<li>Wait a minute or two, the month long transaction log will take a while to load</li>
		<li>Highlight all of the transactions in the list (not the field headings!) and copy it with Ctrl+V/CMD+V/Right Click and hoose Copy</li>
		<li><a href="{% url /index.php/personalbank/pastetransactionlog %}">Click here</a> and paste the log, then choose Submit and wait while we process your log</li>
		<li>You're done! A screen will load with a summary of your finances, make sure to frequently past the last week or so's transactions to keep it up to date, the system will automatically detect transactions its already added, so don't worry about where you copy from and to in your log</li>
	</ul>
</p>
{% endblock %}