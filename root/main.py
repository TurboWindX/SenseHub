import json
import subprocess

#load config file
with open('/root/config.json') as f:
    data = json.load(f)

#check if device is new/resetted
if(data['configmode'] == True):
    #START HOSTAPD, START DNSMASQ
    subprocess.call(['/bin/sh','/root/sense'])
    #EDIT CONFIG.JSON, SETUP COMPLETE
    data['configmode'] = False;
    json.dump(data, open('/root/config.json', "w"), indent=4)

if(data['configmode'] == False):
    subprocess.call(['/usr/bin/systemctl start mysql'], shell=True)
    #START MOSQUITTO-BROKER, START MOSQUITTO-CLIENT
    subprocess.call(['mkdir /var/run/mosquitto/'],shell=True)
    subprocess.call(['chown -R mosquitto:mosquitto /var/run/mosquitto/'],shell=True)
    subprocess.call(['/usr/local/sbin/mosquitto -c /etc/mosquitto/mosquitto.conf'], shell=True)
