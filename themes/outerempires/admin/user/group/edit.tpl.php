{% extends admin/base %}
{% block title %}Editing Group{% endblock %}

{% block navbar %}
<div id="nav_top" class="clearfix  round_top">
				<ul class="clearfix">
					<li><a href="{% url /index.php/admin/user/group/list %}"><img src="{% viewurl /admin/resources/images/icons/small/white/List.png %}"/>List Groups</a></li>
					<li><a href="{% url /index.php/admin/user/group/add %}"><img src="{% viewurl /admin/resources/images/icons/small/white/Create%20Write.png %}"/>Add Group</a></li>
				</ul>
			</div><!-- #nav_top -->
{% endblock %}

{% block body %}
{% if exists notification %}
	<div class="box clearfix grid_16">
		<h2 class="box_head grad_colour round_top">{{notification.message}}</h2>
	</div>
{% endif %}
<div class="box clearfix grid_16">
	<h2 class="box_head grad_colour round_top">Edit Group</h2>
	<a href="#" class="grabber">&nbsp;</a>
	<a href="#" class="toggle">&nbsp;</a>
	<div class="toggle_container">
		<div class="block">
			<form action="" method="post">
                
				<p>
					<label class="form-label required">Group Name (unformatted, e.g. super_moderator, internal use only):</label>
					<input type="text" class="large" name="group_name" value="{{group.group_name}}"/>
					<label class="form-label required">Group Description (e.g. Super Moderator):</label>
					<input type="text" class="large" name="description" value="{{group.description}}"/>
				</p>
				<p>
					<label class="form-label required">Permissions</label>
					<table class="display" id="tabledata">
						<tbody>
							{% for perm in yesPerms %}
								<tr class="{{forloop.counter0|evenorodd}}">
									<td>{{perm.name}}</td>
									<td>{{perm.description}}</td>
									<td>Yes <input checked type="radio" name="{{perm.id}}" value="yes" /></td>
									<td>No <input type="radio" name="{{perm.id}}" value="no" /></td>
								</tr>
							{% endfor %}
							{% for perm in noPerms %}
								<tr class="{{forloop.counter0|evenorodd}}">
									<td>{{perm.name}}</td>
									<td>{{perm.description}}</td>
									<td>Yes <input type="radio" name="{{perm.id}}" value="yes" /></td>
									<td>No <input checked type="radio" name="{{perm.id}}" value="no" /></td>
								</tr>
							{% endfor %}
						</tbody>
					</table>
				</p>
				<p>
					<input class="button themed" type="submit" name="submit" value="Update" />
				</p>
			</form>
		</div>
	</div>
</div>
{% endblock %}

