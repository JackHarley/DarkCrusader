{% extends base %}

{% block pageTitle %}Submit Scan{% endblock %}

{% block content %}

<div id="scanright">
	<h2>Instructions</h2>
	
	<p>
		1, Open a scan in OE<br />
		2, Highlight the text as shown here:<br />
		<img src="{% viewurl /images/scan.png %}" /><br />
		<br />
		3, Paste it in the box on the left as shown here:<br /><br />
		<img src="{% viewurl /images/scan2.png %}" /><br />
		<br />
		4, Press Submit
	</p>
</div>
<div id="scanleft">
	<h2>Add Scan to Database</h2>
	
	<p>
		Paste the scan below.<br />
	</p>

	<form action="" method="post">
		<p>
			<textarea name="scanPaste" rows="8" cols="50"></textarea><br />
			<br />
			<input type="submit" name="submit" value="Submit" />
		</p>
	</form>
	<p id="scansubmissionresult" style="color:lime">
		{% if exists scan %}
			{% for result in scan.scan_results %}
				{{scan.location_string|unescape}} - {{result.resource_string}}<br />
			{% endfor %}
		{% endif %}
	</p>
</div>

{% endblock %}