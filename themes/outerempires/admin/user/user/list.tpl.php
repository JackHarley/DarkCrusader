{% extends admin/base %}
{% block title %}List Users{% endblock %}

{% block navbar %} 
<div id="nav_top" align="center" class="clearfix  round_top">
	<ul class="clearfix">
		<li class="current">
			<a href="{% url /index.php/admin/user/user/list %}">
				<img src="{% viewurl /admin/resources/images/icons/small/white/List.png %}"/>List Users
			</a>
		</li>
		<li>
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

<div class="box grid_16 round_all">
	<table class="display table"> 
		<thead> 
			<tr> 
				<th>ID</th>
				<th>Username</th>
				<th>Group</th>
				<th>Actions</th>
			</tr> 
		</thead> 
		<tbody> 
			{% for user in userArray %}
			<tr class="{{forloop.counter0|evenorodd}}">
				<td>{{user.id}}</td>
				<td>{{user.username}}</td>
				<td>{{user.group.description}}</td>
				<td>
					<a HREF="{% url /index.php/admin/user/user/lookup %}?id={{user.id}}">Info</a> |
					<a HREF="{% url /index.php/admin/user/user/del %}?id={{user.id}}">Delete</a> |
					<a HREF="{% url /index.php/admin/user/user/edit %}?id={{user.id}}">Edit</a> |
					<a HREF="{% url /index.php/admin/user/user/ban %}?id={{user.id}}">Ban</a>
				</td>
			</tr>
			{% endfor %}
		</tbody> 
	</table>
</div>
{% endblock %}		
			
				
				