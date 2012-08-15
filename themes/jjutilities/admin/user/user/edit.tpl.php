{% extends admin/base %}
{% block title %}Home{% endblock %}

{% block navbar %}
<div id="nav_top" align="center" class="clearfix  round_top">
				<ul class="clearfix">
					<li><a href="{% url /index.php/admin/user/user/list %}"><img src="{% viewurl /admin/resources/images/icons/small/white/List.png %}"/>List Users</a></li>
					<li class="current"><a href="{% url /index.php/admin/user/user/list %}"><img src="{% viewurl /admin/resources/images/icons/small/white/List.png %}"/>Edit Users</a></li>
				</ul>
			</div><!-- #nav_top -->
{% endblock %}

{% if exists notification %}
	<div class="box clearfix grid_16">
		<h2 class="box_head grad_colour round_top">{{notification.message}}</h2>
	</div>
{% endif %}
{% block body %}
				<div class="box clearfix grid_16">
					<h2 class="box_head grad_colour round_top">Editing {{user.username}}</h2>
					<a href="#" class="grabber">&nbsp;</a>
					<a href="#" class="toggle">&nbsp;</a>
					<div class="toggle_container">
						<div class="block">
							<form action="" method="post">
                                
							<p>
								<label class="form-label required">ID:</label>
								<input disabled type="text" class="form-field full" name="id" value="{{user.id}}" />
								<label class="form-label required">Username:</label>
								<input type="text" class="large" name="username" value="{{user.username}}" />
								<label class="form-label required">Clearance Level:</label>
								<input type="text" class="large" name="clearance_level" value="{{user.user_clearance_level}}" />
								<label class="form-label required">User Group:</label>
								<select name="group">
									{% for group in groups %}
										{% if exists user.group.id %}
											{% if group.id == user.group.id %}
												<option value="{{group.id}}" selected="selected">{{group.description}}</option>
											{% else %}
												<option value="{{group.id}}">{{group.description}}</option>
											{% endif %}
										{% else %}
											<option value="{{group.id}}">{{group.description}}</option>
										{% endif %}
									{% endfor %}
								</select>
							</p>
							<p>
								<input class="green skin_colour round_all" type="submit" name="submit" value="Update" />
							</p>
						</form>



						</div>
					</div>

				</div>
{% endblock %}


