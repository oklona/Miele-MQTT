# Miele-MQTT
A very simple script to read data from Miele@home cloud services, and publish using Mosquitto MQTT

The first time you run it, you will be asked for Client ID and Client Key. These keys are managed by Miele developers, so you need to send an email to developer@miele.com to retrieve these. According to the license agreement with Miele, these should not be distributed.

Additional info you will be asked for on the first run (make sure to have this information handy):

- Username for Miele@home
- Password for Miele@home
- Country code for your Miele@home account
- Location of your mosquitto_pub binary (Normally /usr/bin/mosquitto)
- Base topic to use when publishing Mosquitto data

The script will NOT save your username and password, but it will obtain an autorization code, which will be saved in miele-config.php along with the rest of the data. If you are worriedthat someone wants to play with your household appliances, make sure to keep this file safe.

The script does NOT work on my Windows 10 installation currently, I hope to be able to do something about that soon.

The code written currently ONLY covers Miele dishwashers. Run the script with parameter "-d" to retrieve all data about your appliances, send it to me, and I can add more appliance-support to the script. -Or you could add to the script through Github (I believe, this is my very first Github project.)

