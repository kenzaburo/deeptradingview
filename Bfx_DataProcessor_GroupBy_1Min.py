import psycopg2
import urllib
import json, requests
import calendar
from datetime import datetime, timedelta
import time
import random

btc_tradingpairs = ['btcusd']
top1_tradingpairs = ['ethusd', 'ethbtc',
'etcusd', 'etcbtc',
'ltcusd', 'ltcbtc',
'xmrusd', 'xmrbtc',
'dshusd', 'dshbtc',
'zecusd', 'zecbtc',
'xrpusd', 'xrpbtc']
top2_tradingpairs = ['bchusd', 'bchbcc', 'bcheth',
'omgusd', 'omgbtc', 'omgeth',
'iotusd', 'iotbtc', 'ioteth',
'sanusd', 'sanbtc', 'saneth',
'neousd', 'neobtc', 'neoeth',
'eosusd', 'eosbtc', 'eoseth',
'etpusd', 'etpbtc', 'etpeth',
'qtmusd', 'qtmbtc', 'qtmeth',
'avtusd', 'avtbtc', 'avteth',
'edousd', 'edobtc', 'edoeth',
'datusd', 'datbtc', 'dateth',
'btgusd', 'btgbtc', 'btgeth']


def GetHourFromTimestamp(timestamp):
    return int(datetime.fromtimestamp(timestamp).strftime('%H'))

def GetMinuteFromTimestamp(timestamp):
    return int(datetime.fromtimestamp(timestamp).strftime('%M'))

def GetCurrentDateTimeUTC():
    return datetime.now().replace(second=0, microsecond =0)

def GetTimeStampFromDateTimeUTC(currentDateTime):
    return int(currentDateTime.timestamp())


def RequestTradeRealtime(typeid, start, end, tPairList):
    start_time = datetime.fromtimestamp(start).strftime('%Y-%m-%d %H:%M:%S')
    end_time = datetime.fromtimestamp(end).strftime('%Y-%m-%d %H:%M:%S')
    global id

    for pair in tPairList:
        query ="SELECT * FROM tradehistory.trade_realtime WHERE seq = '%s' AND timestamp >= %s AND timestamp < %s" %(pair.upper(),start,end)
        cursor.execute(query)
        data = cursor.fetchall()
        total_buy = 0
        total_sell = 0
        total_buy_price =0
        total_sell_price =0
        buy_price_avg = 0
        sell_price_avg = 0

        for line in data:
            amount = float(line[5])
            price = float(line[4])
            tid = int(line[2])
            if(amount < 0):
                amount = amount*(-1)
                total_sell += amount
                total_sell_price += amount*price
            else:
                total_buy += amount
                total_buy_price += amount*price
            deletequery = "DELETE FROM tradehistory.trade_realtime WHERE id = %s" %(tid)
            cursor.execute(deletequery)
            conn.commit()
        id += 1   
        if(total_buy == 0):
            buy_price_avg = 0
        else:
            buy_price_avg = total_buy_price/total_buy

        if(total_sell ==0):
            sell_price_avg = 0
        else:
            sell_price_avg = total_sell_price/total_sell
        
        insertquery = "INSERT INTO tradehistory.trade_1min (id, type, left_pair, right_pair, start_time, end_time, sell_amount, buy_amount,sell_price_avg, buy_price_avg) VALUES (%s, %s, %s,%s, %s,%s,%s,%s,%s,%s);"
        inserttuple = (id,typeid,pair[0:-3],pair[3:],start_time,end_time,total_sell,total_buy,sell_price_avg,buy_price_avg)
        cursor.execute(insertquery,inserttuple)
        print(insertquery % inserttuple)
        conn.commit()
            
    


conn_string = "host='localhost' dbname='bfntradingview' user='postgres' password='deeptradingview'"

print("Connecting to database\n	->%s" % (conn_string))
    
conn = psycopg2.connect(conn_string)

cursor = conn.cursor()
print ("Connected!")

id = 0
currentDateTime = GetCurrentDateTimeUTC()
lastRequestTime = currentDateTime + timedelta(minutes=-1)

while(1):
    currentDateTime = GetCurrentDateTimeUTC()
    if(lastRequestTime < currentDateTime):
        start = GetTimeStampFromDateTimeUTC(lastRequestTime)
        end = GetTimeStampFromDateTimeUTC(lastRequestTime + timedelta(minutes=1))
        
        RequestTradeRealtime(0,start, end ,btc_tradingpairs)
        RequestTradeRealtime(1,start, end ,top1_tradingpairs)
        RequestTradeRealtime(2,start, end ,top2_tradingpairs)
        
        lastRequestTime = lastRequestTime + timedelta(minutes=1)   

