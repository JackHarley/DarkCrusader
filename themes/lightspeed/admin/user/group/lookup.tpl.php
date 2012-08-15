{% extends admin/base %}
{% block title %}Viewing Group{% endblock %}

{% block navbar %}
<div id="nav_top" class="clearfix  round_top">
				<ul class="clearfix">
					<li><a href="{% url /index.php/admin/user/group/list %}"><img src="{% viewurl /admin/resources/images/icons/small/white/List.png %}"/>List Groups</a></li>
					<li><a href="{% url /index.php/admin/user/group/add %}"><img src="{% viewurl /admin/resources/images/icons/small/white/Create%20Write.png %}"/>Add A Group</a></li>
				</ul>
			</div><!-- #nav_top -->
{% endblock %}

{% block body %}
{% if exists notification %}
	<div class="box clearfix grid_16">
		<h2 class="box_head grad_colour round_top">{{notification.message}}</h2>
	</div>
{% endif %}
<div class="box clearfix grid_8">
	<h2 class="box_head grad_colour round_top">Details</h2>
	<a href="#" class="grabber">&nbsp;</a>
	<a href="#" class="toggle">&nbsp;</a>
	<div class="toggle_container">
		<div class="block">
			<table class="display" id="tabledata">
				<tbody>
					<tr class="even">
						<td>Group ID</td>
						<td>{{group.id}}</td>
					</tr>
					<tr class="odd">
						<td>Group Internal Name</td>
						<td>{{group.group_name}}</td>
					</tr>
					<tr class="even">
						<td>Group Description</td>
						<td>{{group.description}}</td>
					</tr>
					<tr class="odd">
						<td>Has Permanent Premium</td>
						<td>{% if group.premium == 1 %}Yes{% else %}No{% endif %}</td>
					</tr>
					<tr class="even">
						<td>Clearance Level</td>
						<td>{{group.group_clearance_level}}</td>
					</tr>

				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="box clearfix grid_8">
	<h2 class="box_head grad_colour round_top">Actions</h2>
	<a href="#" class="grabber">&nbsp;</a>
	<a href="#" class="toggle">&nbsp;</a>
	<div class="toggle_container">
		<div class="block">
			<button class="green skin_colour round_all" ONCLICK="window.location.href='{% url /index.php/admin/user/group/edit %}?id={{group.id}}'"><img src="{% viewurl /admin/resources/images/icons/small/white/Pencil.png %}" width="24" height="24" alt="Pencil"><span>Edit Group</span></button>
			<button class="red skin_colour round_all" ONCLICK="window.location.href='{% url /index.php/admin/user/group/del %}?id={{group.id}}'"><img src="{% viewurl /admin/resources/images/icons/small/white/Trashcan.png %}" width="24" height="24" alt="Delete"><span>Delete Group</span></button>
		</div>
	</div>
</div>
<div class="box clearfix grid_16">
	<h2 class="box_head grad_colour round_top">Granted Permissions</h2>
	<a href="#" class="grabber">&nbsp;</a>
	<a href="#" class="toggle">&nbsp;</a>
	<div class="toggle_container">
		<div class="block">
			<table class="display" id="tabledata">
				<tbody>
					{% for perm in perms %}
						<tr>
							<td>{{perm.name}} - {{perm.description}}</td>
						</tr>
					{% endfor %}
				</tbody>
			</table>
		</div>
	</div>
</div>
{% endblock %}


