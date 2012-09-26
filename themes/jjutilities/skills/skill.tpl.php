{% extends base %}

{% block pageTitle %}Skills{% endblock %}

{% block content %}
<h2>{{skill.name}}</h2>

<h3>Skill Details</h3>
<p>
	<ul>
		<li>Category: {{skill.category.name}}</li>
		<li>Name: {{skill.name}}</li>
		<li>Description: {{skill.description}}</li>
	</ul>
</p>

<h3>Prerequistes</h3>

{% if empty skill.prerequistes %}
	<p>None :)</p>
{% else %}
	<p>
		<table cellpadding="0" border="0">
			{% for prerequiste in skill.prerequistes %}
				<tr>
					<td><a href="{% url /index.php/skills/skill %}?id={{prerequiste.prerequiste_skill.id}}">{{prerequiste.prerequiste_skill.name}}</a> {{prerequiste.prerequiste_skill_level}}</td>
				</tr>

				{% for prerequisteL2 in prerequiste.prerequiste_skill.prerequistes %}
					<tr>
						<td></td><td><a href="{% url /index.php/skills/skill %}?id={{prerequisteL2.prerequiste_skill.id}}">{{prerequisteL2.prerequiste_skill.name}}</a> {{prerequisteL2.prerequiste_skill_level}}</td>
					</tr>

					{% for prerequisteL3 in prerequisteL2.prerequiste_skill.prerequistes %}
						<tr>
							<td></td><td></td><td><a href="{% url /index.php/skills/skill %}?id={{prerequisteL3.prerequiste_skill.id}}">{{prerequisteL3.prerequiste_skill.name}}</a> {{prerequisteL3.prerequiste_skill_level}}</td>
						</tr>

						{% for prerequisteL4 in prerequisteL3.prerequiste_skill.prerequistes %}
							<tr>
								<td></td><td></td><td></td><td><a href="{% url /index.php/skills/skill %}?id={{prerequisteL4.prerequiste_skill.id}}">{{prerequisteL4.prerequiste_skill.name}}</a> {{prerequisteL4.prerequiste_skill_level}}</td>
							</tr>

							{% for prerequisteL5 in prerequisteL4.prerequiste_skill.prerequistes %}
								<tr>
									<td></td><td></td><td></td><td></td><td><a href="{% url /index.php/skills/skill %}?id={{prerequisteL5.prerequiste_skill.id}}">{{prerequisteL5.prerequiste_skill.name}}</a> {{prerequisteL5.prerequiste_skill_level}}</td>
								</tr>

								{% for prerequisteL6 in prerequisteL5.prerequiste_skill.prerequistes %}
									<tr>
										<td></td><td></td><td></td><td></td><td></td><td><a href="{% url /index.php/skills/skill %}?id={{prerequisteL6.prerequiste_skill.id}}">{{prerequisteL6.prerequiste_skill.name}}</a> {{prerequisteL6.prerequiste_skill_level}}</td>
									</tr>

								{% endfor %}
							{% endfor %}
						{% endfor %}
					{% endfor %}
				{% endfor %}
			{% endfor %}
		</table>
	</p>
{% endif %}

<h3>Gives Access To</h3>

{% if empty skill.unlocks %}
	<p>Nothing :(</p>
{% else %}
	<p>
		<ul>
			{% for unlock in skill.unlocks %}
				<li><a href="{% url /index.php/skills/skill %}?id={{unlock.skill.id}}">{{unlock.skill.name}}</a> at {{skill.name}} {{unlock.prerequiste_skill_level}}</li>
			{% endfor %}
		</ul>
	</p>
{% endif %}

{% endblock %}