<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');


class Bitfinex_model extends CI_Model
{


    function get_trade_data($start_time, $end_time)
    {

        $url = "https://api.bitfinex.com/v2/trades/tBTCUSD/hist?start=$start_time&&end=$end_time";
        try{
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_URL, $url);
            // curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            $result = curl_exec($curl);
            curl_close($curl);

            // insert into trade table
            foreach (json_decode($result) as $fieds) {
                    
                    $data = array(
                        "id" => $fieds[0],
                        "timestamp" => date('Y/m/d H:i:s', $fieds[1]/1000),
                        "amount" => $fieds[2],
                        "at_price" => $fieds[3]
                    );
                    $query = $this->db->insert(COINMASTER_SCHEMAS . "." . BTC_USD_REAL, $data);
            }
            return "";
        } catch(Exception $e) {
            $result  = array('error_code' => $e->getCode() ,'error_msg' => $e->getMessage() );
        }
    }

    function get_trade_data_v1($start_time, $end_time, $left, $right,$type)
    {
            $limit = 1000;
            $code = "$left$right";
            $left_pair = $left;
            $right_pair = $right;
            $url = "https://api.bitfinex.com/v1/trades/$code?timestamp=$start_time&&limit_trades=$limit";
            try{
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_URL, $url);
                // curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                $result = curl_exec($curl);
                curl_close($curl);


                $result = json_decode($result);


                // check call is okay or not 
                if(!is_array($result)){
                    return array("faled to call query");

                } 

                //
                $sell_amount = 0;
                $sell_price_sum = 0;
                $sell_count = 0;

                $buy_amount = 0; 
                $buy_price_sum = 0;
                $buy_count = 0;
                foreach ( $result as $fieds) {
                        if($fieds->type =='sell'){
                            $sell_amount += $fieds->amount;
                            $sell_count++;
                            $sell_price_sum += $fieds->price;
                        } 

                        if($fieds->type =='buy'){
                            $buy_amount += $fieds->amount;
                            $buy_count++;
                            $buy_price_sum += $fieds->price;
                        }
                }


                $s_time = date('Y/m/d H:i:s', $start_time);
                $e_time = date('Y/m/d H:i:s', $end_time);
                $buy_price_avg = ($buy_count == 0) ? 0: ($buy_price_sum)/($buy_count);
                $sell_price_avg = ($sell_count == 0) ? 0: ($sell_price_sum)/($sell_count);
                $data = array(
                            "type" => $type,
                            "left_pair" => $left_pair,
                            "right_pair" =>$right_pair,
                            "start_time" => $s_time,
                            "end_time" => $e_time,
                            "buy_price_avg" => "$buy_price_avg",
                            "sell_price_avg"=>"$sell_price_avg",
                            "buy_amount" =>$buy_amount,
                            "sell_amount" =>$sell_amount,

                        );

                $this->db->insert(COINMASTER_SCHEMAS . "." . REAL_1M_TRADE, $data);

                
            } catch(Exception $e) {
                $result  = array('error_code' => $e->getCode() ,'error_msg' => $e->getMessage() );
            }

        return array("buy"=>$data1,"sell"=>$data2);
        
    }


    function get_trade_data_xxm($start_time, $end_time, $table_name)
    {

        try{
            $query1 = "select sum(amount) from ".COINMASTER_SCHEMAS.".".BTC_USD_REAL. " where amount<=0 and timestamp > '$start_time' and timestamp <= '$end_time'";
            $query = $this->db->query($query1);
            $sell_total = $query->result()[0]->sum;
           
            $query2 = "select sum(amount) from ".COINMASTER_SCHEMAS.".".BTC_USD_REAL. " where amount>0 and timestamp > '$start_time' and timestamp <= '$end_time'";
            $query = $this->db->query($query2);
            $buy_total = $query->result()[0]->sum;


            $query3 = "select avg(at_price) from ".COINMASTER_SCHEMAS.".".BTC_USD_REAL. " where timestamp > '$start_time' and timestamp <= '$end_time'";
            $query = $this->db->query($query3);
            $price_avg = $query->result()[0]->avg;

            $result  = array(
                    'buy_total' => $buy_total,
                    'sell_total' => $sell_total,
                    'avg_price' => $price_avg,
                    'timestamp' => $end_time
                );

            $query = $this->db->insert(COINMASTER_SCHEMAS . "." . $table_name, $result);

        } catch(Exception $e) {
            $result  = array('error_code' => $e->getCode() ,'error_msg' => $e->getMessage() );
        }
    }


}
