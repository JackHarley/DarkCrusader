{% extends admin/base %}
{% block title %}List Groups{% endblock %}

{% block navbar %}
<div id="nav_top" class="clearfix  round_top">
				<ul class="clearfix">
					<li class="current"><a href="{% url /index.php/admin/user/group/list %}"><img src="{% viewurl /admin/resources/images/icons/small/white/List.png %}"/>List Groups</a></li>
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
<div class="box clearfix grid_16">
				<table class="display table">
					<thead>
						<tr>
							<th>ID</th>
							<th>Group Name</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
					{% for group in groupArray %}
						<tr class="{{forloop.counter0|evenorodd}}">
							<td>{{group.id}}</td>
							<td>{{group.description}}</td>
							<td>
								<a class="button white" href="{% url /index.php/admin/user/group/lookup %}?id={{group.id}}">
									Info
								</a>
								|
								<a class="button white" href="{% url /index.php/admin/user/group/del %}?id={{group.id}}">
									Delete
								</a>
								|
								<a class="button white" href="{% url /index.php/admin/user/group/edit %}?id={{group.id}}">
									Edit
								</a>
							</td>
						</tr>
						{% endfor %}
					</tbody>
				</table>
			</div>
{% endblock %}


