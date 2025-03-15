<!-- <p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). -->

### This is Sorting System project build with Laravel 8

#### Prerequisites

-   PHP (7.3 - 8.1)
-   Composer (2.8)

#### Step Instalation

Note: lakukan step 2 & 3 ketika project laravel di dapat dari hasil clone git. Jika project laravel didapat dengan copy paste file zip/rar, lewati step 2 & 3.

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
           ServerName 127.0.0.1
           DocumentRoot "C:\Apache24\htdocs\sorting-system\public"
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
