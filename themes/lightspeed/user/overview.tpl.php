{% extends base %}

{% block pageTitle %}
Overview
{% endblock %}

{% block content %}

<h2>Overview</h2>

<h3>Linked Characters</h3>

<ul>
	<li>Jedi Jackian</li>
</ul>

<h3>Account Details</h3>

<ul>
	<li>User Group: {{activeUser.group.description}}</li>
	<li>Username: {{activeUser.user.username}}</li>
	<li>Premium: Not Subscribed</li>
</ul>

{% endblock %}