<?php

$url = 'http://ipinfo.io/ip';
$apiKey = 'XXX';
$zoneUUID = 'XXX';

//Retrieve the current IPv4 for the Livebox
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_COOKIESESSION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$ipLivebox = curl_exec($curl);
curl_close($curl);

//Retrieve the current @ value for A record at Gandi
$urlGandi = 'https://dns.api.gandi.net/api/v5/zones/' . $zoneUUID;
$curl2 = curl_init();
curl_setopt($curl2, CURLOPT_URL, $urlGandi . '/records/@/A');
curl_setopt($curl2, CURLOPT_COOKIESESSION, true);
curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl2, CURLOPT_HTTPHEADER, array(
    'X-Api-Key: ' . $apiKey,
));
$jsonGandi = curl_exec($curl2);
curl_close($curl2);
$dnsRecordGandi = json_decode($jsonGandi);
$ipGandi = $dnsRecordGandi->rrset_values[0];

//Check if the IPv4 is the same, otherwise update @ and mx records.
if($ipLivebox !== $ipGandi) {
    
    $postfields = array(
        'rrset_values' => array($ipLivebox),
    );
    
    //Update @ record
    $curl3 = curl_init();
    curl_setopt($curl3, CURLOPT_URL, $urlGandi . '/records/@/A');
    curl_setopt($curl3, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'X-Api-Key: ' . $apiKey,
    ));
    curl_setopt($curl3, CURLOPT_COOKIESESSION, true);
    curl_setopt($curl3, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl3, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl3, CURLOPT_POSTFIELDS, json_encode($postfields));

    $returnARecord = curl_exec($curl3);
    curl_close($curl3);

    if (preg_match('#DNS Record Created#i', $returnARecord)) {
        echo 'Enregistrement A ok.';
    } else {
        echo 'Enregistrement A échoué.';
    }

    //update MX record
    $curl4 = curl_init();
    curl_setopt($curl4, CURLOPT_URL, $urlGandi . '/records/mx/A');
    curl_setopt($curl4, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'X-Api-Key: ' . $apiKey,
    ));
    curl_setopt($curl4, CURLOPT_COOKIESESSION, true);
    curl_setopt($curl4, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl4, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl4, CURLOPT_POSTFIELDS, json_encode($postfields));

    $returnMXRecord = curl_exec($curl4);
    curl_close($curl4);

    if (!preg_match('#DNS Record Created#i', $returnMXRecord)) {
        echo 'Enregistrement MX ok.';
    } else {
        echo 'Enregistrement MX échoué.';
    }
}
