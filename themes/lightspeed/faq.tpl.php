{% extends base %}

{% block pageTitle %}FAQ{% endblock %}

{% block content %}
<h2>Frequently Asked Questions</h2>

<h3>What is Premium?</h3>
<p>
	Premium is a subscription based service offered to all users of the SWAT/FIRE site. 
	You can see prices and subscribe on your Account Settings page.<br />
	Premium subscription takes credits out of your site bank account.
</p>

<h3>I didn't purchase premium but the site says I have it</h3>
<p>
	Certain user groups get access to premium for free, this includes SWAT and FIRE members and also a few select independents and developers
</p>

<h3>Where does the money I pay for premium go to?</h3>
<p>
	Premium money gets divided up between SWAT, FIRE and Jedi Jackian (40% to SWAT, 40% to FIRE and 20% to Jedi Jackian)
</p>

<h3>What benefits does premium give me?</h3>
<p>
	<ul>
		<li>Local storage of personal bank transactions (much faster loading)</li>
		<li>Time period selection for personal bank (coming soon)</li>
		<li>Market seller overview allows you to see an overview of your top customers</li>
		<li>Exclusive handy utilities such as a ship build tester and colony setup tester (coming soon)</li>
		<li>Exclusive market discounts from participating SWAT/FIRE members (coming soon)</li>
		<li>Tonnes of other new features that are in the works</li>
	</ul>
</p>

<h3>What is my "Site Bank Account" and what is it for?</h3>
<p>
	Your site bank account is a special account that was created for you when you joined this site.
	You can see your balance in the sidebar on the left hand side. It may already have credits in it if had to transfer credits to verify your account (only people who do not add an API key have to verify their accounts in this way).
	The site bank account is used in a number of ways on the site, one use is that you can subscribe to premium from the balance in your site account
</p>

<h3>How do I add credits to my site bank account</h3>
<p>
	Simply transfer credits to {{siteBankCharacterName}} from any of your linked characters (<a href="{% url /index.php/user/characters %}">you can manage linked characters here</a>) and then reload this site, your balance will automatically update.
	For example, to add 1,000c to your site account, type this into OE chat: <i>/transfercredits {{siteBankCharacterName}},1000</i>
</p>
{% endblock %}
