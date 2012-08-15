{% extends admin/base %}

{% block title %}Add User{% endblock %}

{% block navbar %} 
<div id="nav_top" align="center" class="clearfix  round_top">
	<ul class="clearfix">
		<li>
			<a href="{% url /index.php/admin/user/user/list %}">
				<img src="{% viewurl /admin/resources/images/icons/small/white/List.png %}"/>List Users
			</a>
		</li>
		<li class="current">
			<a href="{% url /index.php/admin/user/user/add %}">
				<img src="{% viewurl /admin/resources/images/icons/small/white/Create%20Write.png %}"/>Add User
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
	<h2 class="box_head grad_colour round_top">Add User</h2>
	<a href="#" class="grabber">&nbsp;</a>
	<a href="#" class="toggle">&nbsp;</a>
	<div class="toggle_container">					
		<div class="block">
			<form action="" method="post">
                
				<p>
					<label class="form-label required">Username:</label>
					<input type="text" class="large" name="username" />
					
					<label class="form-label required">Password:</label>
					<input type="password" class="large" name="password" />
					
					<label class="form-label required">Clearance Level:</label>
					<input type="text" class="large" name="clearance_level" />

					<label class="form-label required">User Group:</label>
					
					<select name="group">
					{% for group in groups %}
						<option value="{{group.id}}">{{group.description}}</option>
					{% endfor %}
					</select>
				</p>
				<p>
					<input class="green skin_colour round_all" type="submit" name="submit" value="Add" />
				</p>
			</form>
		</div>
	</div>

</div>
{% endblock %}	