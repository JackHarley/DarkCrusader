{% extends base %}

{% block pageTitle %}Characters{% endblock %}

{% block content %}
<h2>Characters</h2>

<p>
	Here you can manage your linked characters
</p>

{% if ! empty linkedCharacters %}
<h3>Linked Characters</h3>
<p>
	These are the characters confirmed as yours, ensure the one you want to use for Personal bank and other site features is set as default
</p>

<p>
	<table cellpadding="0" border="0">
		<tr>
			<th>Character Name</th>
			<th>API Key</th>
			<th>Default?</th>
			<th></th>
		</tr>

		{% for character in linkedCharacters %}
			<tr>
				<td>{{character.character_name}}</td>
				<td>{{character.api_key}}</td>
				<td>{% if character.is_default == 1 %}Yes{% else %}No{% endif %}</td>
				<td>
					<a href="{% url /index.php/user/characters %}?act=deletecharacter&id={{character.id}}">Delete</a>  
					{% if character.is_default == 0 %}
						| <a href="{% url /index.php/user/characters %}?act=default&id={{character.id}}">Make Default</a>
					{% endif %}
				</td>
			</tr>
		{% endfor %}
	</table>
</p>

{% endif %}

{% if ! empty linkRequests %}
<h3>Character Link Requests</h3>
<p>
	These are the characters you have requested a link to, THEY CANNOT BE USED FOR ANYTHING UNTIL YOU CONFIRM THE LINK!
	To confirm the link, simply type the command under Verification Command into OE Chat and hit enter, then click the [Update] link in the sidebar next to Site Bank
</p>

<p>
	<table cellpadding="0" border="0">
		<tr>
			<th>Character Name</th>
			<th>API Key</th>
			<th>Verification Command</th>
			<th></th>
		</tr>

		{% for character in linkRequests %}
			<tr>
				<td>{{character.character_name}}</td>
				<td>{{character.api_key}}</td>
				<td>/transfercredits {{siteBankCharacterName}},{{character.verification_amount}}</td>
				<td><a href="{% url /index.php/user/characters %}?act=deleterequest&id={{character.id}}">Delete</a></td>
			</tr>
		{% endfor %}
	</table>
</p>

{% endif %}

<h3>Add a New Character</h3>

<p>
	Here you can add a new character to your account.
</p>

<p>
	<form method="post" action="">
		Character Name:<br />
		<input type="text" name="character_name" /><br />
		<br />
		API Key: (this is optional, but verification is instant with it, and without it you won't be able to use many site features. It is highly recommended you give your API key unless you are extremely paranoid. You can find your API key in the Account Management window in Outer Empires)<br />
		<input type="text" name="api_key" /><br />
		<br />
		<input type="submit" name="submit" value="Add" />
	</form>
</p>

{% endblock %}