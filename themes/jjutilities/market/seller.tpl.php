{% extends base %}

{% block pageTitle %}Seller Overview{% endblock %}

{% block content %}
<h2>Seller Overview - All Time</h2>

<p>
	Time Period: 
	<select name="period">
		<option>All Time</option>
	</select>
	<input type="submit" name="submit" value="Go" />
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