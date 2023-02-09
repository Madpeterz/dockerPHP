<?php

namespace madpeterz\dockerphp;

use Exception;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Http\Client\Socket\Client;
use madpeterz\dockerphp\Reply\BasicReply;
use Nyholm\Psr7\Request;
use YAPF\Core\ErrorControl\ErrorLogging;

class SocketControl extends ErrorLogging
{
    protected bool $isConnected = false;
    public function connected(): bool
    {
        return $this->isConnected;
    }
    protected Client $socketClient;
    public function __construct(string $socket, int $timeoutMS = 3000)
    {
        $options = [
            "remote_socket" => $socket,
            "timeout" => $timeoutMS,
            "ssl" => null,
        ];
        try {
            $this->socketClient = new Client($options);
            $this->isConnected = true;
        } catch (Exception $e) {
            $this->addError("Failed to setup socket:" . $e->getMessage());
        }
    }
    public ?Docker $dockerLink = null;
    public function attachDockerObject(Docker &$obj): void
    {
        $this->dockerLink = $obj;
    }

    public function post(
        string $endpoint,
        array $queryKeyPair = [],
        $postBody = null,
        array $allowedCodes = [200]
    ): BasicReply {
        $headers = ["Content-type" => "application/json","Host" => "http"];
        if ($postBody === null) {
            $postBody = "";
        } elseif ((is_array($postBody) == true) || (is_object($postBody) == true)) {
            $postBody = json_encode($postBody, JSON_UNESCAPED_SLASHES);
        }
        if ($postBody != "") {
            $headers["Content-Length"] = strlen($postBody);
        }
        $request = new Psr7Request(
            "POST",
            $this->makeQueryArgs($endpoint, $queryKeyPair),
            $headers,
            $postBody
        );
        return $this->runRequest(
            $request,
            $allowedCodes
        );
    }
    public function delete(
        string $endpoint,
        array $queryKeyPair = [],
        $postBody = null,
        array $allowedCodes = [200]
    ): BasicReply {
        $headers = ["Host" => "http","Content-Type" => "application/json"];
        if ($postBody === null) {
            $postBody = "";
        }
        if (is_object($postBody) == true) {
            $postBody = json_encode($postBody);
        }
        if ($postBody != "") {
            $headers["Content-Length"] = strlen($postBody);
        }
        return $this->runRequest(
            new Psr7Request(
                "delete",
                $this->makeQueryArgs($endpoint, $queryKeyPair),
                $headers,
                $postBody
            ),
            $allowedCodes
        );
    }
    public function get(string $endpoint, array $queryKeyPair = [], array $allowedCodes = [200]): BasicReply
    {
        return $this->runRequest(
            new Psr7Request("get", $this->makeQueryArgs($endpoint, $queryKeyPair), ["host" => "localhost"]),
            $allowedCodes
        );
    }
    protected function runRequest(Psr7Request $request, array $allowedCodes): BasicReply
    {
        try {
            $reply = $this->socketClient->sendRequest($request);
            if (!in_array($reply->getStatusCode(), $allowedCodes)) {
                return new BasicReply(
                    errorMessage:$reply->getReasonPhrase() . "code: " . $reply->getStatusCode() . "  
                    " . $request->getUri()
                );
            }
            $body = $reply->getBody();
            $bodyText = $body->getContents();
            $bits = explode("\r", $bodyText);
            if ($bits > 1) {
                if (array_key_exists(1, $bits) == true) {
                    $bodyText = trim($bits[1]);
                }
            }
            return new BasicReply(
                status: true,
                body:$bodyText
            );
        } catch (Exception $e) {
            return new BasicReply(errorMessage:"get failed:" . $e->getMessage() . " " . $request->getUri());
        }
    }

    protected function makeQueryArgs(string $endpoint, array $queryKeyPair): string
    {
        $uri = "/" . $endpoint;
        if (count($queryKeyPair) == 0) {
            return $uri;
        }
        $uri .= "?";
        $addon = "";
        foreach ($queryKeyPair as $key => $value) {
            if ($value === "") {
                continue;
            }
            if ($value === null) {
                continue;
            }
            if (is_bool($value) == true) {
                if ($value == true) {
                    $value = "true";
                }
                $value = "false";
            }
            $uri .= $addon . $key . "=" . $value;
            $addon = "&";
        }
        return $uri;
    }
}
