<?php
/**
 * Created by PhpStorm.
 * User: bob
 * Date: 7/9/14
 * Time: 7:57 AM
 */

namespace Docker\Command;


use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

class CommandOutput {


    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var RequestInterface
     */
    protected $request;


    protected $commandOutput;

    function __construct(RequestInterface $request,ResponseInterface $response,$commandOutput)
    {
        $this->commandOutput = $commandOutput;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->commandOutput;
    }

    /**
     * @return \GuzzleHttp\Message\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }




} 