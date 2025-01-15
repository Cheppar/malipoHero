<?php
// Fetch the JSON data sent from React Native
$inputData = file_get_contents('php://input');

// Log the received data (for debugging purposes)
file_put_contents('php_debug.log', "Received Input Data: " . $inputData . "\n", FILE_APPEND);

// Decode the incoming JSON data
$inputData = json_decode($inputData, true);

// Validate the input data
if (!isset($inputData['amount'], $inputData['phone_number'], $inputData['external_reference'])) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input data."]);
    exit;
}

// Extract data
$amount = $inputData['amount'];
$phoneNumber = $inputData['phone_number'];
$externalReference = $inputData['external_reference'];

// Your API credentials
$apiUsername = '4iZr6EreOOqyXgIrJwCK';
$apiPassword = '1b5hz4SMjpIAkKa3qUVEtCGfovmPCYcuzgFilyCQ';

// Create the Basic Auth token
$credentials = base64_encode($apiUsername . ':' . $apiPassword);
$basicAuthToken = 'Basic ' . $credentials;

// Prepare the cURL request
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://backend.payhero.co.ke/api/v2/payments',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode(array(
        "amount" => $amount,
        "phone_number" => $phoneNumber,
        "external_reference" => $externalReference,
        "provider" => "m-pesa",
        "channel_id"=> 1049, 
        "callback_url"=> "https://cheppar.co.ke/cheppar/callback.php",
    )),
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: ' . $basicAuthToken
    ),
));

// Execute the cURL request
$response = curl_exec($curl);

// Log the API response (for debugging purposes)
file_put_contents('php_debug.log', "API Response: " . $response . "\n", FILE_APPEND);

// Check for cURL errors
if (curl_errno($curl)) {
    echo json_encode(["message" => 'cURL Error: ' . curl_error($curl)]);
    curl_close($curl);
    exit;
}

curl_close($curl);

// Respond with the API response
echo $response;
?>
