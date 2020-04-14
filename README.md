# Miele-MQTT
A very simple script to read data and issue commands via Miele@home cloud services, using Mosquitto MQTT

The first time you run it, you will be asked for Client ID and Client Key. These keys are managed by Miele developers, so you need to send an email to developer@miele.com to retrieve these. According to the license agreement with Miele, these should not be distributed.

The script has been designed to be left running in the background, polling data from Miele every 30 seconds.

This script is now based upon Bluerhinos' project phpMQTT, which can be found here: https://github.com/bluerhinos/phpMQTT
The scriptfile from Bluerhinos, called phpMQTT.php needs to be installed in the same directory as miele-MQTT.php in order for the solution to work.

Additional info you will be asked for on the first run (make sure to have this information handy):

- Username for Miele@home
- Password for Miele@home
- Country code for your Miele@home account
- Base topic to use when publishing Mosquitto data

The script will NOT save your Miele Home password, but it will obtain an autorization code, which will be saved in miele-config.php along with the rest of the data. If you are worried that someone wants to play with your household appliances, make sure to keep this file safe.


To send commands to your appliance, publish mqtt in the form of: <br>
Topic: \<Topic\>/command/\<deviceID\>/\<action\> <br>
Data: \<data\>

Example: <br>
Topic: /miele/command/0010101010/powerOn <br>
Data: true

The script has been tested using PHP 7.x on Linux.

The code written currently covers Miele dishwashers, washing machines and dryers (thanks, Stoffi!). Run the script with parameter "-d" to retrieve all data about your appliances, send it to me, and I can add more appliance-support to the script. -Or you could add to the script through Github, and create a Pull Request (PR).

<b>Command line switches</b><br>
The script now support the following command line swithces:<br>
"-s" or "--single": Just query data once, and quit. Using this, sending commands to the appliance will not work.<br>
"-c" or "--create": Create new config file. If you already have an existing config file, default values for everything except passwords will be retreived from the existing config file, sp you will only have to type your password. Using this switch, no data is retreived from Miele@home.<br>
"-j" or "--json": Query data once, and output the data as JSON, in the same format that Miele use.<br>
"-D" or "--debug": Output all debug information while the script is running. <br>
"-d" or "--dump": Dumps the data retreieved from Miele@home in case you have an unsupported appliance, so you can send it to me through "Issues" on Github, and we can add support for additional appliances.<br>


<b>Installation instructions</b><br>
This script has been developed using Ubuntu 18.04, and I have only tested it using this distribution. However, installation should be similar for all Debian-based distributions. For RedHat, I recommend Googling "how to install PHP" for your release. It should be easy, using yum.

In Ubuntu, install PHP and the required PHP librarys with the following command:<br>
 apt-get update && apt-get install -y php-cli php-common php-curl php-json php-readline <br>

Copy this script and phpMQTT.php into a folder where you have write permissions (for the config file). Gather your credentials, security tokens and MQTT information, and run "php ./miele-MQTT.php -c". The config-file has now been created, and you can run the script with whatever parameters you need from here.

