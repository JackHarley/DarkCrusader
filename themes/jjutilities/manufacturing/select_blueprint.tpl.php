{% extends base %}

{% block pageTitle %}Manufacturing{% endblock %}

{% block content %}
<h2>Manufacturing Route Planner</h2>

<p>
	Please choose a blueprint you wish to manufacture below to begin
</p>

<form method="post" action="">
	{% for blueprint in blueprints %}
		<input type="radio" name="blueprint" value="{{blueprint.blueprint_description}}" /> {{blueprint.blueprint_description}}<br /><br />
	{% endfor %}<br />
	<input type="submit" name="submit" value="Continue" />
</form>
{% endblock %}