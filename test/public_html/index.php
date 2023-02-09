<?php

namespace madpeterz\dockerphp\test;

use CurlHandle;
use madpeterz\dockerphp\Builders\ContainersCreateConfig;
use madpeterz\dockerphp\Docker;

include "../load.php";

$docker = new Docker("tcp://localhost:2375");
if ($docker->connected() == false) {
    die("Not connected:" . $docker->getLastErrorBasic());
}
$reply = $docker->apiVersion();
if ($reply->status == false) {
    die("Failed to call apiVersion: " . $reply->errorMessage);
}
echo "Version:" . $reply->body . "<br/>";
$reply = $docker->containers->list();
if ($reply->status == false) {
    die("Failed to load containers");
}

$reply = $docker->images->list(true);
$foundImage = false;
foreach (json_decode($reply->body, true) as $index => $data) {
    $imagedata = explode(":", $data["RepoTags"][0]);
    $imagename = $imagedata[0];
    $imagetag = $imagedata[1];
    echo "Image: " . $imagename . "<br/>";
    echo "Tag: " . $imagetag . "<br/>";
    echo "Created: " . date("l jS \of F Y h:i:s A", $data["Created"]) . "<br/>";
    if ($data["Containers"] != -1) {
        echo "In use: Yes<br/>";
    } else {
        echo "In use: No<br/>";
    }
}

$reply = $docker->containers->list(true, 1000);
echo "There are: " . $reply->data["count"] . " containers<br/>";
$containerId = "";
foreach ($reply->data as $key => $value) {
    if ($key != "count") {
        echo $value["Id"] . " " . $value["Names"][0] . "<br/> ";
        if ($value["Names"][0] == "/web-test") {
            $containerId = $value["Id"];
        }
    }
}

if ($containerId != "") {
    $docker->containers->remove($containerId);
}

$createConfig = new ContainersCreateConfig("crccheck/hello-world:latest");
$createConfig->addMapPort(7654, 8000);
$reply = $docker->containers->create("web-test", $createConfig);
if ($reply->status == true) {
    $containerId = $reply->data["Id"];
    echo "New container created with id: " . $reply->data["Id"] . "";
    $reply = $docker->containers->start($reply->data["Id"]);
    if ($reply->status == true) {
        echo "- Started";
    }
    echo "<br/>";
}
