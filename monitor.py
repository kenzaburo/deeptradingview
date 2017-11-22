#!/usr/bin/env python
import os
import time
from time import sleep
while 1:
	process_RtlsLocationServer= "collectorTop1.py" 
	tmp = os.popen("ps -Af").read()
	if process_RtlsLocationServer not in tmp[:]:
		newprocess="nohup python3  /var/www/html/collectorTop1.py > /var/www/html/collectorTop1.log &"
		os.system(newprocess)
	#else:
		#print str(time.time())+"---The process is running."
	
	sleep(15)
