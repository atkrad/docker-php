<?php
/**
 * Created by PhpStorm.
 * User: bob
 * Date: 7/9/14
 * Time: 5:53 AM
 */

namespace Docker\Command;


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