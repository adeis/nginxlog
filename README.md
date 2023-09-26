# NGINX LOG
repository ini adalah contoh untuk melakukan parsing nginx log ke html, supaya bisa diexport ke file tertentu seperti excel pdf dll
### Melakukan parsing pada nginx log, ke dalam HTML
Buka confing nginx log
```bash
sudo vi /etc/nginx/nginx.conf
```
di bagian config setting tambahkan pengaturan ini

```bash
# Logging Settings
##
log_format upstream_time '$remote_addr - $remote_user [$time_local] '
			'"$host" '
			'"$request" $status $body_bytes_sent '
			'"$http_referer" "$http_user_agent"'
			'rt=$request_time uct="$upstream_connect_time" uht="$upstream_header_time" urt="$upstream_response_time"';


access_log /var/log/nginx/access.log upstream_time;
```

### jalankan file php
```
php -S 127.0.0.1:8000 -t .
```

atau sesuaikan dengan ip anda, bisa dengan

```
php -S 0.0.0.0:8000 -t .
```
lalu buka browser anda, 127.0.0.1:8000