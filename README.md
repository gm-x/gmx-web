# GameX


```
server {
	listen 80 default_server;
	listen [::]:80 default_server;

	root /var/www/html;
	index index.html;

	server_name _;
	
	location /assets {}
	location /uploads {}

	location /install {
		try_files $uri /install/index.php$is_args$args;
	}

	location / {
		try_files $uri /index.php$is_args$args;
 	}

	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
	
		fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
	}
	
	location ~ /\.ht {
		deny all;
	}
}
```
