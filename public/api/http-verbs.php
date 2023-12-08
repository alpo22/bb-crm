<?php
// Dont call this directly, use one of the functions below
function CallAPI($method, $url, $data)
{
  usleep(210000); //only allowed 5 per second, so make this wait a bit more than 1/5th of a second

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  switch ($method) {
    case 'GET':
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
      break;
    case 'POST':
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
      break;
    case 'PUT':
      // curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
      curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
      break;
    case 'DELETE':
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      break;
  }

  $response = curl_exec($curl);

  if ($response === false) {
    echo 'Curl error: ' . curl_error($curl);
    curl_close($curl);
    die;
  }
  curl_close($curl);
  return json_decode($response);
}

// $response = httpPost('https://emailoctopus.com/api/1.5/lists/' . $listId . '/contacts',
//    ['api_key'        => $emailOctopusApiKey,
//    'email_address' => $company['emailAddress'],
//    'fields'        => $fields,
//    'status'        => $status]
// );
function httpPost($url, $data)
{
  return CallAPI('POST', $url, $data);
}

function httpDelete($url, $data = null)
{
  return CallAPI('DELETE', $url, $data);
}

function httpPut($url, $data = null)
{
  return CallAPI('PUT', $url, $data);
}

function httpGet($url, $data = null)
{
  return CallAPI('GET', $url, $data);
}
