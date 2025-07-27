<?php
require_once __DIR__ . '/../config/sms_config.php';

class SmsSender {
    private $api_key;
    private $secret_key;
    private $api_url = 'https://api.smsprovider.com/v1/send';

    public function __construct() {
        $this->api_key = getenv('SMS_API_KEY');
        $this->secret_key = getenv('SMS_SECRET_KEY');
    }

    public function sendSms($phone, $message) {
        $data = [
            'recipient' => $this->formatPhone($phone),
            'message' => $message,
            'sender_id' => 'HOSPITAL'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode("$this->api_key:$this->secret_key"),
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            throw new Exception("SMS sending failed. API Response: " . $response);
        }

        return json_decode($response, true);
    }

    private function formatPhone($phone) {
        // Format namba za Tanzania: 0712345678 -> 255712345678
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strpos($phone, '0') === 0) {
            return '255' . substr($phone, 1);
        }
        return $phone;
    }
}
?>