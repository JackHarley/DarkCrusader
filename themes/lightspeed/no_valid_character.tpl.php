{% extends base %}

{% block pageTitle %}Error{% endblock %}

{% block content %}
<h2>Character Not Set Up</h2>

<p>
	In order to access this site feature, you need to set up a valid character with an OE API access key associated with it.
	You can do this by <a href="{% url /index.php/user/characters %}">clicking here</a> and filling out the bottom form.
</p>
{% endblock %}