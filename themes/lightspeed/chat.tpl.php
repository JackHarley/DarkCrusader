{% extends base %}

{% block pageTitle %}Chat{% endblock %}

{% block content %}
<h2>IRC Chat</h2>

<p>
	Simply click the Connect button below to connect to our secure IRC channel.<br />
	<a target="_blank" href="http://qchat.rizon.net/?nick={{nickname}}&channels={{channel}}{% if exists key %}%20{{key}}{% endif %}&uio=d4">Click here to open a new resizeable window with the chat in it.</a><br />
	Alternatively, use an IRC client to connect to irc.rizon.net #{{channel}} {% if exists key %}with the channel key {{key}}{% endif %}
</p>

<iframe src="http://qchat.rizon.net/?nick={{nickname}}&channels={{channel}}{% if exists key %}%20{{key}}{% endif %}&uio=d4" width="100%" height="350"></iframe>
{% endblock %}