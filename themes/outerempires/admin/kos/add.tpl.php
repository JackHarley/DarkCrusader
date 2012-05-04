{% extends admin/base %}

{% block content %}
<h2>Add Kill on Sight Entry</h2>

<p>
	<form action="" method="post">
		Player Name:<br />
		<input type="text" name="player" /><br />
		<br />
		Reason:<br />
		<input type="text" name="reason" /><br />
		<br />
		<input type="submit" name="submit" value="Add" />
	</form>
</p>
{% endblock %}