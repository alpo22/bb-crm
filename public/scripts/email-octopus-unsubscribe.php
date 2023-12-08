<?php
// Update my DB to mark those who unsubscribed as "dontEmail".

die();

require __DIR__ . '/secrets.php';

$myDbLink = mysqli_connect(SECRET_CRM_DB_SERVER, SECRET_CRM_DB_USER, SECRET_CRM_DB_PASS, SECRET_CRM_DB_NAME) or die('Database error: ' . mysqli_error($liveDbLink));

$apiKey = SECRET_EMAIL_OCTOPUS_API_KEY;

$listsJson = file_get_contents('https://emailoctopus.com/api/1.5/lists?api_key=' . $apiKey);
$lists     = json_decode($listsJson);
$lists     = $lists->data;

foreach ($lists as $list) {
  $unsubscribedJson = file_get_contents('https://emailoctopus.com/api/1.5/lists/' . $list->id . '/contacts/unsubscribed?api_key=' . $apiKey);
  $contacts         = json_decode($unsubscribedJson);
  $contacts         = $contacts->data;

  foreach ($contacts as $contact) {
    unsubscribeContact($contact->fields->mysqlDbId);
    echo $contact->fields->mysqlDbId . ', ';
  }
}

echo '<h1>Done</h1>';

function unsubscribeContact($id)
{
  global $myDbLink;

  $q = "UPDATE xxx_import_companies
  SET dontEmail = '1',
  notes = CONCAT('Unsubscribed from EmailOctopus newsletter\n\n', notes)
  WHERE id = '" . $id . "'
  AND dontEmail = 0
  LIMIT 1";

  $myDbLink->query($q);
}
