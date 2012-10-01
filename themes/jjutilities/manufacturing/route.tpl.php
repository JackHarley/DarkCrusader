{% extends base %}

{% block pageTitle %}Manufacturing{% endblock %}

{% block content %}
<h2>Manufacturing Route Plan</h2>
	
<p>
	Simply follow the instructions below, if you want you can use the checkboxes to mark as you complete a step

<p>
	{% for instruction in instructions %}
		<input type="checkbox" name="{{forloop.counter}}" id="{{forloop.counter}}" /> <label for="{{forloop.counter}}">{{instruction|unescape}}</a><br />
	{% endfor %}
</p>
{% endblock %}