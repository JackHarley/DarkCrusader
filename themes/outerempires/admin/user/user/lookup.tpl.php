{% extends admin/base %}
{% block title %}Lookup User{% endblock %}

{% block navbar %} 
<div id="nav_top" align="center" class="clearfix  round_top">
	<ul class="clearfix">
		<li>
			<a href="{% url /index.php/admin/user/user/list %}">
				<img src="{% viewurl /admin/resources/images/icons/small/white/List.png %}"/>
				List Users
			</a>
		</li>
		<li>
			<a href="{% url /index.php/admin/user/user/add %}">
				<img src="{% viewurl /admin/resources/images/icons/small/white/Create%20Write.png %}"/>
				Add User
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

<div class="box clearfix grid_9">
	<h2 class="box_head grad_colour round_top">Details for {{user.username}}</h2>
	<a href="#" class="grabber">&nbsp;</a>
	<a href="#" class="toggle">&nbsp;</a>
	<div class="toggle_container">					
		<div class="block">
			<table class="display" id="tabledata">
				<tbody>
					<tr class="even">
						<td>User ID</td>
						<td>{{user.id}}</td>
					</tr>
					<tr class="odd">
						<td>Username</td>
						<td>{{user.username}}</td>
					</tr>
					<tr class="odd">
						<td>Group</td>
						<td>{{user.group.description}}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="box clearfix grid_7">
	<h2 class="box_head grad_colour round_top">Actions</h2>
	<a href="#" class="grabber">&nbsp;</a>
	<a href="#" class="toggle">&nbsp;</a>
	<div class="toggle_container">					
		<div class="block">
			<button class="green skin_colour round_all" ONCLICK="window.location.href='{% url /index.php/admin/user/user/edit %}?id={{user.id}}'">
				<img src="{% viewurl /admin/resources/images/icons/small/white/Pencil.png %}" width="24" height="24" alt="Pencil">
				<span>Edit User</span>
			</button>
			<button class="red skin_colour round_all" ONCLICK="window.location.href='{% url /index.php/admin/user/user/del %}?id={{user.id}}'">
				<img src="{% viewurl /admin/resources/images/icons/small/white/Trashcan.png %}" width="24" height="24" alt="Delete">
				<span>Delete User</span>
			</button>
			<button class="red skin_colour round_all" ONCLICK="window.location.href='{% url /index.php/admin/user/user/ban %}?id={{user.id}}'">
				<img src="{% viewurl /admin/resources/images/icons/small/white/Acces%20Denied%20Sign.png %}" width="24" height="24" alt="Acces Denied Sign">
				<span>Ban User</span>
			</button>
		</div>
	</div>
</div>
{% endblock %}	



