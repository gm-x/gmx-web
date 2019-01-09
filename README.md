# GameX

### Installation
```
composer install
```
Go to `http://example.com/install`

### Tests
```
./vendor/bin/phpunit --bootstrap vendor/autoload.php tests
```

### Configuration

#### Nginx
```
server {
	listen 80 default_server;
	listen [::]:80 default_server;

	root /var/www/html;
	index index.html;

	server_name _;

	location /install {
		try_files $uri /install/index.php$is_args$args;
	}

	location / {
		try_files $uri /public/index.php$is_args$args;
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
