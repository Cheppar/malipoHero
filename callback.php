<?php
// Set the header to ensure the response is in JSON format
header('Content-Type: application/json');

// Fetch the raw POST data sent to the callback URL
$inputData = file_get_contents('php://input');

// Log the raw POST data to a file for debugging purposes
file_put_contents('callback_log.txt', "Received Callback Data: " . $inputData . "\n", FILE_APPEND);

// Check if data was received
if (!empty($inputData)) {
    // Decode the incoming JSON data
    $decodedData = json_decode($inputData, true);

    // Log the decoded data (optional, to verify the structure of the data)
    file_put_contents('callback_log.txt', "Decoded Callback Data: " . print_r($decodedData, true) . "\n", FILE_APPEND);

    // Prepare the data to be inserted into Supabase
    $data = [
        'status' => $decodedData['status'],
        'transaction_type' => $decodedData['response']['Transaction_Type'],
        'source' => $decodedData['response']['Source'],
        'amount' => $decodedData['response']['Amount'],
        'mpesa_reference' => $decodedData['response']['MPESA_Reference'],
        'transaction_reference' => $decodedData['response']['Transaction_Reference'],
        'payment_method' => $decodedData['response']['Payment_Method'],
        'account' => $decodedData['response']['Account'],
        'user_reference' => $decodedData['response']['User_Reference'],
        'transaction_date' => $decodedData['response']['transaction_date'],
        'woocommerce_payment_status' => $decodedData['response']['woocommerce_payment_status'],
        'service_wallet_balance' => $decodedData['response']['ServiceWalletBalance'],
        'payment_wallet_balance' => $decodedData['response']['PaymentWalletBalance'],
        'forward_url' => $decodedData['forward_url'],
    ];

    // Check if this transaction already exists in Supabase based on `transaction_reference`
    $transactionReference = $decodedData['response']['Transaction_Reference'];

    // Supabase API details
    $supabaseUrl = 'replace with supabase URL'; 
    $supabaseApiKey = 'replace with supabase key'; 
    $tableName = 'your_table_name';
    $endpoint = $supabaseUrl . '/rest/v1/' . $tableName . '?transaction_reference=eq.' . $transactionReference;

    // Initialize cURL to check if the record already exists
    $ch = curl_init($endpoint);

    // Set cURL options for GET request
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $supabaseApiKey,
        'apikey: ' . $supabaseApiKey
    ]);

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check if any error occurred with the cURL request
    if (curl_errno($ch)) {
        file_put_contents('callback_log.txt', "Error with cURL request: " . curl_error($ch) . "\n", FILE_APPEND);
    }

    // Decode the response to check if the transaction exists
    $existingData = json_decode($response, true);

    // If no data exists for this transaction, insert new data
    if (empty($existingData)) {
        // Convert the data to JSON for the POST request
        $jsonData = json_encode($data);

        // Supabase API endpoint for inserting data
        $endpoint = $supabaseUrl . '/rest/v1/' . $tableName;

        // Initialize cURL to send data to Supabase
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_POST, true);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Log the response from Supabase (optional)
        file_put_contents('callback_log.txt', "Supabase Response: " . $response . "\n", FILE_APPEND);
    } else {
        // Log if the record already exists
        file_put_contents('callback_log.txt', "Record already exists for transaction reference: " . $transactionReference . "\n", FILE_APPEND);
    }

    // Close cURL
    curl_close($ch);

    // Send a response (you can customize this based on your API's requirements)
    echo json_encode([
        'status' => 'success',
        'message' => 'Data received successfully and saved to Supabase, if not already present.',
        'received_data' => $decodedData
    ]);
} else {
    // Log and return an error if no data was received
    file_put_contents('callback_log.txt', "No data received or empty data.\n", FILE_APPEND);
    
    echo json_encode([
        'status' => 'error',
        'message' => 'No data received. Please send a POST request with data.'
    ]);
}
?>
