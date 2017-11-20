import websocket
import _thread as thread
import time
import json

FIELDS = ['CHANNEL_ID','CODE','SEQ', 'ID', 'TIMESTAMP', 'PRICE', '±AMOUNT']

def on_message(ws, message):
    response = json.loads(message)
    is_tu = response[1] == 'tu'
    if is_tu:
        value = {x: y for x, y in zip(FIELDS, response)}
        print('trades', value)

def on_error(ws, error):
    print(error)

def on_close(ws):
    print('### closed ###')

def on_open(ws):
    def run(*args):
        ws.send(json.JSONEncoder().encode({'event': 'subscribe', 'channel': 'trades', 'pair': 'BTCUSD'}))
    thread.start_new_thread(run, ())


def run():
    websocket.enableTrace(True)
    ws = websocket.WebSocketApp('wss://api.bitfinex.com/ws',
                              on_message = on_message,
                              on_error = on_error,
                              on_close = on_close)
    ws.on_open = on_open
    ws.run_forever()



run()
