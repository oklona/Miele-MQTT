# Miele-MQTT
A very simple script to read data and issue commands via Miele@home cloud services, using Mosquitto MQTT

<b>Upgrading from a pre-3.x version</b>
If you have run the script previously, and just updated to the new version, you need to create a new config file. In order to do that, run the script with the parameter "-c" once. It is not necessary to rename/delete the old config file, as the old file, if it exists, will be used to read out default values.

<b>Before first time use</b>
The first time you run it, you will be asked for Client ID and Client Key. These keys are managed by Miele developers, so you need to send an email to developer@miele.com to retrieve these. According to the license agreement with Miele, these should not be distributed.

You will also be asked for how many days before expiry to refresh token. I chose a default of 5 days, but normally 1 day is probalby sufficient. This means that 'this' number of days before your access token expires, the script will use the refresh token to request a new access token (and refresh token at the same time).

Before using the script, you need to confirm for Miele that you want to allow the script to read and access your data. This cannot be done through the script, so you need to follow this procedure:

- Go to https://www.miele.com/developer/swagger-ui/swagger.html
- Click Authorize
- Use the second form with your client ID and secret
- On the same site trigger an API call (i.e. "devices"), and authorize the client ID/app to use the API. Use UserName/Password if you're asked for it.


The script has been designed to be left running in the background, polling data from Miele every 30 seconds.

This script is now based upon Bluerhinos' project phpMQTT, which can be found here: https://github.com/bluerhinos/phpMQTT
The scriptfile from Bluerhinos, called phpMQTT.php needs to be installed in the same directory as miele-MQTT.php in order for the solution to work.

Additional info you will be asked for on the first run (make sure to have this information handy):

- Username for Miele@home
- Password for Miele@home
- Country code for your Miele@home account
- Base topic to use when publishing Mosquitto data

The script will NOT save your Miele Home password, but it will obtain an autorization code, which will be saved in miele-config.php along with the rest of the data. If you are worried that someone wants to play with your household appliances, make sure to keep this file safe.

<b>Send commands </b>
To send commands to your appliance, publish mqtt in the form of: <br>
Topic: \<Topic\>/command/\<deviceID\>/\<action\> <br>
Data: \<data\>

Example: <br>
Topic: /miele/command/0010101010/powerOn <br>
Data: true

The script has been tested using PHP 7.x on Linux.

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

