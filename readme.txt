=== Groups Blog Protect ===
Contributors: itthinx
Donate link: http://www.itthinx.com/plugins/groups-blog-protect
Tags: 301, 302, 303, 307, 404, access, access control, blog, blogs, capability, capabilities, content, download, downloads, file, file access, files, group, groups, member, members, membership, memberships, permission, permissions, protect, redirect, redirection, subscription, subscriptions
Requires at least: 3.3
Tested up to: 5.2
Stable tag: 1.1.0
License: GPLv3

Protect access to blogs by group membership.

== Description ==

This plugin allows to redirect visitors to a blog who do not belong to the blog's _Registered_ group.

The plugin is an extension to and __requires__ [Groups](http://wordpress.org/extend/plugins/groups/).

Note that the current version does __not__ allow to specify a particular group - at least in that sense, the status of the plugin can be considered somewhat _experimental_.

The redirection settings can be adjusted in Settings > Groups Blog Protect :

Redirection options can be set for the blog - or for each blog in a multisite setup:

- no redirection
- redirect to a specific post, note that with this option the blog is shown to the visitor but only that page can be visited
- redirect to the WordPress login

The redirect status code can be selected among:

- Moved Permanently (301)
- Found (302)
- See other (303)
- Temporary Redirect (307)

== Installation ==

1. Upload or extract the `groups-blog-protect` folder to your site's `/wp-content/plugins/` directory. You can also use the *Add new* option found in the *Plugins* menu in WordPress.  
2. Enable the plugin from the *Plugins* menu in WordPress.
3. Go to Settings > Groups Blog Protect and adjust the redirection settings as desired.

== Frequently Asked Questions ==

= What do the status codes mean? =

Read the section on [Status Code Definitions](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html) in [RFC 2616](http://www.w3.org/Protocols/rfc2616/rfc2616.html).

= I have a question, where do I ask? =

You can leave a comment at the [Groups Blog Protect](http://www.itthinx.com/plugins/groups-blog-protect/) plugin page.

== Screenshots ==

1. Groups Blog Protect settings.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
* Initial release.
