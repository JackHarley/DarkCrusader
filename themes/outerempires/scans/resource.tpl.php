{% extends base %}

{% block pageTitle %}Scans{% endblock %}

{% block content %}
<h2>Search for a Resource</h2>
	
	<p>
		Simply enter the resource you wish to search for

	</p>
	
	<p>
		<form action="" method="get">
			Resource:<br />
			<input type="text" name="resource" /><br />
			<input type="submit" value="Search" />
		</form>
	</p>
{% endblock %}