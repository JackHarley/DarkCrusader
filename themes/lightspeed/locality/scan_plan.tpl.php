{% extends base %}

{% block pageTitle %}Locality Scan Plan{% endblock %}

{% block content %}
<h2>Locality Scan Plan Instructions</h2>
	
<p>
	Simply follow the instructions below, if you want you can use the checkboxes to mark as you complete a step

<p>
	<ul>
		{% for instruction in instructions %}
			<li><input type="checkbox" name="{{forloop.counter}}" /> {{instruction}}</li>
		{% endfor %}
	</ul>
</p>
{% endblock %}