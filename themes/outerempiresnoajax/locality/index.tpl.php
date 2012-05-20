{% extends base %}

{% block pageTitle %}Locality{% endblock %}

{% block content %}
<h2>Get Locality Information</h2>
	
	<p>
		Simply enter the locality you want to search for
	</p>
	
	<p>
		<form action="" method="get">
			Locality:<br />
			<input type="text" name="q" />:<input type="text" name="s" />:<input type="text" name="r" />:<input type="text" name="l" /><br />
			
			<input type="submit" value="Get Locality" />
		</form>
	</p>
{% endblock %}