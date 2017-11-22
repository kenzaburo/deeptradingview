# -*- coding: utf-8 -*-
import websocket
import _thread as thread
import time
import json
import psycopg2
import sys
import pprint
import datetime

#Define our connection string
conn_string = "host='35.198.237.29' dbname='bfntradingview' user='postgres' password='deeptradingview'"

# print the connection string we will use to connect
# print "Connecting to database\n ->%s" % (conn_string)

# get a connection, if a connect cannot be made an exception will be raised here
conn = psycopg2.connect(conn_string)

# conn.cursor will return a cursor object, you can use this cursor to perform queries
cursor = conn.cursor()





FIELDS = ['CHANNEL_ID','CODE','SEQ', 'ID', 'TIMESTAMP', 'PRICE', '±AMOUNT']

def on_message(ws, message):
    response = json.loads(message)
    is_tu = response[1] == 'tu'
    if is_tu:
        value = {x: y for x, y in zip(FIELDS, response)}
        

       
        time = datetime.datetime.fromtimestamp( int(value['TIMESTAMP'])).strftime('%Y-%m-%d %H:%M:%S')
        print(time);
        string_values = "(" + str(value['CHANNEL_ID']) +",'"+value['CODE']+"','"+value['SEQ'].split("-")[1]+"',"+str(value['ID'])+",'"+time+"',"+str(value['PRICE'])+','+str(value['±AMOUNT'])+")"
        # execute our Query
        query = "insert into bitfinex.realtime_trades (channel_id,code,sequence,id,timestamp,price,amount) values " + string_values
        cursor.execute(query)
        conn.commit()

        # retrieve the records from the database
        # records = cursor.fetchall()

        # pprint.pprint(records)
        print("inserted data sucessfully!");



def on_error(ws, error):
    print(error)

def on_close(ws):
    print('### closed ###')

def on_open(ws):
    def thread_run(*args):
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'BTCUSD'}))
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'LTCUSD'}))
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'ETHUSD'}))
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'ETCUSD'}))
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'XMRUSD'}))
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'ZECUSD'}))
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'XRPUSD'}))

        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'ETHBTC'}))
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'LTCBTC'}))
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'ETCBTC'}))
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'XMRBTC'}))
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'ZECBTC'}))
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'XRPBTC'}))

    thread.start_new_thread(thread_run, ())

def run():


    websocket.enableTrace(True)
    ws = websocket.WebSocketApp('wss://api.bitfinex.com/ws',
                              on_message = on_message,
                              on_error = on_error,
                              on_close = on_close)
    ws.on_open = on_open
    ws.run_forever()



run()
