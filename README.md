# Miele-MQTT
A very simple script to read data and issue commands via Miele@home cloud services, using Mosquitto MQTT

The first time you run it, you will be asked for Client ID and Client Key. These keys are managed by Miele developers, so you need to send an email to developer@miele.com to retrieve these. According to the license agreement with Miele, these should not be distributed.

This script is now based upon Bluerhinos' project phpMQTT, which can be found here: https://github.com/bluerhinos/phpMQTT
The scriptfile from Bluerhinos, called phpMQTT.php needs to be installed in the same directory as miele-MQTT.php in order for the solution to work.

Additional info you will be asked for on the first run (make sure to have this information handy):

- Username for Miele@home
- Password for Miele@home
- Country code for your Miele@home account
- Base topic to use when publishing Mosquitto data

The script will NOT save your username and password, but it will obtain an autorization code, which will be saved in miele-config.php along with the rest of the data. If you are worried that someone wants to play with your household appliances, make sure to keep this file safe.


To send commands to your appliance, publish mqtt in the form of: <br>
Topic: \<Topic\>/command/\<deviceID\>/\<action\> <br>
Data: \<data\>

Example: <br>
Topic: /miele/command/0010101010/powerOn <br>
Data: true


The script does NOT work on my Windows 10 installation currently, I hope to be able to do something about that soon.

The code written currently covers Miele dishwashers, washing machines and dryers (thanks, Stoffi!). Run the script with parameter "-d" to retrieve all data about your appliances, send it to me, and I can add more appliance-support to the script. -Or you could add to the script through Github (I believe, this is my very first Github project.)

