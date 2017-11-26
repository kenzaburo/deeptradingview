#! /bin/sh
export PGPASSWORD="deeptradingview"
now=$(date '+%Y-%m-%d %H:%M')
prv=$(date '+%Y-%m-%d %H:%M' -d '1 hour ago')
echo "group 1minutes $prv $now"
echo "select bitfinex.history_1h_trade('${prv}','${now}')" | psql -h localhost  -d bfntradingview -U postgres 
