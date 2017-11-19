<?php
/**
 * Created by PhpStorm.
 * User: vnmnguyen
 * Date: 5/12/14
 * Time: 10:33 AM
 */
date_default_timezone_set("Singapore");
if (!isset($_SESSION))
{
    session_start();
}
define("mailerscripturl", "http://athena.smu.edu.sg/~vignesh/mailerscript.php");


function mailer($from, $to, $subject, $message)
{
    //Let this remain hardcoded. Do NOT move to INI file.
    $secret = "UPqHPVAA7RV3eGZ1CySqNXtXL7IBs3STVPr5ELmSs8rAFu2NJqmeDnqIYAj1OM1Z16dl7FZasze1LUwkZ7OtOlyEPgCJuG6TCY3x";
    $postdata = http_build_query(
        array
        (
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
            'secret' => $secret,
        )
    );

    $opts = array('http' =>
        array
        (
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );

    $context = stream_context_create($opts);
    $result = file_get_contents(mailerscripturl, false, $context);
    return json_decode($result);
}

function baseUrl()
{
    return $_SERVER["SERVER_NAME"].substr($_SERVER["PHP_SELF"],0, strrpos($_SERVER["PHP_SELF"],"/")+1);
}

