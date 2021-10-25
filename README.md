# promo-codes
Setup Instructions based on a Ubuntu(Linux) server Installation
 - Server Version - Ubuntu Server Version 20.04 X64

Server Configuration
-------------------------------

1. Login to your Server via Terminal
    ssh root@206.189.24.XXX
    Enter password : ********

2. Installing Apache and Updating the Firewall
    apt update
    apt install apache2

    - Adjust the Firewall to Allow Web Traffic
    sudo ufw app list

    - If you look at the Apache Full profile details, you’ll see that it enables traffic to ports 80 and 443:
    sudo ufw app info "Apache Full"
    
    - To allow incoming HTTP and HTTPS traffic for this server, run
    sudo ufw allow "Apache Full"
    
    - Test by visiting your server’s public IP address in your web browser, this should display apaches default page
    http://206.189.24.XXX

3. Install php 7.4 and all extensions required by Laravel

    apt-get update && apt-get upgrade
    ***apt-get install python-software-properties - REMOVE***

    add-apt-repository ppa:ondrej/php

    apt-get update

    apt-get install php7.4

    apt-get install php-pear php7.4-curl php7.4-dev php7.4-gd php7.4-mbstring php7.4-zip php7.4-mysql php7.4-xml

    sudo systemctl restart apache2
    sudo systemctl status apache2

    - Install sqlite3 for running unit tests

    sudo apt-get install php7.4-sqlite3

4. Install Mysql and create database
    apt update
    apt install mysql-server
    sudo mysql_secure_installation 
        # make sure you pick strong password strength policy, remove anonymous users and and disallow remote root login - or your preferred equivalent settings
        # select yes for reload root priviledge table

    mysql -u root -p
        Enter password next as "********"

        ****Recommendation - Login and create a mysql user for safeboda laravel app
    - Create mysql app user and grant priviledges as desired
        CREATE USER 'micmukima_dev'@'localhost' IDENTIFIED BY '********';
        CREATE USER 'micmukima_dev'@'127.0.0.1' IDENTIFIED BY '********';

        GRANT ALL PRIVILEGES ON * . * TO 'micmukima_dev'@'localhost';
        GRANT ALL PRIVILEGES ON * . * TO 'micmukima_dev'@'127.0.0.1';

        FLUSH PRIVILEGES;
        
    # run following commands from mysql logged in console    
    create database safeboda_promo;

    show databases;
        *** safeboda_promo database should appear in the list displayed
    exit;

5. Install Laravel project from GitHub
    cd /var/www/html

    rm *

    git clone https://github.com/micmukima/promo_codes.git

    cd safeboda-promo/

    /******
    * install composer2 since main composer is deprecated
    */

    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

    php -r "if (hash_file('sha384', 'composer-setup.php') === '756890a4488ce9024fc62c56153228907f1545c228516cbf63f885e036d37e9a59d27d63f46af1d4d07ee0f76181c7d3') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

    sudo php composer-setup.php --install-dir=/usr/bin --filename=composer

    - While inside project home directory, run the following command to install application libraries
    
    composer install

    sudo vim .env

    - set db name, username: root, password: your mysql password that you saved

    php artisan key:generate

    php artisan migrate --step

    - To fill up the database with dummy test data, run the command
    php artisan db:seed

6. Configure application for Access via Apache

    cd /etc/apache2/sites-available

    Change content of the file as follows making sure you get the path to your projects public folder correctly

    sudo vim 000-default.conf

    - Add following information

    <VirtualHost *:80>
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/html/safeboda-promo/public

        <Directory /var/www/html/safeboda-promo/public>

            Options Indexes FollowSymLinks

            AllowOverride All

            Require all granted

        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

        <IfModule mod_dir.c>

        DirectoryIndex index.php index.pl index.cgi index.html index.xhtml index.htm

        </IfModule>
    </VirtualHost>

    Save file and exit

    - Enable mod_rewrite as follows
    sudo a2enmod rewrite

    - To activate the new configuration, you need to run:
    systemctl restart apache2

    Give permission to storage folder.
    cd /var/www/html/safeboda-promo

    sudo chmod -R 777 storage


    Your project should be running now. Visit your server IP address to verify. 
    http://206.189.24.XXX/

    Optional step - if you have a custom domain you can use your provider hosting portal and map it to your server
    For digital ocean see details HERE https://www.digitalocean.com/docs/networking/dns/quickstart/

   
    - Updating changes from GitHub
    cd /var/www/html/safeboda-promo
    git pull

    - Setting application to production mode, change following variables in .env
    APP_ENV=production and APP_DEBUG=false

7. Configure application for Access via Nginx

    This is an alternative deployment option for apache2 but will not focus on it for NOW
    

8. Running Application tests
    - Via artisan
        - php artisan test
    - application is also configured to run tests via composer as follows
        - composer test
    - natively test using phpunit
        - ./vendor/bin/phpunit --testdox tests
