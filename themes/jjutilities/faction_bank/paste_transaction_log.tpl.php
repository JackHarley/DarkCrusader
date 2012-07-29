{% extends base %}

{% block pageTitle %}Bank{% endblock %}

{% block content %}
<h2>Paste Transaction Log</h2>

<p>
	Paste the transaction log below, don't worry about duplicates, the system will automatically detect them and won't add them again
</p>

<p>
	<form method="post" action="">
		<textarea rows="25" cols="100" name="paste"></textarea><br /> 
		<input type="submit" value="Submit" />
	</form>
</p>

{% endblock %}