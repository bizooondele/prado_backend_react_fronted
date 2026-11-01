<?php

class ApiControllerTestResponse
{
    public $statusCode = 200;

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }
}

class TestableApiController extends ApiController
{
    public $response;
    public $requestBody = '';
    public $createdController;
    public $resource;

    public function __construct()
    {
        parent::__construct();
        $this->response = new ApiControllerTestResponse();
    }

    public function getResponse()
    {
        return $this->response;
    }

    protected function getRequestBody()
    {
        return $this->requestBody;
    }

    protected function createResourceController($controllerName)
    {
        $this->createdController = $controllerName;
        $this->resource = new ApiControllerTestResource();
        return $this->resource;
    }
}

class ApiControllerTestResource
{
    public $action;
    public $data;

    public function __call($action, $arguments)
    {
        $this->action = $action;
        $this->data = $arguments[0];
        return array('action' => $action);
    }
}

class ApiControllerTest extends PHPUnit\Framework\TestCase
{
    private $originalRequestMethod;
    private $originalRequestUri;
    private $originalApiKeyHeader;
    private $originalApiKey;
    private $originalPost;
    private $originalGet;

    protected function setUp(): void
    {
        $this->originalRequestMethod = isset($_SERVER['REQUEST_METHOD'])
            ? $_SERVER['REQUEST_METHOD']
            : null;
        $this->originalRequestUri = isset($_SERVER['REQUEST_URI'])
            ? $_SERVER['REQUEST_URI']
            : null;
        $this->originalApiKeyHeader = isset($_SERVER['HTTP_X_API_KEY'])
            ? $_SERVER['HTTP_X_API_KEY']
            : null;
        $this->originalApiKey = getenv('API_KEY');
        $this->originalPost = $_POST;
        $this->originalGet = $_GET;
        $_POST = array();
        $_GET = array();
        $_SERVER['REQUEST_URI'] = '/api/process-message';
        $_SERVER['HTTP_X_API_KEY'] = 'test-api-key';
        putenv('API_KEY=test-api-key');
    }

    protected function tearDown(): void
    {
        if ($this->originalRequestMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->originalRequestMethod;
        }

        if ($this->originalRequestUri === null) {
            unset($_SERVER['REQUEST_URI']);
        } else {
            $_SERVER['REQUEST_URI'] = $this->originalRequestUri;
        }

        if ($this->originalApiKeyHeader === null) {
            unset($_SERVER['HTTP_X_API_KEY']);
        } else {
            $_SERVER['HTTP_X_API_KEY'] = $this->originalApiKeyHeader;
        }

        if ($this->originalApiKey === false) {
            putenv('API_KEY');
        } else {
            putenv('API_KEY=' . $this->originalApiKey);
        }

        $_POST = $this->originalPost;
        $_GET = $this->originalGet;
    }

    public function testRejectsMissingApiKey()
    {
        unset($_SERVER['HTTP_X_API_KEY']);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $controller = new TestableApiController();
        $controller->requestBody = '{"userinput":"hello"}';

        $result = $controller->getJsonContent();

        $this->assertSame(401, $controller->response->statusCode);
        $this->assertSame('error', $result['status']);
    }

    public function testRejectsInvalidApiKey()
    {
        $_SERVER['HTTP_X_API_KEY'] = 'wrong-api-key';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $controller = new TestableApiController();
        $controller->requestBody = '{"userinput":"hello"}';

        $result = $controller->getJsonContent();

        $this->assertSame(401, $controller->response->statusCode);
        $this->assertSame('error', $result['status']);
    }

    public function testRejectsRequestsWhenApiKeyIsNotConfigured()
    {
        putenv('API_KEY');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $controller = new TestableApiController();
        $controller->requestBody = '{"userinput":"hello"}';

        $result = $controller->getJsonContent();

        $this->assertSame(500, $controller->response->statusCode);
        $this->assertSame('error', $result['status']);
    }

    public function testRejectsGetRequests()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $controller = new TestableApiController();

        $result = $controller->getJsonContent();

