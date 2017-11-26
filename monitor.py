#!/usr/bin/env python
import os
import time
from time import sleep
while 1:
	process_top1= "collectorTop1.py" 
	tmp = os.popen("ps -Af").read()
	if process_top1 not in tmp[:]:
		newprocess="nohup python3  /var/www/html/backend/collectorTop1.py >> /var/www/html/backend/logs/collectorTop1.log &"
		os.system(newprocess)
        process_top11 = "Bfx_Crawler_Top1.py"
	if process_top11 not in tmp[:]:
		newprocess="nohup python3  /var/www/html/backend/Bfx_Crawler_Top1.py >> /var/www/html/backend/logs/Bfx_Crawler_Top1.log &"
		os.system(newprocess)
                 
        process_top2 = "Bfx_Crawler_Top2.py";
	if process_top2 not in tmp[:]:
		newprocess="nohup python3  /var/www/html/backend/Bfx_Crawler_Top2.py >> /var/www/html/backend/logs/Bfx_Crawler_Top2.log &"
		os.system(newprocess)
        #else:
		#print str(time.time())+"---The process is running."
	process_btc = "Bfx_Crawler_Btc.py";
	if process_btc not in tmp[:]:
		newprocess="nohup python3  /var/www/html/backend/Bfx_Crawler_Btc.py >> /var/www/html/backend/logs/Bfx_Crawler_Btc.log &"
		os.system(newprocess)

	sleep(15)
