<?php

use Prado\Web\Services\TJsonResponse;

class ApiController extends TJsonResponse
{
    public function getJsonContent()
    {
        $route = $this->getRequestPath();
        $method = isset($_SERVER['REQUEST_METHOD'])
            ? strtoupper($_SERVER['REQUEST_METHOD'])
            : 'GET';

        try {
            if ($route === '/api' || strpos($route, '/api/') === 0) {
                $this->requireApiKey();
            }

            if ($route === '/api/process-message') {
                return $this->processMessage();
            }

            $data = $route === '/api/sensors/register_reading' && $method === 'POST'
                ? $this->getSensorRequestData()
                : $this->getRequestData();
            $operation = $this->resolveOperation($route, $method, $data);

            if ($operation === null) {
                $this->getResponse()->setStatusCode(404);
                return array('status' => 'error', 'message' => 'Endpoint not found.');
            }

            list($controllerName, $action) = $operation;
            $controller = $this->createResourceController($controllerName);

            return array(
                'status' => 'success',
                'data' => $controller->$action($data)
            );
        } catch (ApiRequestException $exception) {
            $this->getResponse()->setStatusCode($exception->getCode());
            return array(
                'status' => 'error',
                'message' => $exception->getMessage()
            );
        }
    }

    private function requireApiKey()
    {
        $expectedKey = getenv('API_KEY');
        if ($expectedKey === false || $expectedKey === '') {
            throw new ApiRequestException(500, 'API key authentication is not configured.');
        }

        $providedKey = isset($_SERVER['HTTP_X_API_KEY'])
            ? $_SERVER['HTTP_X_API_KEY']
            : '';
        if (!is_string($providedKey) || !hash_equals($expectedKey, $providedKey)) {
            throw new ApiRequestException(401, 'A valid API key is required.');
        }
    }

protected function getRequestPath()
{
    $requestUri = isset($_SERVER['REQUEST_URI'])
        ? $_SERVER['REQUEST_URI']
        : '/';

    $path = parse_url($requestUri, PHP_URL_PATH);

    if (!is_string($path)) {
        return '/';
    }

    // Remove the entry script's directory when the application is deployed under a prefix.
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
    $scriptDirectory = str_replace('\\', '/', dirname($scriptName));
    $scriptDirectory = $scriptDirectory === '.' ? '' : rtrim($scriptDirectory, '/');
    if ($scriptDirectory !== '' && strpos($path, $scriptDirectory . '/') === 0) {
        return rtrim(substr($path, strlen($scriptDirectory)), '/') ?: '/';
    }

    // Preserve the known UserDir prefixes when the entry script path is unavailable.
    $userDirPrefix = '/~rafal/prado_backend_react_fronted/backend';
    $userDirPrefix2 = '/~rafal/prado/backend';
    if (strpos($path, $userDirPrefix) === 0 ) {
        $path = substr($path, strlen($userDirPrefix));
    }

    if (strpos($path, $userDirPrefix2) === 0 ) {
        $path = substr($path, strlen($userDirPrefix2));
    }
    return rtrim($path, '/') ?: '/';
}

    protected function getRequestData()
    {
        $data = $_GET;
        unset($data['json']);

        $body = $this->getRequestBody();
        if ($body === '') {
            return $data;
        }

        $decoded = json_decode($body);
        if (!is_object($decoded) && !is_array($decoded)) {
            throw new ApiRequestException(400, 'Request body must be a JSON object or array.');
        }

        $bodyData = is_object($decoded) ? get_object_vars($decoded) : $decoded;
        return array_merge($data, $bodyData);
    }

    protected function getSensorRequestData()
    {
        $decoded = json_decode($this->getRequestBody());
        if (!is_object($decoded) && !is_array($decoded)) {
            throw new ApiRequestException(400, 'Request body must be a JSON object or array.');
        }

        return array(
            'gate-nr' => isset($_GET['gate-nr']) ? $_GET['gate-nr'] : null,
            'readings' => is_array($decoded) ? $decoded : array($decoded)
        );
    }

