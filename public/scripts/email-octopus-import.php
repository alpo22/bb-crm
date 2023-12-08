<?php
// Get contacts from EmailOctopus and add their ListID and ID into my DB.

die();

require __DIR__ . '/secrets.php';

$myDbLink = mysqli_connect(SECRET_CRM_DB_SERVER, SECRET_CRM_DB_USER, SECRET_CRM_DB_PASS, SECRET_CRM_DB_NAME) or die('Database error: ' . mysqli_error($liveDbLink));

$apiKey         = SECRET_EMAIL_OCTOPUS_API_KEY;
$numberToLookAt = 9999;
$currentNumber  = 0;

$listsJson = file_get_contents('https://emailoctopus.com/api/1.5/lists?api_key=' . $apiKey);
$lists     = json_decode($listsJson);
$lists     = $lists->data;

foreach ($lists as $list) {
  echo '<b>' . $list->id . ', ' . $list->name . '</b><br>' . PHP_EOL;

  //get all the people in that list
  $listContactsJson = file_get_contents('https://emailoctopus.com/api/1.5/lists/' . $list->id . '/contacts?api_key=' . $apiKey . '&limit=500');
  $contacts         = json_decode($listContactsJson);
  $contacts         = $contacts->data;

  foreach ($contacts as $contact) {
    $customerId = sqrt($contact->fields->idSquared);
    $success    = setEmailOctopusData($customerId, $contact->email_address, $list->id, $contact->id);

    if ($success) {
      echo 'Did ' . $customerId . '<br />';
    } else {
      echo 'Did not update ' . $customerId . '(' . $contact->email_address . ') to be on list ' . $list->id . ' with customerId ' . $contact->id . '<br />';
    }

    $currentNumber++;
  }
  echo count($contacts) . '<br />';

  if ($currentNumber == $numberToLookAt) {
    die;
  }
  echo '<br />';
}

echo 'Done ' . $currentNumber;

//returns true if updated one, false if did not
function setEmailOctopusData($id, $emailAddress, $emailOctopusListId, $emailOctopusContactId)
{
  global $myDbLink;

  $q = "UPDATE xxx_import_companies
  SET emailOctopusListId = '" . $emailOctopusListId . "',
  emailOctopusContactId = '" . $emailOctopusContactId . "'
  WHERE id = '" . $id . "'
  AND emailAddress = '" . $emailAddress . "'
  LIMIT 1";

  $myDbLink->query($q);

  return mysqli_affected_rows($myDbLink) === 1;
}
