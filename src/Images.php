<?php

namespace madpeterz\dockerphp;

use madpeterz\dockerphp\Reply\BasicReply;

class Images extends UseControler
{
    /**
     * `list` returns a list of images
     * @param bool all Show all images (default hides intermediate images)
     * @param string filters A JSON encoded value of the filters (a map[string][]string) to process on the
     * images list.
     * @param bool sharedSize If true, return the total size of the image and all its parent layers.
     * @param bool digestInfo If true, the digest information will be returned in the response.
     * @return BasicReply A JsonReply object.
     */
    public function list(
        bool $all = false,
        string $filters = "",
        bool $sharedSize = false,
        bool $digestInfo = false
    ): BasicReply {
        return $this->interface->get(
            "images/json",
            ["all" => $all, "filters" => $filters, "shared-size" => $sharedSize, "digests" => $digestInfo]
        );
    }

    /**
     * Pull an image from a registry
     * Please make sure you have logged in via
     * docker->auth->login first before attempting to pull
     * or you will use up all your daily pulls!
     *
     * @param string image The name of the image to pull
     * @param string tag The tag of the image to pull.
     * @return BasicReply A BasicReply object.
     */
    public function pullFromRegistry(string $image, string $tag): BasicReply
    {
        return $this->interface->post(
            "images/create",
            ["fromImage" => $image,"tag" => $tag],
            ["X-Registry-Auth" => $this->interface->dockerLink->auth->getToken()]
        );
    }

    /**
     * `delete` deletes an image
     * @param string imageNameOrId The name or ID of the image to delete
     * @param bool force Force removal of the image
     * @param bool noprune Do not delete untagged parents
     * @return BasicReply A BasicReply object.
     */
    public function delete(string $imageNameOrId, bool $force = false, bool $noprune = false): BasicReply
    {
        return $this->interface->delete("images/" . $imageNameOrId, ["force" => $force, "noprune" => $noprune]);
    }

    /**
     * `deleteUnused` deletes all unused images
     *
     * @param bool dangling Only remove images that are not used by any container.
     * @param fromBeforeUnix Unix timestamp in seconds
     * @param label A label to filter the images by.
     * @return BasicReply A BasicReply object.
     */
    public function deleteUnused(bool $dangling = false, ?int $fromBeforeUnix = null, ?string $label = null): BasicReply
    {
        return $this->interface->post(
            "images/prune",
            ["dangling" => $dangling, "until" => $fromBeforeUnix, "label" => $label]
        );
    }
}
