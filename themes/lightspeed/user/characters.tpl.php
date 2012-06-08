{% extends base %}

{% block pageTitle %}Characters{% endblock %}

{% block content %}
<h2>Characters</h2>

<p>
	Here you can manage your linked characters
</p>

<h2>Linked Characters</h2>
<p>
	These are the characters confirmed as yours, ensure the one you want to use for Personal bank and other site features is set as default
</p>

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

<br />
<br />

<h2>Character Link Requests</h2>
<p>
	These are the characters you have requested a link to, to confirm the link, simply transfer the number of credits under the Verification Amount field to {{siteBankCharacterName}}, then reload this page and the character will appear in the top table, and the amount you transferred will be deposited in your site account
</p>

<table cellpadding="0" border="0">
	<tr>
		<th>Character Name</th>
		<th>API Key</th>
		<th>Verification Amount</th>
		<th></th>
	</tr>

	{% for character in linkRequests %}
		<tr>
			<td>{{character.character_name}}</td>
			<td>{{character.api_key}}</td>
			<td>{{character.verification_amount}}</td>
			<td><a href="{% url /index.php/user/characters %}?act=deleterequest&id={{character.id}}">Delete</a></td>
		</tr>
	{% endfor %}
</table>

<br />
<br />

<h2>Add a New Character</h2>

<p>
	Here you can add a new character to your account. Once you hit Add, it will be added to the Character Link Requests table above. To confirm the link, simply transfer the number of credits under the Verification Amount field to {{siteBankCharacterName}}, then reload this page and the character will appear in the top table, and the amount you transferred will be deposited in your site account
</p>

<form method="post" action="">
	Character Name:<br />
	<input type="text" name="character_name" /><br />
	<br />
	API Key: (this is optional, but without it you won't be able to use many site features, you can find your API key in the Account Management window in Outer Empires)<br />
	<input type="text" name="api_key" /><br />
	<br />
	<input type="submit" name="submit" value="Add" />
</form>

{% endblock %}