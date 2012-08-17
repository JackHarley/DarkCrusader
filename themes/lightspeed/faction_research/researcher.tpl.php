{% extends base %}

{% block pageTitle %}Faction Research{% endblock %}

{% block content %}
<h2>{{researcherName}}'s Researched Blueprints</h2>

<p>
	<table border="0" cellpadding="0" width="100%">
		<tr style="height:20px">
			<th>Description</th>
			<th>Date Added</th>
		</tr>

		{% for blueprint in blueprints %}
			<tr style="height:20px{% if forloop.counter0|divisibleby:2 %};background-color:#333333{% endif %}"> 
				<td>{{blueprint.description}}</td>
				<td>{{blueprint.date_added}}</td>
			</tr>
		{% endfor %}
	</table>
</p>
{% endblock %}