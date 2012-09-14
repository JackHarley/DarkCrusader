<body style="background-color:black">
	<div style="height:70%">
		<div style="float:left;width:49%;height:100%">
			<iframe src="http://gameview.outer-empires.com" height="100%" width="100%" marginheight="0" marginwidth="0" frameborder="0"></iframe>
		</div>
		<div style="float:right;width:49%;height:100%">
			<iframe src="http://gameview.outer-empires.com" height="100%" width="100%" marginheight="0" marginwidth="0" frameborder="0"></iframe>
		</div>
	</div>

	<div style="width:100%;height:28%">
		<iframe 
			{% if exists nickname %}
				src="http://qchat.rizon.net/?nick={{nickname}}&channels={{channelString}}&uio=d4"
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