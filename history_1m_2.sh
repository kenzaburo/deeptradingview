#! /bin/sh
export PGPASSWORD="deeptradingview"
now=$(date '+%Y-%m-%d %H:%M')
prv=$(date '+%Y-%m-%d %H:%M' -d '1 min ago')
echo "group 1minutes $prv $now"
echo "select tradehistory.history_1m_trade('${prv}','${now}')" | psql -h localhost  -d bfntradingview -U postgres 
