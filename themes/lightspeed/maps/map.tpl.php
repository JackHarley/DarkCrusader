<!DOCTYPE html>
<html>
	<head>
		<title>Galaxy Map</title>

		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="{% viewurl /style.css %}" media="screen" />

		<style type="text/css">
			body {
				background: #000000;
				color: white;
			}
			#mapheader {
				text-align: center;
				padding-top: 30px;
				width: {% eval width / scale %}px;
			}
			#mapgalaxy {
				display: block;
				height: {% eval height / scale %}px;
				width: {% eval width / scale %}px;
			}
			#mapwrapper {
				width: {% eval width / scale %}px;
				height: {% eval height / scale %}px;
			}
			.system {
				position: absolute;
			}
			.systemdot {
		        background: #000000;
		        width: 4px;
		        height: 4px;
		        border-radius: 50%;
		        display: block;
			}
			.stationsystemdot {
				background: #000000;
		        width: 9px;
		        height: 9px;
		        border-radius: 50%;
		        display: block;
			}
			.playerownedstationsystemdot {
				background: #000000;
		        width: 13px;
		        height: 13px;
		        border-radius: 50%;
		        display: block;
			}
		</style>
	</head>

	<body>
		<div id="mapwrapper">
			<div id="mapheader">
				<h2>OE Galaxy Map</h2>
			</div>
			<div id="mapgalaxy">
				{% for system in systems %}
					{% if system.system.x %}
						<span title="{{system.faction}}">
							<div class="system" style="top: {% eval (top_padding + system.system.y) / scale %}px;left: {% eval (system.system.x - left_elimination) / scale %}px;">
								{% if system.has_station == 1 %}
									<div class="{% if system.faction != "Government" %}playerowned{% endif %}stationsystemdot" style="background: {{system.hex_colour}}"></div> {% if (system.faction != "Government") || (display_government_system_names) %}{{system.system.system_name}}{% endif %}
								{% else %}
									<div class="systemdot" style="background: {{system.hex_colour}}"></div>
								{% endif %}
							</div>
						</span>
					{% endif %}
				{% endfor %}
			</div>
		</div>
	</body>
</html>