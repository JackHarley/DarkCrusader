{% extends admin/base %}
{% block title %}Home{% endblock %}

{% block navbar %} 
{% endblock %}
{% block body %}
<div class="box grid_16 tabs">
					<div class="side_holder">
						<ul class="tab_sider clearfix">
							<li><a href="#tabs-a">Site Status</a></li>		
							<li><a href="#tabs-e">User Statistics</a></li>	
						</ul>
					</div>
					<div id="tabs-a" class="block tab_sider">
						<h2>Site Status</h2>
						<ul class="full_width">
							<li><span style="color:green">ONLINE</span>Desktop Site</li>
						</ul>
					</div>
					<div id="tabs-e" class="block tab_sider">
						<h2>User Statistics</h2>
						
						<ul class="full_width">
							<li><span>{{userStats.userCount}}</span>Users</li>
							<li><span>{{userStats.userGroupCount}}</span>User Groups</li>
						</ul>
						<br />
						
						<h2>Groups</h2>
						
						<ul class="full_width">
							{% for group in groups %}
								<li><span>{{group.userCount}}</span>{{group.description}}</li>
							{% endfor %}
						</ul>
					</div>
				</div>	
{% endblock %}