        $this->assertSame(405, $controller->response->statusCode);
        $this->assertSame('error', $result['status']);
    }

    public function testRejectsMissingInput()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $controller = new TestableApiController();

        $result = $controller->getJsonContent();

        $this->assertSame(400, $controller->response->statusCode);
        $this->assertSame('error', $result['status']);
    }

    public function testRejectsMalformedJson()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $controller = new TestableApiController();
        $controller->requestBody = '{"userinput":';

        $result = $controller->getJsonContent();

        $this->assertSame(400, $controller->response->statusCode);
        $this->assertSame('error', $result['status']);
    }

    public function testProcessesFormInput()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['userinput'] = 'hello';
        $controller = new TestableApiController();

        $result = $controller->getJsonContent();

        $this->assertSame(200, $controller->response->statusCode);
        $this->assertSame('success', $result['status']);
        $this->assertSame('hello', $result['data']['received']);
        $this->assertSame('HELLO', $result['data']['result']);
        $this->assertArrayHasKey('timestamp', $result['data']);
    }

    public function testProcessesJsonInput()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $controller = new TestableApiController();
        $controller->requestBody = '{"userinput":"hello"}';

        $result = $controller->getJsonContent();

        $this->assertSame(200, $controller->response->statusCode);
        $this->assertSame('success', $result['status']);
        $this->assertSame('hello', $result['data']['received']);
        $this->assertSame('HELLO', $result['data']['result']);
        $this->assertIsInt($result['data']['timestamp']);
    }

    public function testDispatchesUsersLookupByIdToGetOne()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/users';
        $controller = new TestableApiController();
        $controller->requestBody = '{"id":42}';

        $result = $controller->getJsonContent();

        $this->assertSame('UserApiController', $controller->createdController);
        $this->assertSame('getOne', $controller->resource->action);
        $this->assertSame(42, $controller->resource->data['id']);
        $this->assertSame('success', $result['status']);
    }

    public function testDispatchesChatUserLastActivityUpdate()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['REQUEST_URI'] = '/api/chat_user_last_activity';
        $controller = new TestableApiController();
        $controller->requestBody = '{"username":"alice","last_activity":123}';

        $result = $controller->getJsonContent();

        $this->assertSame('ChatUserApiController', $controller->createdController);
        $this->assertSame('success', $result['status']);
        $this->assertSame('setLastActivity', $controller->resource->action);
        $this->assertSame('alice', $controller->resource->data['username']);
    }

    public function testRejectsInvalidJsonBodyOnGet()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/users';
        $controller = new TestableApiController();
        $controller->requestBody = '{"id":';

        $result = $controller->getJsonContent();

        $this->assertSame(400, $controller->response->statusCode);
        $this->assertSame('error', $result['status']);
    }

    public function testRejectsUnknownApiRoute()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/unknown';
        $controller = new TestableApiController();

        $result = $controller->getJsonContent();

        $this->assertSame(404, $controller->response->statusCode);
        $this->assertSame('error', $result['status']);
    }

    /**
     * @dataProvider apiRouteCases
     */
    public function testDispatchesConfiguredRoute($route, $method, $data, $expectedController, $expectedAction)
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $route;
        $_GET = $method === 'GET' ? $data : array();
        $controller = new TestableApiController();
        $controller->requestBody = json_encode((object) $data);

        $result = $controller->getJsonContent();

        $this->assertSame($expectedController, $controller->createdController);
        $this->assertSame($expectedAction, $controller->resource->action);
        $this->assertSame($data, $controller->resource->data);
        $this->assertSame('success', $result['status']);
    }

    public static function apiRouteCases()
    {
        return array(
            array('/api/users', 'GET', array(), 'UserApiController', 'getAll'),
            array('/api/users', 'GET', array('id' => '7'), 'UserApiController', 'getOne'),
            array('/api/user', 'POST', array('name' => 'Alice'), 'UserApiController', 'save'),
            array('/api/user', 'PUT', array('id' => 7, 'name' => 'Alice'), 'UserApiController', 'update'),
            array('/api/user', 'DELETE', array('id' => 7), 'UserApiController', 'remove'),
            array('/api/chat_users', 'GET', array(), 'ChatUserApiController', 'getAll'),
            array('/api/chat_users', 'GET', array('username' => 'alice'), 'ChatUserApiController', 'getOne'),
            array('/api/chat_user', 'GET', array('username' => 'alice'), 'ChatUserApiController', 'getOne'),
            array('/api/chat_user_last_activity', 'GET', array('username' => 'alice'), 'ChatUserApiController', 'getLastActivity'),
            array('/api/chat_user_last_activity', 'PUT', array('username' => 'alice', 'last_activity' => 123), 'ChatUserApiController', 'setLastActivity'),
            array('/api/chat_user', 'POST', array('username' => 'alice'), 'ChatUserApiController', 'save'),
            array('/api/chat_user', 'DELETE', array(), 'ChatUserApiController', 'removeAll'),
            array('/api/user_messages', 'GET', array('username' => 'alice'), 'ChatBufferApiController', 'getUserMessages'),
            array('/api/user_message_created_on', 'GET', array('id' => '8'), 'ChatBufferApiController', 'getCreatedOn'),
            array('/api/user_message_created_on', 'PUT', array('id' => 8, 'created_on' => 123), 'ChatBufferApiController', 'setCreatedOn'),
            array('/api/user_message', 'POST', array('from_user' => 'alice', 'message' => 'Hi'), 'ChatBufferApiController', 'saveMessage')
        );
    }
}
