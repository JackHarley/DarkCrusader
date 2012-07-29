{% extends base %}

{% block pageTitle %}Chat{% endblock %}

{% block content %}
<h2>IRC Chat</h2>

<p>
	Simply {% if ! exists nickname %}enter a nickname and {% endif %}click the Connect button below to connect to our secure IRC channel, if you are a signed in SWAT/FIRE member you will also be connected to our private #swatfire channel<br />
	<a 
		target="_blank" 
		{% if exists nickname %}
			href="http://qchat.rizon.net/?nick={{nickname}}&channels={{channelString}}&uio=d4"
		{% else %}
			href="http://qchat.rizon.net/?prompt=1&channels={{channelString}}&uio=d4"
		{% endif %}
	>
		Click here to open a new resizeable window with the chat in it.
	</a><br />
	Alternatively, use an IRC client to connect to {{connectString}}
</p>

<iframe 
	{% if exists nickname %}
		src="http://qchat.rizon.net/?nick={{nickname}}&channels={{channelString}}&uio=d4"
	{% else %}
		src="http://qchat.rizon.net/?prompt=1&channels={{channelString}}&uio=d4"
	{% endif %}
	width="100%" 
	height="310"
>
</iframe>
{% endblock %}