#!/bin/bash

apt-get update
apt-get install hostapd dnsmasq apache2 mosquitto php mariadb-server ufw python libwebsockets15 libwebsockets-dev libc-ares2 libc-ares-dev openssl uuid uuid-dev git make golang-go libcjson1 libcjson-dev
export PATH=$PATH:/usr/local/go/bin:~/go/bin

git clone https://github.com/TurboWindX/SenseAll

ufw allow 80
ufw allow 3306
ufw allow 53
ufw allow 67
ufw allow 1883
ufw enable

mkdir mosquit
cd mosquit
wget http://mosquitto.org/files/source/mosquitto-2.0.9.tar.gz
tar xzvf mosquitto-2.0.9.tar.gz
cd mosquitto-2.0.9
make
sudo make install
sudo groupadd mosquitto
sudo useradd -s /sbin/nologin mosquitto -g mosquitto -d /var/lib/mosquitto
sudo mkdir -p /var/log/mosquitto/ /var/lib/mosquitto/ /var/run/mosquitto/
sudo chown mosquitto:mosquitto /var/run/mosquitto
sudo chown -R mosquitto:mosquitto /var/log/mosquitto/
sudo chown -R mosquitto:mosquitto /var/lib/mosquitto/


mkdir goauth
cd goauth
git clone https://github.com/iegomez/mosquitto-go-auth
cd mosqsuitto-go-auth
make
cp ./go-auth.so /etc/mosquitto/conf.d/go-auth.so

sudo echo -e "interface=wlan0\nhw_mode=g\ncountry_code=US\nchannel=7\nht_capab=[HT40][SHORT-GI-20][DSSS_CCK-40]\nwmm_enabled=1\nmacaddr_acl=0\nauth_algs=1\nignore_broadcast_ssid=0\nwpa=2\nwpa_key_mgmt=WPA-PSK\nrsn_pairwise=CCMP\nssid=SenseHub\nwpa_passphrase=SensePass\nieee80211n=1\nwme_enabled=1" > /etc/hostapd.conf
sudo echo -e "no-resolv\ninterface=wlan0\ndhcp-range=192.168.69.11,192.168.69.40,24h\ndhcp-option=1,255.255.255.0\ndhcp-option=3,192.168.69.1\ndhcp-option=6,192.168.69.1\nserver=8.8.8.8\nlisten-address=127.0.0.1\nbind-dynamic" > /etc/dnsmasq.conf
