<?php
// Note: The LIVE database is used
//       The LOCAL code is used

require __DIR__ . '/secrets.php';
require __DIR__ . '/../helpers/http-verbs.php';

DEFINE('EMAIL_OCTOPUS_LIST_ID', 'ebe8335f-e12c-11eb-96e5-06b4694bee2a');

$liveDbLink = mysqli_connect(SECRET_CRM_DB_SERVER, SECRET_CRM_DB_USER, SECRET_CRM_DB_PASS, SECRET_CRM_DB_NAME) or die('Database error: ' . mysqli_error($liveDbLink));

switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET':
    if (isset($_GET['id'])) {
      outputRecord($_GET['id']);
    } else {
      outputAllRecords();
    }
    break;
  case 'POST':
    createRecord();
    break;
  case 'PUT':
    updateRecord();
    break;
}

////////////////////

function outputRecord($id)
{
  global $liveDbLink;
  $sql = "SELECT *
          FROM xxx_import_companies
          WHERE id = '" . mysqli_real_escape_string($liveDbLink, $id) . "'";
  $records = $liveDbLink->query($sql);
  $row     = $records->fetch_assoc();
  header('Content-type: application/json');
  header('HTTP/1.1 200 Success', true, 200);
  echo json_encode($row);
}

function outputAllRecords()
{
  global $liveDbLink;
  $sql = 'SELECT *
          FROM xxx_import_companies
          WHERE isCandidate = 1';
  if (isset($_GET['searchString'])) {
    $sql .= " AND url LIKE '%" . $_GET['searchString'] . "%' ";
  }
  if (isset($_GET['searchProvince'])) {
    $sql .= " AND province = '" . $_GET['searchProvince'] . "' ";
  }
  $sql .= ' ORDER BY url ASC';
  $companies = [];
  $records   = $liveDbLink->query($sql);
  while ($row = $records->fetch_assoc()) {
    array_push($companies, $row);
  }
  header('Content-type: application/json');
  header('HTTP/1.1 200 Success', true, 200);
  echo json_encode($companies);
}

function createRecord()
{
  global $liveDbLink;
  $_POST = json_decode(file_get_contents('php://input'), true);
  if (isset(
    $_POST['url'],
    $_POST['phoneNumber'],
    $_POST['emailAddress'],
    $_POST['firstName'],
    $_POST['lastName'],
    $_POST['streetAddress'],
    $_POST['city'],
    $_POST['postalCode'],
    $_POST['province'],
    $_POST['isCandidate'],
    $_POST['isTemplate'],
    $_POST['isInactive'],
    $_POST['dontEmail'],
    $_POST['notes']
  )) {
    $sql           = "SELECT * FROM xxx_import_companies WHERE url = '" . $_POST['url'] . "'";
    $res           = $liveDbLink->query($sql);
    $existing_rows = $res->fetch_assoc();
    if ($existing_rows === null) {
      $sql = "INSERT INTO xxx_import_companies ( url, phoneNumber, emailAddress, firstName, lastName,
            streetAddress, city, postalCode, province, isCandidate, dontEmail, notes, isTemplate, isInactive, lastSaved,
            searchCity, searchTerm, emailOctopusContactId, emailOctopusListId, emailedDate, keywordScore, phonedDate)
            VALUES ('"
        . mysqli_real_escape_string($liveDbLink, $_POST['url']) . "', '"
        . mysqli_real_escape_string($liveDbLink, $_POST['phoneNumber']) . "', '"
        . mysqli_real_escape_string($liveDbLink, $_POST['emailAddress']) . "', '"
        . mysqli_real_escape_string($liveDbLink, $_POST['firstName']) . "', '"
        . mysqli_real_escape_string($liveDbLink, $_POST['lastName']) . "', '"
        . mysqli_real_escape_string($liveDbLink, $_POST['streetAddress']) . "', '"
        . mysqli_real_escape_string($liveDbLink, $_POST['city']) . "', '"
        . mysqli_real_escape_string($liveDbLink, $_POST['postalCode']) . "', '"
        . mysqli_real_escape_string($liveDbLink, $_POST['province']) . "', '"
        . mysqli_real_escape_string($liveDbLink, $_POST['isCandidate']) . "', '"
        . mysqli_real_escape_string($liveDbLink, $_POST['dontEmail']) . "', '"
        . mysqli_real_escape_string($liveDbLink, $_POST['notes']) . "', '"
        . mysqli_real_escape_string($liveDbLink, $_POST['isTemplate']) . "', '"
        . mysqli_real_escape_string($liveDbLink, $_POST['isInactive']) . "', NOW(), '', '', '', '', null, 10, '0000-00-00 00:00:00')";
      $res = $liveDbLink->query($sql);
      addToEmailOctopus($liveDbLink->insert_id);
      header('Content-type: application/json');
      header('HTTP/1.1 200 success', true, 200);
      echo json_encode('');
    } else {
      header('Content-type: application/json');
      header('HTTP/1.1 200 success', true, 200);
      echo json_encode('duplicated');
    }
  }
}

