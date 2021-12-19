#!/bin/bash

cp /root/50-cloud-init.yaml /etc/netplan/50-cloud-init.yaml
echo '' > /etc/mosquitto/conf.d/go-auth.conf
