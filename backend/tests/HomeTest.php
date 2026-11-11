<?php

class HomeTestInput
{
    public $Text;
}

class HomeTestRecord
{
    public $message;
    public $from_user;
    public $saved = false;

    public function saveMessage()
    {
        $this->saved = true;
    }
}

class HomeTestCallbackClient
{
    public $focused;

    public function focus($control)
    {
        $this->focused = $control;
    }
}

class HomeTestUsers
{
    private $exists;
    public $requestedUsername;

    public function __construct($exists)
    {
        $this->exists = $exists;
    }

    public function usernameExists($username)
    {
        $this->requestedUsername = $username;
        return $this->exists;
    }
}

class HomeTestAuth
{
    public $LoginPage = 'Login';
    public $logoutCalled = false;

    public function logout()
    {
        $this->logoutCalled = true;
    }
}

class HomeTestResponse
{
    public $redirectedTo;

    public function Redirect($url)
    {
        $this->redirectedTo = $url;
    }
}

class HomeTestService
{
    public $constructedPage;

    public function ConstructUrl($page)
    {
        $this->constructedPage = $page;
        return '/' . $page;
    }
}

class TestableHome extends Home
{
    public $application;
    public $response;
    public $service;
    public $callbackClient;
    public $userinput;
    public $record;
    public $refreshUserListCalls = 0;
    public $refreshMessageListCalls = 0;

    public function __construct()
    {
        parent::__construct();
        $this->response = new HomeTestResponse();
        $this->service = new HomeTestService();
        $this->callbackClient = new HomeTestCallbackClient();
        $this->userinput = new HomeTestInput();
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getService()
    {
        return $this->service;
    }

    public function getCallbackClient()
    {
        return $this->callbackClient;
    }

    protected function createChatBufferRecord()
    {
        $this->record = new HomeTestRecord();
        return $this->record;
    }

    protected function refreshUserList()
    {
        $this->refreshUserListCalls++;
    }

    protected function refreshMessageList()
    {
        $this->refreshMessageListCalls++;
    }
}

class HomeTest extends PHPUnit\Framework\TestCase
{
    public function testRegisteredUserIsNotRedirectedOnLoad()
    {
        $home = $this->createHome(true);

        $home->onLoad(null);

        $this->assertNull($home->response->redirectedTo);
    }

    public function testUnknownUserIsLoggedOutAndRedirectedToLogin()
    {
        $home = $this->createHome(false);

        $home->onLoad(null);

        $this->assertSame(
            'alice',
            $home->application->Modules['users']->requestedUsername
        );
        $this->assertTrue($home->application->Modules['auth']->logoutCalled);
        $this->assertSame('Login', $home->service->constructedPage);
        $this->assertSame('/Login', $home->response->redirectedTo);
    }

    public function testEmptyMessageDoesNotCreateOrSaveRecord()
    {
        $home = $this->createHome(true);
        $home->userinput->Text = '';

        $home->processMessage(null, null);

        $this->assertNull($home->record);
        $this->assertSame(0, $home->refreshUserListCalls);
        $this->assertSame(0, $home->refreshMessageListCalls);
    }

    public function testNonEmptyMessageIsSavedAndInputCleared()
    {
        $home = $this->createHome(true);
        $home->userinput->Text = 'Hello';

        $home->processMessage(null, null);

        $this->assertTrue($home->record->saved);
        $this->assertSame('Hello', $home->record->message);
        $this->assertSame('alice', $home->record->from_user);
        $this->assertSame(1, $home->refreshUserListCalls);
        $this->assertSame(1, $home->refreshMessageListCalls);
        $this->assertSame('', $home->userinput->Text);
        $this->assertSame($home->userinput, $home->callbackClient->focused);
    }

    public function testRefreshUpdatesUsersAndMessages()
    {
        $home = $this->createHome(true);

        $home->refresh(null, null);

        $this->assertSame(1, $home->refreshUserListCalls);
        $this->assertSame(1, $home->refreshMessageListCalls);
    }

    private function createHome($userExists)
    {
        $home = new TestableHome();
        $auth = new HomeTestAuth();
        $home->application = (object) array(
            'User' => (object) array('Name' => 'alice'),
            'Modules' => array(
                'users' => new HomeTestUsers($userExists),
                'auth' => $auth
            )
        );

        return $home;
    }
}
