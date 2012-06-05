{% extends admin/base %}
{% block title %}Cache Control{% endblock %}

{% block navbar %} 
<div id="nav_top" align="center" class="clearfix  round_top">
	<ul class="clearfix">
		<li>
			<a href="{% url /index.php/admin/maintenance/cache %}">
				<img src="{% viewurl /admin/resources/images/icons/small/white/List.png %}"/>
				Cache
			</a>
		</li>
	</ul>	
</div>
{% endblock %}

{% block body %}

{% if exists notification %}
	<div class="box clearfix grid_16">
		<h2 class="box_head grad_colour round_top">{{notification.message}}</h2>
	</div>
{% endif %}

<div class="box clearfix grid_16">
	<h2 class="box_head grad_colour round_top">Cache Control</h2>
	<a href="#" class="grabber">&nbsp;</a>
	<a href="#" class="toggle">&nbsp;</a>
	<div class="toggle_container">					
		<div class="block">
			<p>Warning! This will clear all site caches, doing this during aperiod of intense traffic could crash your server</p>
			<button class="red skin_colour round_all" ONCLICK="window.location.href='{% url /index.php/admin/maintenance/cache/clearall %}'">
				<img src="{% viewurl /admin/resources/images/icons/small/white/Trashcan.png %}" width="24" height="24" alt="Pencil">
				<span>Clear All Caches</span>
			</button>
		</div>
	</div>
</div>
{% endblock %}	