<body style="background-color:black">
	<div style="float:left;width:71%;height:97%">
		<iframe src="http://gameview.outer-empires.com" height="100%" width="100%" marginheight="0" marginwidth="0" frameborder="0"></iframe>
	</div>

	<div style="float:right;width:27%;height:95%">
		<iframe 
			{% if exists nickname %}
				src="http://qchat.rizon.net/?nick={{nickname}}&channels={{channelString}}&uio=MT1mYWxzZSY1PWZhbHNlJjM9ZmFsc2UmNz10cnVlJjk9MTg1JjE0PWZhbHNlJjE1PWZhbHNlJjE2PWZhbHNl0e"
			{% else %}
				src="http://qchat.rizon.net/?prompt=1&channels={{channelString}}&uio=d4"
			{% endif %}
			height="100%"
			marginheight="0"
			marginwidth="0"
			frameborder="0"
			width="100%"
		></iframe>
</body>