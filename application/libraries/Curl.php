<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Curl {

    public function __construct() {
        // Constructor code, if needed
    }

    public function simple_get($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }
}
