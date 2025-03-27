# Raspberry Pi Kiosk Auto-Setup Script Instructions

This guide explains how to run the `raspberry_pi_kiosk_auto_setup.sh` script to configure your Raspberry Pi (model 4 or higher) as a kiosk for the Virginia Western Community College Cybersecurity Lab.

---

## Pre-Requisites

- A Raspberry Pi with a clean install of Raspberry Pi OS.
- Internet connection.
- Access to the terminal (via desktop or SSH).

---

## Step 1: Transfer the Script to the Raspberry Pi

### a. Using USB

1. Copy the script file to a USB drive.
2. Insert the USB into the Raspberry Pi.
3. Open a terminal and run:

```bash
cp /media/pi/<USB_DRIVE_NAME>/raspberry_pi_kiosk_auto_setup.sh ~/
```

### b. Using SCP from Another Computer

On your other device, run:

```bash
scp raspberry_pi_kiosk_auto_setup.sh pi@<RPI-IP>:/home/pi/
```

---

## Step 2: Make the Script Executable

Open a terminal on the Raspberry Pi and run:

```bash
chmod +x raspberry_pi_kiosk_auto_setup.sh
```

---

## ▶Step 3: Run the Script

Run the script:

```bash
./raspberry_pi_kiosk_auto_setup.sh
```

The script will:
- Update and upgrade your system
- Install Apache, MariaDB, and PHP
- Clone the project repository
- Deploy the front-end code
- Configure the database
- Enable necessary services on boot
- Set Chromium to launch the login page in kiosk mode on boot
- Reboot the Raspberry Pi when done

---

## Optional: Log Script Output

To save the output to a log file for troubleshooting or review:

```bash
./raspberry_pi_kiosk_auto_setup.sh | tee setup_log.txt
```

---

## Post-Run

After the script completes and the Raspberry Pi reboots, it will automatically launch the login page in Chromium. Your kiosk is now ready for use.

You can now capture this SD card as a `.img` file to distribute to clients.
