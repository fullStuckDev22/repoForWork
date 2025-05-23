<?php

use AmoCRM\Client\AmoCRMApiClient;
use Symfony\Component\Dotenv\Dotenv;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Filters\ContactsFilter;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use League\OAuth2\Client\Token\AccessTokenInterface;
use AmoCRM\EntitiesServices\Interfaces\HasParentEntity;
use AmoCRM\Filters\NotesFilter;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Collections\NotesCollection;
use AmoCRM\Models\Factories\NoteFactory;
use AmoCRM\Models\Interfaces\CallInterface;
use AmoCRM\Models\NoteType\CallInNote;
use AmoCRM\Models\NoteType\ServiceMessageNote;
use AmoCRM\Models\NoteType\SmsOutNote;
use Ramsey\Uuid\Uuid;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Collections\NullTagsCollection;
use AmoCRM\Filters\LeadsFilter;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\CustomFieldsValues\BirthdayCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\DateTimeCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NullCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use Carbon\Carbon;
use AmoCRM\Collections\UsersCollection;
use AmoCRM\Models\Rights\RightModel;
use AmoCRM\Models\UserModel;
use AmoCRM\Models\AccountModel;
use League\OAuth2\Client\Token\AccessToken;
use AmoCRM\OAuth2\Client\Provider\AmoCRM;


require_once 'vendor/autoload.php';
include_once 'bootstrap.php';


session_start();


/*
// First init - take access token

if (isset($_GET['referer'])) { $apiClient->setAccountBaseDomain($_GET['referer']); }

if (!isset($_GET['code'])) {
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth2state'] = $state;
    $authorizationUrl = $apiClient->getOAuthClient()->getAuthorizeUrl(['state' => $state, 'mode' => 'post_message']);
    header('Location: ' . $authorizationUrl);
    die;
}

try {
    $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($_GET['code']);

    $data = [
      'accessToken' => $accessToken->getToken(),
      'expires' => $accessToken->getExpires(),
      'refreshToken' => $accessToken->getRefreshToken(),
      'baseDomain' => $apiClient->getAccountBaseDomain(),
    ];

    file_put_contents('token_info.json', json_encode($data));

    $log = $apiClient->getOAuthClient()->getResourceOwner($accessToken);
    echo 'Приветствую, '. $log->getName(). '!<br>Access token saved!';
}
catch (Exception $e) { die((string)$e); }
*/

/*
// Add field for contact
$contact = $apiClient->contacts()->getOne($id);
$customFields = $contact->getCustomFieldsValues();
$phoneField = $customFields->getBy('fieldCode', 'PHONE');
//$contact->setName('Contact Name');

if (empty($phoneField)) {
    $phoneField = (new MultitextCustomFieldValuesModel())->setFieldCode('PHONE');
    $customFields->add($phoneField);
}

$phoneField->setValues((new MultitextCustomFieldValueCollection())->add((new MultitextCustomFieldValueModel())->setEnum('WORK')->setValue('+79061223854')));
try { $apiClient->contacts()->updateOne($contact); }
catch (AmoCRMApiException $e) { printError($e); die; }
*/

// WEBHOOK
if (!empty($_POST)) {

    $data = file_get_contents('php://input');
    $data = $_POST;

    $tokenJson = file_get_contents('token_info.json');
    $tokenJson = json_decode($tokenJson, true);

    $accessToken = new AccessToken([
    'access_token' => $tokenJson['accessToken'],
    'refresh_token' => $tokenJson['refreshToken'],
    'expires' => $tokenJson['expires'],
    'baseDomain' => $tokenJson['baseDomain'],
    ]);


    $apiClient->setAccessToken($accessToken)->setAccountBaseDomain($baseDomain);


    if (isset($data['contacts']['add'])) {

        $doljnost = $data['contacts']['add'][0]['custom_fields'][0]['values'][0]['value'];
        $number = $data['contacts']['add'][0]['custom_fields'][1]['values'][0]['value'];
        $email = $data['contacts']['add'][0]['custom_fields'][2]['values'][0]['value'];

        $userCreated = $data['contacts']['add'][0]['responsible_user_id'];
        $itemId = $data['contacts']['add'][0]['id'];

        $dateCreate = $data['contacts']['add'][0]['created_at'];
        $dateCreate = date('d-m-Y H:i:s', $dateCreate);

        $dateUpdate = $data['contacts']['add'][0]['updated_at'];
        $dateUpdate = date('d-m-Y H:i:s', $dateUpdate);

        file_put_contents('mess.txt', print_r($_POST,true));

        $usersService = $apiClient->users()->getOne($userCreated);
        $userName = $usersService->getName();

        // add Notes
        $notesCollection = new NotesCollection();
        $serviceMessageNote = new ServiceMessageNote();
        $serviceMessageNote->setEntityId($itemId)
        ->setText(' added contact. Time: '.$dateCreate)
        ->setService('Callback responsible user - '.$userName)
        ->setCreatedBy(0);

        $notesCollection->add($serviceMessageNote);

        try {
        $leadNotesService = $apiClient->notes(EntityTypesInterface::CONTACTS);
        $notesCollection = $leadNotesService->add($notesCollection);
        }
        catch (AmoCRMApiException $e) { printError($e); die; }


    }
    else if (isset($data['contacts']['update'])) {

        $doljnost = $data['contacts']['update'][0]['custom_fields'][0]['values'][0]['value'];
        $number = $data['contacts']['update'][0]['custom_fields'][1]['values'][0]['value'];
        $email = $data['contacts']['update'][0]['custom_fields'][2]['values'][0]['value'];

        $userCreated = $data['contacts']['update'][0]['responsible_user_id'];
        $itemId = $data['contacts']['update'][0]['id'];

        $dateCreate = $data['contacts']['update'][0]['created_at'];
        $dateCreate = date('d-m-Y H:i:s', $dateCreate);

        $dateUpdate = $data['contacts']['update'][0]['updated_at'];
        $dateUpdate = date('d-m-Y H:i:s', $dateUpdate);

        file_put_contents('mess.txt', print_r($_POST,true));

        $usersService = $apiClient->users()->getOne($userCreated);
        $userName = $usersService->getName();

        // add Notes
        $notesCollection = new NotesCollection();
        $serviceMessageNote = new ServiceMessageNote();
        $serviceMessageNote->setEntityId($itemId)
        ->setText(' update contact. Time: '.$dateUpdate)
        ->setService('Callback responsible user - '.$userName)
        ->setCreatedBy(0);

        $notesCollection->add($serviceMessageNote);

        try {
        $leadNotesService = $apiClient->notes(EntityTypesInterface::CONTACTS);
        $notesCollection = $leadNotesService->add($notesCollection);
        }
        catch (AmoCRMApiException $e) { printError($e); die; }
    }
}
