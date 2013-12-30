wpdemo
======

WordPress demo files for workshop at Fastspot


## Setup

Assuming you're on a Mac, to get started quickly, you can run the following on the CLI:

```bash
$ git clone git@github.com:jasonrhodes/wpdemo.git
$ cd wpdemo
$ cat setup/vhost.txt >> /etc/apache2/extra/httpd-vhosts.conf
$ cat setup/hosts.txt >> /etc/hosts
$ mysql -u root -p wpfs < sql/db-installed.sql
$ mysql -u root -p < sql/create-db-user.sql
```

You'll need to open /etc/apache2/extra/httpd-vhosts.conf and edit the 4 places at the end of the file where it says `/path/to/wpdemo/folder` to be the full path to your cloned repo.

Then: `$ sudo apachectl restart`


## Logging in

If you set it up like above, you can log in at http://wpfs.dev/wp/wp-admin and see the site at http://wpfs.dev -- login credentials are admin/password


## Switching Branches

You may need to run `$ git fetch` to get all of the origin branches. We'll be switching to different branches and loading databases from the /sql folder to demo different things.


## Loading Databases

To load a new database, do `$ mysql -u root -p wpfs < sql/name-of-file.sql`