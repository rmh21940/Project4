# Project4
Repository for ITP258 Project 4 Log in System

3/18 - Added HTML shell files(RH)


3/20 - to automate the autoLogout script
On Linux/Unix (Using Cron):
1. Open your crontab editor by running:

        crontab -e

2. Add a new line to run the script at 10:00 pm every day. Make sure to use the full path to your PHP CLI and the script. For example:

        0 22 * * * /usr/bin/php /path/to/Project4/PHP/autoLogout.php

Adjust /usr/bin/php if your PHP binary is located elsewhere, and replace /path/to/Project4/PHP/autoLogout.php with the correct full path.

3. Save the file.