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
				margin: 0 auto;
			}
			#mapgalaxy {
				display: block;
				height: {% eval height / scale %}px;
				width: {% eval width / scale %}px;
				margin: 0 auto;
			}
			#mapwrapper {
				width: {% eval width / scale %}px;
				height: {% eval height / scale %}px;
				margin: 0 auto;
			}
			.system {
				position: absolute;
				height: 10px;
				width:150px;
			}
			.systemname {
			}
			.systemdot {
		        background: #000000;
		        width: 4.5px;
		        height: 4.5px;
		        border-radius: 50%;
		        float:left;
		        margin-right:2px;
			}
			.stationsystemdot {
				background: #000000;
		        width: 9px;
		        height: 9px;
		        border-radius: 50%;
		        float:left;
		        margin-right:4px;
			}
			.playerownedstationsystemdot {
				background: #000000;
		        width: 13px;
		        height: 13px;
		        border-radius: 50%;
		        float: left;
		        margin-right:4px;
		        overflow: hidden;
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
						<span title="{{system.system.system_name}} ({{system.faction}})">
							<a href="{% url /index.php/systems %}?name={{system.system.system_name}}" target="_blank">
								<div class="system" style="top: {% eval (top_padding + system.system.y) / scale %}px;left: {% eval (system.system.x - left_elimination) / scale %}px;">
									{% if system.has_station == 1 %}
										<div class="{% if system.faction != "Government" %}playerowned{% endif %}stationsystemdot" style="background: {{system.hex_colour}}"></div> <div class="systemname">{% if (system.faction != "Government") || (display_government_system_names) %}{{system.system.system_name}}{% endif %}</div>
									{% else %}
										<div class="systemdot" style="background: {{system.hex_colour}}"></div>
									{% endif %}
								</div>
							</a>
						</span>
					{% endif %}
				{% endfor %}
			</div>
		</div>
	</body>
</html>