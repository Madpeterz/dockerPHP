<?php

namespace madpeterz\dockerphp;

use madpeterz\dockerphp\Builders\ContainersCreateConfig;
use madpeterz\dockerphp\Reply\BasicReply;
use madpeterz\dockerphp\Reply\JsonReply;

class Containers extends UseControler
{
    /**
     * `list` returns a list of containers
     * @param bool all Show all containers. Only running containers are shown by default (i.e., this
     * defaults to false)
     * @param int limit The number of containers to return.
     * @param bool size true/false, Show total file sizes
     * @param string filters A JSON encoded value of the filters (a map[string][]string) to process on the
     * containers list. Available filters:
     * @return JsonReply A JsonReply object.
     */
    public function list(bool $all = false, int $limit = 10, bool $size = false, string $filters = ""): JsonReply
    {
        $reply = $this->interface->get(
            "containers/json",
            ["all" => $all,"limit" => $limit,"size" => $size,"filters" => $filters]
        );
        if ($reply->status == false) {
            return new JsonReply(errorMessage: $reply->errorMessage);
        }
        if (strlen($reply->body) == 0) {
            return new JsonReply(status: true, data: ["count" => 0]);
        }
        $reply = json_decode($reply->body, true);
        $count = count($reply);
        $reply["count"] = $count;
        return new JsonReply(status: true, data:$reply);
    }

    /**
     * `inspect` returns a `BasicReply` object containing the JSON response from the Docker API
     * @param string Id The container ID
     * @param bool size true/false, shows the size of the container
     * @return BasicReply A BasicReply object.
     */
    public function inspect(string $Id, bool $size = false): BasicReply
    {
        return $this->interface->get("containers/" . $Id . "/json", ["size" => $size]);
    }

    /**
     * `logs` returns the logs of a container
     * @param string id The ID or name of the container
     * @param bool stdout true/false, return logs from stdout
     * @param bool stderr Show stderr log messages.
     * @param int since Unix timestamp (UTC) to filter logs. Only logs since this timestamp will be
     * returned.
     * @param int until UNIX timestamp. Only return logs since this time.
     * @param bool timestamps boolean
     * @return BasicReply A BasicReply object.
     */
    public function logs(
        string $id,
        bool $stdout = true,
        bool $stderr = false,
        int $since = 0,
        int $until = 0,
        bool $timestamps = false
    ): BasicReply {
        return $this->interface->get(
            "containers/" . $id . "/logs",
            ["stdout" => $stdout,"stderr" => $stderr,"since" => $since,"until" => $until,"timestamps" => $timestamps]
        );
    }

    /**
     * `create` creates a container
     * @param string name The name of the container.
     * @param CreateContainerPayload config A CreateContainerPayload object.
     * @param string platform The platform to run the container on.
     * @return JsonReply A BasicReply object.
     */
    public function create(string $name, ContainersCreateConfig $config, string $platform = ""): JsonReply
    {
        $reply = $this->interface->post(
            "containers/create",
            ["name" => $name],
            $config,
            [201]
        );
        if ($reply->status == false) {
            return new JsonReply(errorMessage: $reply->errorMessage);
        }
        return new JsonReply(status: true, data:json_decode($reply->body, true));
    }

    /**
     * `start` starts a container
     * @param string id The id of the container you want to start
     * @return BasicReply A BasicReply object.
     */
    public function start(string $id): BasicReply
    {
        return $this->interface->post("containers/" . $id . "/start", allowedCodes:[204,304]);
    }

    /**
     * `stop` stops a container
     * @param string id The ID of the container to stop
     * @param int killWaitTimeSecs The number of seconds to wait before killing the container.
     * @return BasicReply A BasicReply object.
     */
    public function stop(string $id, int $killWaitTimeSecs = 1): BasicReply
    {
        return $this->interface->post(
            "containers/" . $id . "/stop",
            ["t" => $killWaitTimeSecs],
            allowedCodes:[204,304]
        );
    }

    /**
     * `restart` restarts a container
     * @param string id The ID of the container to restart
     * @param int killWaitTimeSecs The number of seconds to wait before killing the container.
     * @return BasicReply A BasicReply object.
     */
    public function restart(string $id, int $killWaitTimeSecs = 1): BasicReply
    {
        return $this->interface->post(
            "containers/" . $id . "/restart",
            ["t" => $killWaitTimeSecs],
            allowedCodes:[204]
        );
    }

    public function kill(string $id): BasicReply
    {
        return $this->interface->post("containers/" . $id . "/kill", ["signal" => "SIGKILL"], allowedCodes:[204]);
    }

    public function remove(string $id, bool $cleanup = false, bool $force = false, bool $unlink = false): BasicReply
    {
        if ($this->stop($id)->status == false) {
            return new BasicReply(errorMessage: "Unable to stop container before removal!");
        }
        return $this->interface->delete(
            "containers/" . $id,
            [
                "v" => $cleanup,
                "force" => $force,
                "link" => $unlink,
            ],
            allowedCodes:[204]
        );
    }
}
