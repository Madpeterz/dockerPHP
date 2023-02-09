<?php

namespace madpeterz\dockerphp;

use madpeterz\dockerphp\Reply\BasicReply;

class Auth extends UseControler
{
    protected ?string $IdentityToken = null;
    protected bool $supportsIdToken = false;
    protected bool $loggedIn = false;
    protected array $loginDetails = [];

    /**
     * This function returns true if the user is logged in, and false if they are not.
     * @return bool A boolean value.
     */
    public function isLoggedin(): bool
    {
        return $this->loggedIn;
    }

    /**
     * If the user is logged in, return the IdentityToken if supported
     * if not supported returns the json_encode of the login details
     * if not logged in returns a empty string
     * @return string The token is being returned.
     */
    public function getToken(): string
    {
        if ($this->loggedIn == false) {
            return "";
        }
        if ($this->supportsIdToken == true) {
            return $this->IdentityToken;
        }
        return json_encode($this->loginDetails);
    }

    /**
     * It logs in to the Docker registry
     * @param string username your username
     * @param string password The password for the user
     * @param string email The email address of the user.
     * @param string server The server address to use. This is usually https://index.docker.io/v1/
     * @return BasicReply A BasicReply object.
     */
    public function login(
        string $username,
        string $password,
        string $email = "",
        string $server = "https://index.docker.io/v1/"
    ): BasicReply {
        $this->loggedIn = false;
        $this->loginDetails = ["username" => $username, "password" => $password, "email" => $email, "serveraddress" => $server];
        $reply = $this->interface->post("auth", [], $this->loginDetails);
        if ($reply->status == false) {
            return $reply;
        }
        $this->loggedIn = true;
        $dat = json_decode($reply->body, true);
        if (array_key_exists("IdentityToken", $dat) == false) {
            $this->supportsIdToken = false;
            $this->IdentityToken = null;
            return new BasicReply(status: true, body:"logged in - have token");
        }
        return new BasicReply(status: true, body:"logged in - basic mode");
    }
}