function updateRecord()
{
  global $liveDbLink;
  $_PUT = json_decode(file_get_contents('php://input'), true);
  if (isset(
    $_PUT['id'],
    $_PUT['url'],
    $_PUT['phoneNumber'],
    $_PUT['emailAddress'],
    $_PUT['firstName'],
    $_PUT['lastName'],
    $_PUT['streetAddress'],
    $_PUT['city'],
    $_PUT['postalCode'],
    $_PUT['province'],
    $_PUT['isCandidate'],
    $_PUT['isTemplate'],
    $_PUT['isInactive'],
    $_PUT['dontEmail'],
    $_PUT['notes']
  )) {
    updateEmailOctopusAsNecessary($_PUT); // have to do this before update database, as it checks if PUT'd data differs from db
    $sql = "UPDATE xxx_import_companies
            SET url = '" . mysqli_real_escape_string($liveDbLink, $_PUT['url']) . "',
              phoneNumber = '" . mysqli_real_escape_string($liveDbLink, $_PUT['phoneNumber']) . "',
              emailAddress = '" . mysqli_real_escape_string($liveDbLink, $_PUT['emailAddress']) . "',
              firstName = '" . mysqli_real_escape_string($liveDbLink, $_PUT['firstName']) . "',
              lastName = '" . mysqli_real_escape_string($liveDbLink, $_PUT['lastName']) . "',
              streetAddress = '" . mysqli_real_escape_string($liveDbLink, $_PUT['streetAddress']) . "',
              city = '" . mysqli_real_escape_string($liveDbLink, $_PUT['city']) . "',
              postalCode = '" . mysqli_real_escape_string($liveDbLink, $_PUT['postalCode']) . "',
              province = '" . mysqli_real_escape_string($liveDbLink, $_PUT['province']) . "',
              isCandidate = '" . mysqli_real_escape_string($liveDbLink, $_PUT['isCandidate']) . "',
              dontEmail = '" . mysqli_real_escape_string($liveDbLink, $_PUT['dontEmail']) . "',
              notes = '" . mysqli_real_escape_string($liveDbLink, $_PUT['notes']) . "',
              isTemplate = '" . mysqli_real_escape_string($liveDbLink, $_PUT['isTemplate']) . "',
              isInactive = '" . mysqli_real_escape_string($liveDbLink, $_PUT['isInactive']) . "',
              lastSaved = NOW()
            WHERE id ='" . mysqli_real_escape_string($liveDbLink, $_PUT['id']) . "'";
    $liveDbLink->query($sql);
    header('Content-type: application/json');
    header('HTTP/1.1 200 Success', true, 200);
    echo json_encode('');
  }
}

function addToEmailOctopus($id)
{
  $emailOctopusContactId = addContactToList($id, $_POST['emailAddress'], $_POST['firstName'], $_POST['lastName'], $_POST['province'], $_POST['url']);
  updateEmailOctopusDataLocally($id, $emailOctopusContactId);
}

/*
1. load the previous record
2. if they are on the email octopus list
7.   if changed isInactive to true or "Do not email" to true OR "is candidate" to false or "is template" to true:
unsubscribe them
8.   if changed isInactive to false or "Do not email" to false OR "is candidate" to true or "is template" to false:
resubscribe them
9.   if changed firstName, lastName or emailAddress
10.     update emailoctopus
11. else if they should be on the list
12    add them to it
 */
