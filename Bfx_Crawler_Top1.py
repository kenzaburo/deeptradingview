import websocket
import psycopg2
import _thread as thread
import time
import json

top1_tradingpairs = ['ethusd', 'ethbtc',
'etcusd', 'etcbtc',
'ltcusd', 'ltcbtc',
'xmrusd', 'xmrbtc',
'dshusd', 'dshbtc',
'zecusd', 'zecbtc',
'xrpusd', 'xrpbtc']

def on_message(ws, message):
    try:
        response = json.loads(message)
        is_tu = response[1] == 'tu'
        if is_tu:
            query = "INSERT INTO tradehistory.trade_realtime (channel_id, seq, id, timestamp,price,amount) VALUES (%s, '%s', %s,%s,%s,%s)" % (int(response[0]),str(response[2][-6:]),int(response[3]),int(response[4]),float(response[5]),float(response[6]))
            print(query)
            cursor.execute(query)
            conn.commit()
    except Exception as e:
        print('Error from Exception')
        print(e)
        pass
def on_error(ws, error):
    print('Error from on_error')
    print(error)

def on_close(ws):
    print('### closed ###')

def on_open(ws):
    def run(pair):
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': pair}))
        
    for pair in top1_tradingpairs:
        thread.start_new_thread(run, (pair,))
   



def run():
    websocket.enableTrace(True)
    ws = websocket.WebSocketApp('wss://api.bitfinex.com/ws',
                              on_message = on_message,
                              on_error = on_error,
                              on_close = on_close)
    ws.on_open = on_open
    ws.run_forever()

#Open Connection to the Server
conn_string = "host='35.198.237.29' dbname='bfntradingview' user='postgres' password='deeptradingview'"

print("Connecting to database\n	->%s" % (conn_string))
conn = psycopg2.connect(conn_string)

cursor = conn.cursor()
print ("Connected!")

run()