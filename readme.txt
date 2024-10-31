=== Restrict Partial Content Plugin ===
Contributors: speedito
Tags: Restrict specific content portion for role type or users
Requires at least: 3.9.1
Tested up to: 4.4.2
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a simple plugin to restrict the access based on user role, user id or date/time (displays a countdown timer).

== Description ==
Typical use case would be to restrict access to a portion of the content to users unless. Restriction can be based on user role (to boost user subscriptions). You can also setup a timer function to open up content after a certain time.

Options available are:
<ol>
<li>Restrict access based on the role of user</li>
<li>Restrict access based on specific user id/user name</li>
<li>Restrict access based date/time</li>
</ol>

See FAQ section for details and examples

== Installation ==
1. Upload zip archive `restrict-partial-content.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==
Shortcode to use: [restrict][/restrict]
All content between the start and end of the shortcode will be restricted

Shortcode parameters

1. allow_role => The Role(s) to allow access to the restricted content. This should correspond to the Wordpress roles. Can take multiple values which are comma separated

2. allow_user => The User ID(s) OR user login name to allow access to the restricted content.

3. message => The message that is visible when content is restricted

4. open_time => The exact date and time when the content should become visible (format to use YYYY-MM-DD HH:MM:SS example 2016-03-24 13:00:00 will mean that the content should show after 24 March 2016 from 1pm onwards)

5. condition => options are "any" and "all" - "any" will mean that any single parameter that matches the criteria will result in the protected content being shown. "all" will mean that only when all criteria is matched will the restricted content be shown.

6. rule => Now you can define rules and use these easily. This gives the flexibility of changing the criteria in one location and having it apply everywhere that the rule has been used. If the rule parameter is used then it will overrule the other parameters.

Some examples
Example 1
[restrict allow_role="subscriber" allow_user = "1, 2, adam" open_time="2016-03-14 11:00:00" condition="any" message="hello"] secret here[/restrict]
This will show the restrcited to all subscribers, users with ID 1 and 2, username "adam". As soon as the open_time is passed it will show the restricted content to everyone.

Example 2
[restrict allow_role="author" open_time="2016-03-14 11:00:00" condition="all" message="hello"] secret here[/restrict]
This will show only when the user is logged in with a subscriber role and the open_time has passed

Example 3
[restrict rule="2"] secret here[/restrict]
This will apply rule with id 2. Rules can be defined from inside the WP dashboard.

== Screenshots ==
1. Restrict content rule definition
2. Shortcode with all parameters
3. Shortcode with the rule parameter


== Changelog ==
= 1.3 =
Now allows rules to be created and applied

= 1.2 =
Added parameter "condition" for greater flexibility in controlling when the criteria matches
Allow_user now accepts user id OR usernames
Also small fixes suggested by "hostz-frank"

= 1.1 =
Small fixes to styling

= 1.0 =
Added option to restrict content based on role and timeouts

= 0.1 =
Initial launch
