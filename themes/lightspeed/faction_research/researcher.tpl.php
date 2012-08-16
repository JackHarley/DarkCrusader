{% extends base %}

{% block pageTitle %}Faction Research{% endblock %}

{% block content %}
<h2>{{researcherName}}'s Researched Blueprints</h2>

<p>
	<ul>
		{% for blueprint in blueprints %}
			<li>{{blueprint.description}}</li>
		{% endfor %}
	</ul>
</p>
{% endblock %}