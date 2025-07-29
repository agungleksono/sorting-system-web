### This is Sorting System project build with Laravel 8

#### Prerequisites

-   PHP (7.3 - 8.1)
-   Composer (2.8)

#### Step Instalation

Note: lakukan step 2 ketika project laravel di dapat dari hasil clone git. Jika project laravel didapat dengan copy paste file zip/rar, lewati step 2 dan 4.

1.  Tempatkan laravel project di folder htdocs. Install composer terlebih dahulu, jika composer belum diinstall.
2.  Run command `composer install`
3.  Setting file .env
    Sesuaikan konfigurasi database berikut dengan database production

    ```
    DB_CONNECTION=sqlsrv
    DB_HOST=DESKTOP-9T2C0BF
    DB_PORT=null
    DB_DATABASE=SORTING_SYSTEM
    DB_USERNAME=sa
    DB_PASSWORD="Password#100"
    ```

4.  Run command `php artisan key:generate`
5.  Configure Virtual Host
    Buka file `C:/Apache24/conf/extra/httpd-vhosts.conf`, tambahkan configurasi berikut:
    Sesuaikan ServerName dengan IP address server.

        <VirtualHost *:80>
            ServerAdmin webmaster@localhost
            ServerName 192.168.13.151
            Alias /sorting-system "C:\Apache24\htdocs\sorting-system\public"

            <Directory "C:\Apache24\htdocs\sorting-system\public">
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted
            </Directory>

            ErrorLog "logs/system-sorting.log"
            CustomLog "logs/system-sorting.log" common
        </VirtualHost>

6.  Buka file `C:/Apache24/conf/httpd.conf`.
    Uncomment perintah berikut
    `Include conf/extra/httpd-vhosts.conf`
    `LoadModule rewrite_module modules/mod_rewrite.so`
7.  Tambahkan code berikut pada file .htaccess di folder `public/.htaccess`. Tambahkan di bawah perintah `RewriteEngine On`
    `RewriteBase /sorting-system`
8.  Sesuaikan file .env. Set IP address sesuai dengan IP address server
    APP_URL=http://127.0.0.1/sorting-system
9.  Jalankan command berikut
    `php artisan optimize:clear`
10. Import sql script to generate table in SORTING_SYSTEM database.
