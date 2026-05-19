<?php
class Phonepay extends CI_Model {
    
    public function initiatePayment($amount, $mobileNumber, $email, $redirectUrl) {
        $merchantId  = 'M142UORGNTEJ'; 
        $apiKey      = "3fa034f6-5feb-4d03-817e-38f59d99f8bb"; 
        if($email == NULL){
            $email = 'user@gmail.com';
        }
        $name        = $this->db_model->select('name', 'tbl_users', array('phone' => $mobileNumber));
        $paymentData = array(
            'merchantId'             => $merchantId,
            'merchantTransactionId'  => rand(111111,9999999), 
            "merchantUserId"         => "MUID".$mobileNumber,
            'amount'                 => $amount * 100,
            'redirectUrl'            => $redirectUrl,
            'redirectMode'           => "POST",
            'callbackUrl'            => $redirectUrl,
            "merchantOrderId"        => rand(0,9),
            "mobileNumber"           => $mobileNumber,
            "message"                => 'Payment Of '.$amount,
            "email"                  => $email,
            "shortName"              => $name ?? 'user',
            "paymentInstrument"      => array("type"=> "PAY_PAGE",)
        );
        $jsonencode     = json_encode($paymentData);
        $payloadMain    = base64_encode($jsonencode);   
        $salt_index     = 1;
        $payload        = $payloadMain . "/pg/v1/pay" . $apiKey;
        $sha256         = hash("sha256", $payload);
        $final_x_header = $sha256 . '###' . $salt_index;
        $request        = json_encode(array('request' => $payloadMain));
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => "https://api.phonepe.com/apis/hermes/pg/v1/pay",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $request,
            CURLOPT_HTTPHEADER     => [
                "Content-Type: application/json",
                 "X-VERIFY: " . $final_x_header,
                 "accept: application/json"
            ],
        ]);
        
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
            return ['success' => false, 'message' => 'cURL Error'];
        } else {
            $res = json_decode($response);
            if (isset($res->success) && $res->success == '1') {
                $paymentCode = $res->code;
                $paymentMsg  = $res->message;
                $payUrl      = $res->data->instrumentResponse->redirectInfo->url;
                header("Location: $payUrl");
                return ['success' => true, 'paymentCode' => $paymentCode, 'paymentMsg' => $paymentMsg, 'payUrl' => $payUrl];
            } else {
                return ['success' => false, 'message' => 'Payment initiation failed'];
            }
        }
    }
}
?>
