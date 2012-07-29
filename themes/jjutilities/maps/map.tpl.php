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
				height: 18px;
			}
			.namedsystem {
				position: absolute;
				height: 18px;
				width: 100px;
			}
			.specialsystem {
				position: absolute;
				height: 18px;
				width: 100px;
				padding-right: 10px;
				border-width: 2px;
				border-color: white;
				border-style: dotted;
			}
			.systemname {
			}
			.systemdot {
		        background: #000000;
		        width: 4.5px;
		        height: 4.5px;
		        border-radius: 50%;
		        float:left;
		        margin-right:4px;
		        z-index:900;
			}
			.stationsystemdot {
				background: #000000;
		        width: 9px;
		        height: 9px;
		        border-radius: 50%;
		        float:left;
		        margin-right:4px;
		        vertical-align: middle;
		        z-index:1000;
			}
			.playerownedstationsystemdot {
				background: #000000;
		        width: 13px;
		        height: 13px;
		        border-radius: 50%;
		        float: left;
		        margin-right:4px;
		        overflow: hidden;
		        z-index:1000;
			}
		</style>
	</head>

	<body>
		<div id="mapwrapper">
			<div id="mapheader">
				<h2>OE Galaxy Map</h2>
			</div>	
			<div id="mapgalaxy">
				{% set specialSystemOutputted "no" %}
				{% for system in systems %}
					{% if system.system.x %}
						<div 
							class="{% if (exists specialSystem) && (specialSystem.system.id == system.system.id) %}{% set specialSystemOutputted "yes" %}special{% else if ((exists specialSystem) && (specialSystem.system.id == system.system.id)) || ((system.faction == "Government") && (display_government_system_names)) || ((system.faction != "Government") && (system.has_station == 1)) %}named{% endif %}system" 
							style="top: {% eval (top_padding + system.system.y) / scale %}px;left: {% eval (system.system.x - left_elimination) / scale %}px;"
						>
							<span title="{{system.system.system_name}} ({{system.faction}})">
								<a href="{% url /index.php/systems/system %}?name={{system.system.system_name}}" target="_blank">
									{% if system.has_station == 1 %}
										<div class="{% if system.faction != "Government" %}playerowned{% endif %}stationsystemdot" style="background: {{system.hex_colour}}"></div> {% if (system.faction != "Government") || (display_government_system_names) %}<div class="systemname">{{system.system.system_name}}</div>{% endif %}
									{% else %}
										<div class="systemdot" style="background: {{system.hex_colour}}"></div> {% if (exists specialSystem) && (specialSystem.system.id == system.system.id) %}<div class="systemname">{{system.system.system_name}}</div>{% endif %}
									{% endif %}
								</a>
							</span>
						</div>
					{% endif %}
				{% endfor %}

				{% if (exists specialSystem) && (specialSystemOutputted == "no") %}
					{% if specialSystem.system.x %}
						<div class="specialsystem" style="top: {% eval (top_padding + specialSystem.system.y) / scale %}px;left: {% eval (specialSystem.system.x - left_elimination) / scale %}px;">
							<span title="{{specialSystem.system.system_name}} ({{specialSystem.faction}})">
								<a href="{% url /index.php/systems/system %}?name={{specialSystem.system.system_name}}" target="_blank">
									{% if specialSystem.has_station == 1 %}
										<div class="{% if specialSystem.faction != "Government" %}playerowned{% endif %}stationsystemdot" style="background: {{specialSystem.hex_colour}}"></div> <div class="systemname">{{specialSystem.system.system_name}}</div>
									{% else %}
										<div class="systemdot" style="background: {{specialSystem.hex_colour}}"></div> <div class="systemname">{{specialSystem.system.system_name}}</div>
									{% endif %}
								</a>
							</span>
						</div>
					{% endif %}
				{% endif %}
			</div>
		</div>
	</body>
</html>