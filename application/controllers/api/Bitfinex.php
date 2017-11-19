<?php
/**
 * Created by PhpStorm.
 * User: trunghuynh
 * Date: 25/10/16
 * Time: 11:27 AM
 */

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class Bitfinex extends REST_Controller
{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->library("session");
        $this->load->database();
        $this->load->helper('url');
    }

    public function trade_get()
    {
        $this->load->model("bitfinex_model");
        $left = $this->get('left');
        $right = $this->get('right');
        $type = $this->get('type');

        //End time is current time of request
        $end_time = round(microtime(true));
        //Start time  is current time - 10 second
        $start_time = $end_time - 60;

        $data = $this->bitfinex_model->get_trade_data_v1($start_time, $end_time, $left, $right,$type);
        $message = [
            'status' => TRUE,
            'data' => $data
        ];

        $this->set_response($message, REST_Controller::HTTP_BAD_REQUEST);
    }


    public function get_time($interval) {
        //End time is current time of request
        $end_time = date('Y-m-d H:i:s');
        //Start time  is current time - 5 mintues
        $start_time = strtotime($end_time ) - $interval * 50;
        $start_time = date('Y-m-d H:i:s',$start_time);

        return array('start_time' =>$start_time ,'end_time' => $end_time );
    }

    public function trade_60m_get()
    {
        $this->load->model("bitfinex_model");
        $result = $this->get_time(15);

        $start_time = $result['start_time'];
        $end_time = $result['end_time'];


        $data = $this->bitfinex_model->get_trade_data_xxm($start_time, $end_time,BTC_USD_60M);
        $message = [
            'status' => TRUE,
            'data' => $data
        ];

        $this->set_response($message, REST_Controller::HTTP_BAD_REQUEST);
    }


    public function trade_30m_get()
    {
        $this->load->model("bitfinex_model");
        $result = $this->get_time(15);

        $start_time = $result['start_time'];
        $end_time = $result['end_time'];


        $data = $this->bitfinex_model->get_trade_data_xxm($start_time, $end_time,BTC_USD_30M);
        $message = [
            'status' => TRUE,
            'data' => $data
        ];

        $this->set_response($message, REST_Controller::HTTP_BAD_REQUEST);
    }

    public function trade_15m_get()
    {
        $this->load->model("bitfinex_model");
        $result = $this->get_time(15);

        $start_time = $result['start_time'];
        $end_time = $result['end_time'];


        $data = $this->bitfinex_model->get_trade_data_xxm($start_time, $end_time,BTC_USD_15M);
        $message = [
            'status' => TRUE,
            'data' => $data
        ];

        $this->set_response($message, REST_Controller::HTTP_BAD_REQUEST);
    }

    public function trade_5m_get()
    {
        $this->load->model("bitfinex_model");
        $result = $this->get_time(5);

        $start_time = $result['start_time'];
        $end_time = $result['end_time'];


        $data = $this->bitfinex_model->get_trade_data_xxm($start_time, $end_time,BTC_USD_5M);
        $message = [
            'status' => TRUE,
            'data' => $data
        ];

        $this->set_response($message, REST_Controller::HTTP_BAD_REQUEST);
    }
}