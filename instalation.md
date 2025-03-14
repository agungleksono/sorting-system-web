### This is Sorting System project build with Laravel 8

#### Prerequisites

PHP (7.3 - 8.1)
Composer (2.8)

#### Step Instalation

1. Tempatkan laravel project di folder htdocs
2. Run command `composer install`
3. Setting file .env
   Sesuaikan konfigurasi database berikut dengan database production
   DB_CONNECTION=sqlsrv
   DB_HOST=DESKTOP-9T2C0BF
   DB_PORT=null
   DB_DATABASE=SORTING_SYSTEM
   DB_USERNAME=sa
   DB_PASSWORD="Password#100"
4. Run command `php artisan key:generate`
5. Configure Virtual Host
   Buka file `C:/Apache24/conf/extra/httpd-vhosts.conf`, tambahkan configurasi berikut

    ```
    <VirtualHost *:80>
      DocumentRoot "C:/Apache24/htdocs/sorting-system/public"
      ServerName 192.168.1.100

      <Directory "C:/Apache24/htdocs/sorting-system/public">
            AllowOverride All
            Require all granted
      </Directory>
    </VirtualHost>
    ```

6. Sesuaikan file .env
   APP_URL=http://192.168.1.100/sorting-system
