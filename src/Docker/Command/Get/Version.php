<?php

namespace Docker\Command\Get;


use Docker\Command\AbstractCommand;
use Docker\Container;
use Docker\Json;
use GuzzleHttp\Message\ResponseInterface;

class Version extends AbstractCommand {

    protected $path = "/version";
    protected $minVersion = null;
    protected $maxVersion = null;
    protected $method = "get";

    protected $expectedStatusCode = "200";



    protected function afterSend(ResponseInterface $response){
        return $response->json()["Version"];
    }


}