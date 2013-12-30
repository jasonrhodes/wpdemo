Simple JSON API for WordPress
=============================

Version 0.2

Generate a simple, read-only JSON REST API for any WordPress site. Just install, activate, and visit mysite.com/api/{post_type_name} to see a JSON payload of that post type.

Use any of the params found at <http://codex.wordpress.org/Class_Reference/WP_Query> by including them via the ?querystring to adjust the returned payload.

To query for an individual object, visit mysite.com/api/{post_type_name}/{id}

### Really Simple, Basic Authentication

If you want to restrict access to your newly generated read-only REST API to people who have the master API key, you can set that key in the plugin's options page.

### TO DO

* Better auth: Make it easier to customize API keys per user instead of only using a master hard-coded key
* Consider not requiring {post_type_name} when requesting a single resource since ID is unique across all types.
* Allow user to adjust the endpoint for the URL (ie "/api")

Please fork and submit a pull request for any proposed changes.
