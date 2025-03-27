#!/bin/bash
# Raspberry Pi Kiosk Auto-Setup Script v1

# Exit on any error
set -e

echo "Updating system..."
sudo apt-get update
sudo apt-get upgrade -y

echo "Installing Apache..."
sudo apt-get install apache2 -y

echo "Installing MariaDB..."
sudo apt-get install mariadb-server -y

echo "Installing PHP and modules..."
sudo apt-get install php php-mysql -y

echo "Securing MariaDB. Follow the prompts..."
sudo mysql_secure_installation

echo "Cloning project repository..."
git clone git@github.com:rmh21940/Project4.git

echo "Deploying front-end files..."
sudo cp -r Project4/frontend/* /var/www/html/
sudo chown -R www-data:www-data /var/www/html

echo "Importing database schema (if applicable)..."
if [ -f Project4/backend/schema.sql ]; then
    mysql -u root -p lab_kiosk < Project4/backend/schema.sql
fi

echo "Enabling services to start on boot..."
sudo systemctl enable apache2
sudo systemctl enable mariadb

echo "Configuring kiosk auto-start..."
mkdir -p ~/.config/lxsession/LXDE-pi
echo '@chromium-browser --kiosk --incognito http://localhost/login.php' >> ~/.config/lxsession/LXDE-pi/autostart

echo "Setup complete. Rebooting..."
sudo reboot
