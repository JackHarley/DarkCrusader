{% extends base %}

{% block pageTitle %}Manufacturing{% endblock %}

{% block content %}
<h2>Manufacturing Route Plan</h2>

<h3>Blueprint: <i>{{blueprint}}</i></h3>

<h3>Information</h3>
<p>
	You can make x{{items}} of this blueprint at the moment.<br />
	Your limiting factor for manufacturing this round was your
	{% if handycap == "resource" %}
		available resources, the resource that limited you the most was {{handycapResource}}. It is recommended you build additional {{handycapResource}} mines or buy some additional {{handycapResource}} on the market.
	{% else if handycap == "shipStorage" %}
		ship storage, upgrade to a bigger ship!
	{% else if handycap == "manufacturingColonyStorage" %}
		manufacturing colony storage, free up some space in your manufacturing colony or build some storage facilities.
	{% endif %}
</p>

{% if items > 0 %}
<h3>Instructions</h3>
<p>
	{% for instruction in instructions %}
		<input type="checkbox" name="{{forloop.counter}}" id="{{forloop.counter}}" /> <label for="{{forloop.counter}}">{{instruction|unescape}}</a><br />
	{% endfor %}
</p>
{% endif %}

{% endblock %}