function updateEmailOctopusAsNecessary($_PUT)
{
  global $liveDbLink;
  // 1.
  $sql = "SELECT * FROM xxx_import_companies WHERE id = '" . mysqli_real_escape_string($liveDbLink, $_PUT['id']) . "'";
  $companyQuery = $liveDbLink->query($sql);
  $company      = $companyQuery->fetch_assoc();
  $emailOctopusListId    = $company['emailOctopusListId'];
  $emailOctopusContactId = $company['emailOctopusContactId'];
  // 2.
  if ($emailOctopusListId != '') {
    // 7.
    if (($company['isInactive'] != $_PUT['isInactive'] && $_PUT['isInactive']) ||
      ($company['dontEmail'] != $_PUT['dontEmail'] && $_PUT['dontEmail']) ||
      ($company['isCandidate'] != $_PUT['isCandidate'] && !$_PUT['isCandidate']) ||
      ($company['isTemplate'] != $_PUT['isTemplate'] && $_PUT['isTemplate'])
    ) {
      unsubscribeContactFromList($emailOctopusListId, $emailOctopusContactId);
    }
    // 7a.
    if (($company['isInactive'] != $_PUT['isInactive'] && !$_PUT['isInactive']) ||
      ($company['dontEmail'] != $_PUT['dontEmail'] && !$_PUT['dontEmail']) ||
      ($company['isCandidate'] != $_PUT['isCandidate'] && $_PUT['isCandidate']) ||
      ($company['isTemplate'] != $_PUT['isTemplate'] && !$_PUT['isTemplate'])
    ) {
      resubscribeEmailOctopusContact($emailOctopusListId, $emailOctopusContactId);
    }
    // 9.
    if (($company['firstName'] != $_PUT['firstName']) ||
      ($company['lastName'] != $_PUT['lastName']) ||
      ($company['emailAddress'] != $_PUT['emailAddress'])
    ) {
      updateEmailOctopusContact($emailOctopusListId, $emailOctopusContactId, $_PUT['emailAddress'], $_PUT['firstName'], $_PUT['lastName'], $_PUT['url']);
    }
  } else {
    //11.
    if (!$_PUT['isInactive'] && !$_PUT['dontEmail'] && $_PUT['isCandidate'] && $_PUT['isTemplate']) {
      //12.
      $emailOctopusContactId = addContactToList(EMAIL_OCTOPUS_LIST_ID, $_PUT['id'], $_PUT['emailAddress'], $_PUT['firstName'], $_PUT['lastName'], $_PUT['province'], $_PUT['url']);
      updateEmailOctopusDataLocally($_PUT['id'], EMAIL_OCTOPUS_LIST_ID, $emailOctopusContactId);
    }
  }
}

function unsubscribeContactFromList($emailOctopusListId, $emailOctopusContactId)
{
  $data     = ['api_key' => SECRET_EMAIL_OCTOPUS_API_KEY, 'status' => 'UNSUBSCRIBED'];
  $response = httpPut('https://emailoctopus.com/api/1.5/lists/' . $emailOctopusListId . '/contacts/' . $emailOctopusContactId, $data);
}

// @returns new emailOctopusContactId
function addContactToList($id, $emailAddress, $firstName, $lastName, $province, $url)
{
  $fields = [
    'FirstName' => $firstName,
    'LastName'  => $lastName,
    'province'  => $province,
    'mysqlDbId' => $id,
    'idSquared' => $id * $id,
    'url'       => $url,
  ];
  $data = [
    'api_key' => SECRET_EMAIL_OCTOPUS_API_KEY,
    'email_address'    => $emailAddress,
    'fields'           => $fields,
    'status'           => 'SUBSCRIBED'
  ];
  $response = httpPost('https://emailoctopus.com/api/1.5/lists/' . EMAIL_OCTOPUS_LIST_ID . '/contacts', $data);
  if (property_exists($response, 'id')) {
    return $response->id;
  }
  return 0; // some error. probably an invalid email address
}

function updateEmailOctopusDataLocally($id, $emailOctopusContactId)
{
  global $liveDbLink;
  $sql = "UPDATE xxx_import_companies
            SET emailOctopusListId = '" . EMAIL_OCTOPUS_LIST_ID . "',
              emailOctopusContactId = '" . mysqli_real_escape_string($liveDbLink, $emailOctopusContactId) . "'
            WHERE id ='" . mysqli_real_escape_string($liveDbLink, $id) . "'";
  $liveDbLink->query($sql);
}

function unsubscribeFromEmailOctopus($emailOctopusContactId)
{
  $data = ['api_key' => SECRET_EMAIL_OCTOPUS_API_KEY, 'status' => 'UNSUBSCRIBED'];
  httpPut('https://emailoctopus.com/api/1.5/lists/' . EMAIL_OCTOPUS_LIST_ID . '/contacts/' . $emailOctopusContactId, $data);
}

function updateEmailOctopusContact($emailOctopusListId, $emailOctopusContactId, $emailAddress, $firstName, $lastName, $url)
{
  $originalData = httpGet('https://emailoctopus.com/api/1.5/lists/' . $emailOctopusListId . '/contacts/' . $emailOctopusContactId . '?api_key=' . SECRET_EMAIL_OCTOPUS_API_KEY);
  $fields = [
    'FirstName' => $firstName,
    'LastName'  => $lastName,
    'url'       => $url,
  ];
  $data = [
    'api_key' => SECRET_EMAIL_OCTOPUS_API_KEY,
    'email_address'    => $emailAddress,
    'fields'           => $fields,
    'status'           => $originalData->status
  ];
  httpPut('https://emailoctopus.com/api/1.5/lists/' . $emailOctopusListId . '/contacts/' . $emailOctopusContactId, $data);
}

function resubscribeEmailOctopusContact($emailOctopusListId, $emailOctopusContactId)
{
  $data = ['api_key' => SECRET_EMAIL_OCTOPUS_API_KEY, 'status' => 'SUBSCRIBED'];
  httpPut('https://emailoctopus.com/api/1.5/lists/' . $emailOctopusListId . '/contacts/' . $emailOctopusContactId, $data);
}
