<?php
/**
 * Created by PhpStorm.
 * User: bob
 * Date: 7/9/14
 * Time: 5:53 AM
 */

namespace Docker\Command\Containers;


use Docker\Command\AbstractCommand;
use Docker\Container;
use Docker\Json;
use GuzzleHttp\Message\ResponseInterface;

class Create extends AbstractCommand {

    protected $path = "/containers/create";
    protected $minVersion = "1.12";
    protected $maxVersion = "1.12";
    protected $method = "post";

    protected $expectedStatusCode = array("201");

    /**
     * @var Container
     */
    protected $container;

    function __construct(Container $container){
        $this->container = $container;
    }


    protected function getOptions(){

        return array(
            'body'         => Json::encode($this->container->getConfig()),
            'headers'      => array('content-type' => 'application/json')
        );

    }

    protected function afterSend(ResponseInterface $response){
        $this->container->setId($response->json()['Id']);
        return true;
    }


}