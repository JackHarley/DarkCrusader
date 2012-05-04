{% extends admin/base %}

{% block content %}
<h2>Manage KoS List</h2>
	
<p>
	<a href="{% url /index.php/admin/kos/add %}">Add Entry</a>
</p>
	
<table cellpadding="2" border="0">
	<tr>
		<th>Player Name</th>
		<th>Reason for Condemnation</th>
	</tr>
	
	{% for KoSEntry in KoSList %}
		<tr>
			<td>{{KoSEntry.player_name}}</td>
			<td>{{KoSEntry.reason}}</td>
			<td><a href="{% url /index.php/admin/kos/del %}?id={{KoSEntry.id}}">Delete</a></td>
		</tr>
	{% endfor %}
</table>
{% endblock %}