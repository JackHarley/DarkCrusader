{% extends base %}

{% block pageTitle %}Skills{% endblock %}

{% block content %}
<h2>Browse Skills</h2>

<p>
	<form method="get" action="">
		Category: 
		<select name="category_id">
			<option value="0">Any</option>
			{% for category in skillCategories %}
				<option {% if exists category_id && category_id == category.id %}selected{% endif %} value="{{category.id}}">{{category.name}}</option>
			{% endfor %}
		</select> 
		<input type="submit" value="Search" />
	</form>
</p>

<p>
	All the available skills in the OE system are shown below. Click on a skill to see more information on that skill and the prerequistes
</p>

<p>
	<table border="0" cellpadding="0" width="100%">
		<tr style="height:20px">
			<th>Category</th>
			<th>Name</th>
			<th>Description</th>
		</tr>

		{% for skill in allSkills %}
			<tr style="{% if forloop.counter0|divisibleby:2 %}background-color:#333333{% endif %}"> 
				<td>{{skill.category.name}}</td>
				<td><a href="{% url /index.php/skills/skill %}?id={{skill.id}}">{{skill.name}}</a></td>
				<td>{{skill.description}}</td>
			</tr>
		{% endfor %}
	</table>
</p>
{% endblock %}