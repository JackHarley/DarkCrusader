{% extends base %}

{% block pageTitle %}
Overview
{% endblock %}

{% block content %}

<h2>{{activeUser.username}}</h2>

<ul>
	<li>User Group: {{activeUser.group.description}}</li>
	<li>Username: {{activeUser.username}}</li>
	<li>Premium: 
		{% if exists userIsPremium %}
			<span style="color:lime">Subscribed</span>
		{% else %}
			<span style="color:red">Not Subscribed</span>
		{% endif %}
	</li>
</ul>

<h3>Linked Characters - <a href="{% url /index.php/user/characters %}">Manage</a></h3>

{% if linkedCharacters|length < 1 %}
	<p>You have no linked characters</p>
{% else %}
	<ul>
		{% for character in linkedCharacters %}
			<li>{{character.character_name}}</li>
		{% endfor %}
	</ul>
{% endif %}

<h3>Premium</h3>

<p>
	{% if exists userIsPremium %}
		{% if activeUser.group.premium == 1 %}
			You are currently subscribed to premium indefinitely because you are a {{activeUser.group.description}}
		{% else %}
			You are currently subscribed to premium and your premium will expire on {{activeUser.premium_until}}
		{% endif %}
	{% else %}
		You are currently not subscribed to premium, subscribe below!
	{% endif %}

	<br />
	
	Premium gives you a number of exclusive features on the site, including a better personal bank and market features. Check the FAQ for a full list of premium features.
</p>

{% if activeUser.group.premium != 1 %}
	<h3>Subscribe/Extend Premium</h3>

	<p>
		You can add funds to your account by simply transferring credits to the OE account '{{siteBankCharacterName}}' from any of your linked characters, then click the [Update] button in the sidebar to update the balance in the sidebar<br />
		For example, to transfer 10,000c, you would type <i>/transfercredits {{siteBankCharacterName}},10000</i> into chat
	</p>

	<p>
		<form method="post" action="">
			Select an option:<br />
			<input type="radio" name="duration" value="day" /> 1 Day - 5,000c<br />
			<input type="radio" name="duration" value="week" /> 7 Days - 18,000c<br />
			<input type="radio" name="duration" value="30days" /> 30 Days - 50,000c<br />
			<input type="radio" name="duration" value="lifetime" /> Lifetime (~100 years) - 500,000c<br />
			<br />
			<input type="submit" name="submit" value="Subscribe/Extend" />
		</form>
	</p>
{% endif %}

{% endblock %}