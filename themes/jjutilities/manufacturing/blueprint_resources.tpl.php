{% extends base %}

{% block pageTitle %}Manufacturing{% endblock %}

{% block js %}
<script type="text/javascript">
	function addresource() {
		console.log(1);
		var i = $('#resourcenumber').val();
		i++;
		$('<br />Name: <input type="text" name="resourcename'+i+'" /> Quantity: <input type="text" name="resourcequantity'+i+'" />').appendTo('#resourcefields');
		$('#resourcenumber').val(i);
	}
</script>
{% endblock %}

{% block content %}
<h2>Manufacturing Route Planner</h2>

<p>
	It appears that the blueprint<br />
	<i>{{blueprintDescription}}</i><br /> 
	has not had its required resources inputted into the database, please add the resources required for this BP below and DOUBLE CHECK ALL SPELLING AND QUANTITY. THERE IS NO WAY TO UPDATE THIS INFORMATION AFTER IT HAS BEEN INPUTTED!
</p>

<form method="post" action="/DarkCrusader/index.php/empire/manufacturing/blueprintresources">
	<input type="hidden" name="blueprint" value="{{blueprintDescription}}" /><br />
	<input type="hidden" id="resourcenumber" value="1" /><br />
	
	<h3 style="margin-top:0;margin-bottom:0;">Resources:</h3>
	<div style="margin-top:0;margin-bottom:0;" id="resourcefields">
		Name: <input type="text" name="resourcename1" /> Quantity: <input type="text" name="resourcequantity1" />
	</div>

	<button type="button" onclick="addresource()">Add Resource...</button><br />
	<br />
	<input type="submit" name="submit" value="Continue" />
</form>
{% endblock %}