    protected function resolveOperation($route, $method, $data)
    {
        $routes = array(
            '/api/users' => array(
                'GET' => array_key_exists('id', $data)
                    ? array('UserApiController', 'getOne')
                    : array('UserApiController', 'getAll')
            ),
            '/api/user' => array(
                'GET' => array('UserApiController', 'getOne'),
                'POST' => array('UserApiController', 'save'),
                'PUT' => array('UserApiController', 'update'),
                'DELETE' => array('UserApiController', 'remove')
            ),
            '/api/users_w_comments' => array(
                'GET' => array('UserApiController', 'getAllWithCommentsSQL'),
            ),            
            '/api/comments' => array(
                'GET' => array('CommentApiController', 'getOne'),
                'POST' => array('CommentApiController', 'save'),
                'PUT' => array('CommentApiController', 'update'),
                'DELETE' => array('CommentApiController', 'remove')
            ),
            '/api/chat_users' => array(
                'GET' => array_key_exists('username', $data)
                    ? array('ChatUserApiController', 'getOne')
                    : array('ChatUserApiController', 'getAll')
            ),
            '/api/chat_user' => array(
                'GET' => array('ChatUserApiController', 'getOne'),
                'POST' => array('ChatUserApiController', 'save'),
                'DELETE' => array('ChatUserApiController', 'removeAll')
            ),
            '/api/chat_user_last_activity' => array(
                'GET' => array('ChatUserApiController', 'getLastActivity'),
                'PUT' => array('ChatUserApiController', 'setLastActivity')
            ),
            '/api/user_messages' => array(
                'GET' => array_key_exists('username', $data)
                    ? array('ChatBufferApiController', 'getUserMessagesApi')
                    : array('ChatBufferApiController', 'getAllMessagesApi')
            ),
            '/api/user_message_created_on' => array(
                'GET' => array('ChatBufferApiController', 'getCreatedOn'),
                'PUT' => array('ChatBufferApiController', 'setCreatedOn')
            ),
            '/api/user_message' => array(
                'POST' => array('ChatBufferApiController', 'saveMessage')
            ),
            '/api/message_history' => array(
                'GET' => array_key_exists('username', $data)
                    ? array('ChatHistoryApiController', 'getOne')
                    : array('ChatHistoryApiController', 'getAllMessages')
            ),
            '/api/nba/teams' => array(
                'GET' => array_key_exists('teamname', $data)
                    ? array('TeamRecordApiController', 'getOne')
                    : array('TeamRecordApiController', 'getAll')
            ),
            '/api/nba/teams_w_players' => array(
                'GET' => array_key_exists('teamname', $data)
                    ? array('TeamRecordApiController', 'getOneWithPlayers')
                    : array('TeamRecordApiController', 'getAllWithPlayers')
            ),
            '/api/sensors/register_reading' => array(
                'POST' => array('SensorApiController', 'registerReading')
            ),
           '/api/test/post-json' => array(
                'POST' => array('TestApiController', 'postJson')
            )
        );

        if (!isset($routes[$route])) {
            return null;
        }

        if (!isset($routes[$route][$method])) {
            throw new ApiRequestException(405, 'Method not allowed for this endpoint.');
        }

        return $routes[$route][$method];
    }

    protected function createResourceController($controllerName)
    {
        return new $controllerName();
    }

    protected function getRequestBody()
    {
        return file_get_contents('php://input');
    }

    private function processMessage()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->getResponse()->setStatusCode(405);
            return array(
                'status' => 'error',
                'message' => 'Method not allowed. Use POST.'
            );
        }

        try {
            $data = $this->getRequestData();
        } catch (ApiRequestException $exception) {
            $this->getResponse()->setStatusCode($exception->getCode());
            return array('status' => 'error', 'message' => $exception->getMessage());
        }

        if (empty($data['userinput'])) {
            $this->getResponse()->setStatusCode(400);
            return array('status' => 'error', 'message' => 'Missing required field: userinput.');
        }

        return array(
            'status' => 'success',
            'data' => array(
                'received' => $data['userinput'],
                'result' => strtoupper($data['userinput']),
                'timestamp' => time()
            )
        );
    }
}
