Raspberry Pi Kiosk Setup Instructions
--------------------------------------------------

Purpose: Set up a Raspberry Pi (model 4 or higher) as a plug-and-play kiosk for the Virginia Western Community College Cybersecurity Lab. This guide walks through preparing the system to be captured into a .img file that the client can flash onto an SD card for immediate use.

Start with a clean install of Raspberry Pi OS. Ensure the OS is up to date before proceeding.

1. Update System Packages
-------------------------
Open a terminal and run:

    sudo apt-get update
    sudo apt-get upgrade -y

2. Install LAMP Stack (Linux, Apache, MariaDB, PHP)
---------------------------------------------------
2.1 Install Apache Web Server:

    sudo apt-get install apache2 -y

2.2 Install MariaDB (MySQL alternative):

    sudo apt-get install mariadb-server -y

2.3 Install PHP and MySQL support:

    sudo apt-get install php php-mysql -y

3. Verify Apache and PHP
------------------------
3.1 Test Apache:

Open a web browser and go to:
    http://localhost
You should see the default Apache web page.

3.2 Test PHP:

Create a test file:

    sudo nano /var/www/html/info.php

Add the following content:

    <?php
    phpinfo();
    ?>

Visit http://localhost/info.php in a browser. You should see the PHP configuration page.

After confirming PHP works, delete the file:

    sudo rm /var/www/html/info.php

4. Secure and Configure MariaDB
-------------------------------
4.1 Run Security Script:

    sudo mysql_secure_installation

Follow the prompts to:
- Set the root password
- Remove anonymous users
- Disallow remote root login
- Remove test database
- Reload privilege tables

4.2 (Optional) Create Database and User:

    sudo mysql -u root -p

At the MySQL prompt:

    CREATE DATABASE lab_kiosk;
    CREATE USER 'lab_user'@'localhost' IDENTIFIED BY 'secure_password';
    GRANT ALL PRIVILEGES ON lab_kiosk.* TO 'lab_user'@'localhost';
    FLUSH PRIVILEGES;
    EXIT;

5. Deploy Front-End and Back-End Code
-------------------------------------
Clone the project repository:

    git clone git@github.com:rmh21940/Project4.git

Copy all web files (e.g., index.php, login.php, etc.) to Apache’s web root:

    sudo cp -r Project4/frontend/* /var/www/html/

Set proper ownership:

    sudo chown -R www-data:www-data /var/www/html

If your project includes SQL schema files for the database, import them:

    mysql -u root -p lab_kiosk < Project4/backend/schema.sql

6. Enable Required Services on Boot
-----------------------------------
    sudo systemctl enable apache2
    sudo systemctl enable mariadb

7. Configure Auto-Launch of Kiosk Browser on Boot
-------------------------------------------------
7.1 Create or edit autostart config:

    nano ~/.config/lxsession/LXDE-pi/autostart

7.2 Add the following line to launch Chromium in kiosk mode:

    @chromium-browser --kiosk --incognito http://localhost/login.php

7.3 Save and reboot:

    sudo reboot

The Raspberry Pi will boot to desktop and launch the login page in full-screen mode.

8. Remove the testData.sql file from the database folder
---------------------------------------------------------
This is the production files the client does not need a testData file as they will be loading their own data to the tables    

Final Step
----------
At this point, the Raspberry Pi is fully configured as a kiosk system and ready for use. You may now capture this setup as a .img file to distribute to the client for flashing onto additional SD cards.
