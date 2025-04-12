=== Groups Blog Protect ===
Contributors: itthinx
Donate link: https://www.itthinx.com/shop/
Tags: groups, access, access control, construction, lockdown
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.5.0
License: GPLv3

Protect access to blogs via group memberships powered by [Groups](https://wordpress.org/plugins/groups/).

== Description ==

The plugin protects a site so that only members can access its pages.

Suitable for site lockdowns, private sites and sites that are under construction.

It allows to redirect visitors to a particular page or to the WordPress login.

Members who are logged in have access to the site.
Members must belong to the site's _Registered_ group, or to a particular group as determined specifically for the site.

The plugin is an extension to [Groups](https://wordpress.org/plugins/groups/) which is required.

The redirection settings can be adjusted in *Settings > Groups Blog Protect* :

Redirection options can be set for the blog - or for each blog in a multisite setup:

- no redirection
- redirect to a specific post, note that with this option the blog is shown to the visitor but only that page can be visited
- redirect to the WordPress login

The redirect status code can be selected among:

- Moved Permanently (301)
- Found (302)
- See other (303)
- Temporary Redirect (307)

Site administrators can determine which group is used to protect the site:

- By default, users must belong to the _Registered_ group to be able to access the site, so any registered user who is logged in will have access.
- The constant `GROUPS_BLOG_PROTECT_GROUP` can be set in `wp-config.php` to indicate the name of the group to which users must belong to be able to access the site.
- For multisites, the `GROUPS_BLOG_PROTECT_GROUP_n` constant can be used to indicate the name of the group required to access a particular site, where `n` is the blog ID of the site.

== Installation ==

1. Upload or extract the `groups-blog-protect` folder to your site's `/wp-content/plugins/` directory. You can also use the *Add new* option found in the *Plugins* menu in WordPress.
2. Enable the plugin from the *Plugins* menu in WordPress.
3. Go to *Settings > Groups Blog Protect* and adjust the redirection settings as desired.

== Frequently Asked Questions ==

= Where is the documentation? =

The documentation for this plugin is at [Groups Blog Protect](https://docs.itthinx.com/document/groups-blog-protect/).

The documentation for Groups is at [Groups](https://docs.itthinx.com/document/groups/).

= What do the status codes mean? =

Read the section on [Status Code Definitions](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html) in [RFC 2616](http://www.w3.org/Protocols/rfc2616/rfc2616.html).

= I have a question, where do I ask? =

You can leave a comment at the [Groups Blog Protect](https://www.itthinx.com/plugins/groups-blog-protect/) plugin page.

== Screenshots ==

1. Groups Blog Protect settings.

== Changelog ==

For the full changelog see [changelog.txt](https://github.com/itthinx/groups-blog-protect/blob/master/changelog.txt).

== Upgrade Notice ==

This release has been tested with the latest version of WordPress.
