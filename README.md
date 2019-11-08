# GM-X [![Build Status](https://travis-ci.org/gm-x/gmx-web.svg?branch=master)](https://travis-ci.org/gm-x/gmx-web)

Requirements
------------

* PHP >= 5.6.0
* mbstring Extension
* json Extension
* pdo Extension

Installation
------------
* Put files into document root directory
* Open http://example.com/install
* Fill fields and click install

Configuration
------------

Example `nginx` configuration
```
server {
	listen 80 default_server;
	listen [::]:80 default_server;
	server_name example.org;

	root /var/www;

	index index.php index.html;

	location /install {
		try_files $uri /install/index.php$is_args$args;
	}

	location /config.php {
		deny all;
		access_log off;
		log_not_found off;
	}

	location / {
		try_files $uri /index.php$is_args$args;
	}

	# pass PHP scripts to FastCGI server
	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
	
		fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
	}

	# deny access to .htaccess files, if Apache's document root
	# concurs with nginx's one
	location ~ /\.ht {
		deny all;
	}
}
```
