{% extends base %}

{% block pageTitle %}Seller Overview{% endblock %}

{% block content %}
<h2>Seller Overview - All Time</h2>

<p>
	<form method="get" action="">
		Time Period:
		<select name="period">
			<option value="forever" {% if period == "forever" %}selected{% endif %}>All Time</option>
			<option value="last30days" {% if period == "last30days" %}selected{% endif %}>Last 30 Days</option>
			<option value="last7days" {% if period == "last7days" %}selected{% endif %}>Last 7 Days</option>
			<option value="last24hours" {% if period == "last24hours" %}selected{% endif %}>Last 24 Hours</option>
		</select>
		<input type="submit" value="Go" />
	</form>
</p>

<h3>Your Top 10 Customers Ordered By Total Credit Sales</h3>
<table border="0" cellpadding="0">
	<tr>
		<th>Customer</th>
		<th>Number of Transactions Conducted</th>
		<th>Total Sales</th>
	</tr>

	{% for customer in topCustomers %}
		<tr {% if forloop.counter0|divisibleby:2 %}style="background-color:#333333{% endif %}"> 
			<td>{{customer.name}}</td>
			<td>{{customer.numberOfIndividualSales|numberformat}}</td>
			<td>{{customer.totalSales|numberformat}}c</td>
		</tr>
	{% endfor %}
</table>
{% endblock %}