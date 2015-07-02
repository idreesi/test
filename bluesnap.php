<?php
$params = array (
    'bluesnap_password' => 'Gaditek123',
    'bluesnap_sandbox' => 'on',
    'bluesnap_username' => 'API_14351345577531509471083',
    'amount' => '9.99',
    'description' => 'Payment - Invoice #2',
    'clientdetails' => array(
        'firstname' => 'Idrees',
        'lastname' => 'Gaditek',
    ),
    'currency' => 'USD',
    'cardtype' => 'Visa',
    'cardnum' => '4539791001730106',
    'cardexp' => '0418',
    'cccvv' => '411',
);

$response = bluesnap_capture($params);

echo "<pre>* Request Response\n\n< Status: " . $response['status'] . "\n< Raw Data:\n" . htmlentities($response['rawdata'], ENT_COMPAT, 'UTF-8') . "</pre>";
exit;

function bluesnap_capture($params) {

    $xmlToSend = '<card-transaction xmlns="http://ws.plimus.com">
                       <card-transaction-type>AUTH_CAPTURE</card-transaction-type>
                       <recurring-transaction>ECOMMERCE</recurring-transaction>
                       <soft-descriptor>' . substr($params["description"],10) . '</soft-descriptor>
                       <amount>' . $params['amount'] . '</amount>
                       <currency>' . $params['currency'] . '</currency>
                       <card-holder-info>
                          <first-name>' . $params['clientdetails']['firstname'] . '</first-name>
                          <last-name>' . $params['clientdetails']['lastname'] . '</last-name>
                       </card-holder-info>
                       <credit-card>
                          <card-number>' . $params['cardnum'] . '</card-number>
                          <security-code>' . $params['cccvv'] . '</security-code>
                          <card-type>' . $params['cardtype'] . '</card-type>
                          <expiration-month>' . substr($params['cardexp'],0,2) . '</expiration-month>
                          <expiration-year>' . substr(date('Y'),0,2) . substr($params['cardexp'],2,2) . '</expiration-year>
                       </credit-card>
                    </card-transaction>';

    $url = 'services/2/orders/';
    $results = do_curl($url,$xmlToSend,$params);

    return array('status' => 'declined','rawdata' => $results);
}

function do_curl($endPoint,$xmlToSend,$params) {

    /**
     * Initialize handle and set options
     */
    if($params['bluesnap_sandbox']=='on') {
        $baseUrl = 'https://sandbox.bluesnap.com/';
    } else {
        $baseUrl = 'https://ws.bluesnap.com/';
    }

    $url = $baseUrl . $endPoint;
    $gatewayUsername = $params['bluesnap_username'];
    $gatewayPassword = $params['bluesnap_password'];

    $credentials = "$gatewayUsername:$gatewayPassword";
//$credentials = base64_encode($credentials);
    /*echo $url . "\n";
    echo $credentials . "\n";
    echo base64_encode($credentials) . "\n";
    echo $xmlToSend . "\r\n\r\n";
    print_r($params); exit;*/

    $headers = array(
        "Content-Type: application/xml",
        "Authorization: Basic " . base64_encode($credentials)
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch, CURLOPT_USERPWD, $credentials);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlToSend);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    if($params['bluesnap_sandbox']=='on') {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    /**
     * Execute Curl call and display XML response
     */
    $verbose = enable_verbose($ch);
    $response = curl_exec($ch);
    output_verbose($verbose);

    curl_close($ch);
    return $response;
}

function enable_verbose($ch) {
    //Save verbose
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'rw+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    return $verbose;
}

function output_verbose($verbose) {
    //Output verbose
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
    //echo curl_errno($ch);
}
?>