{% extends admin/base %}
{% block title %}Home{% endblock %}

{% block navbar %} 
{% endblock %}
{% block body %}
<div class="box grid_16 tabs">
					<div class="side_holder">
						<ul class="tab_sider clearfix">
							<li><a href="#tabs-a">Site Status</a></li>
						</ul>
					</div>
					<div id="tabs-a" class="block tab_sider">
						<h2>Site Status</h2>
						<ul class="full_width">
							<li><span style="color:green">ONLINE</span>Desktop Site</li>
						</ul>
					</div>
{% endblock %}