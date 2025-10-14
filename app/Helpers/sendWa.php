<?php

if (!function_exists('testWa')) {
    function testWa(){
            $curl = curl_init();
            $token = env('WHATSAPP_API_KEY');
            $secret_key = env('WHATSAPP_WEBHOOK_SECRET');
            $data = [
            'phone' => '6288228815362',
            'message' => 'hello there',
            ];
            curl_setopt($curl, CURLOPT_HTTPHEADER,
                array(
                    "Authorization: $token.$secret_key",
                )
            );
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($curl, CURLOPT_URL,  "https://pati.wablas.com/api/send-message");
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $result = curl_exec($curl);
            curl_close($curl);
            echo "<pre>";
            print_r($result);
        }
}