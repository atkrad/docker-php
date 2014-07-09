<?php

namespace Docker\Command;


use Docker\Exception\RequestCanceledException;
use Docker\Exception\ResponseNotValidException;
use Docker\Docker;
use Docker\Exception\UnexpectedStatusCodeException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Message\ResponseInterface;

abstract class AbstractCommand {

    const METHOD_POST   = "post";
    const METHOD_GET    = "get";
    const METHOD_DELETE = "delete";
    const METHOD_PUT    = "put";



    // params that must be overriden by child class

    protected $path;
    protected $method = self::METHOD_GET;

    // params that should be overriden by child class
    /**
     * When the request is sent, if the status code of the response does not match this. Then an exception is thrown
     * @var null|array|string if array then it is an array of status code, if string it's a single status code ,if null then nothing is done
     */
    protected $expectedStatusCode = null;
    protected $minVersion = null;
    protected $maxVersion = null;



    // Methods that should be overriden by the child class

    /**
     * intended to be overriden by the child class to provide options for the http request
     * @return array the options for the http request
     */
    protected function getOptions(){
        return array();
    }

    /**
     * allows to update the request before it to be sent
     * it mays also allow to stop the request before it to be sent by returning false or throwing an exception.
     * Non false return wont cancel the request execution
     * @param RequestInterface $request
     * @return bool
     */
    protected function beforeSend(RequestInterface $request){
        return true;
    }

    /**
     * allows to check the response. Returning exception or returning false will throw an exception
     * @param ResponseInterface $response
     * @return bool
     */
    protected function afterSend(ResponseInterface $response){
        return true;
    }


    // default behaviours that can be changed by child in extrem cases
    /**
     * create a request for the run method
     * @param Docker $docker
     * @param $apiVersion
     * @return RequestInterface
     */
    protected function _createRequest(Docker $docker, $apiVersion){
        if(null == $apiVersion)
            $apiVersion = $docker->getApiVersion();

        $request = $docker->getHttpClient()
            ->createRequest(
                $this->method,
                $this->_buildUrl($docker,$apiVersion),
                $this->getOptions()
            );

        return $request;
    }

    /**
     * build the url with the version number
     * @param Docker $docker
     * @param $apiVersion
     * @return string
     */
    protected function _buildUrl(Docker  $docker, $apiVersion){

        if($apiVersion){
            if($apiVersion{0} !== "v"){
                $apiVersion = "v$apiVersion";
            }

            $path = ltrim($this->path,"/");

            $url = "/$apiVersion/$path";
        }else{

            $url = "/" . ltrim($this->path,"/");
        }

        return $url;
    }

    /**
     * check status code. Return false if it doesnt match and an exception will be thrown. Non false return will be used as as part of the final return
     * @param ResponseInterface $response
     * @return bool
     */
    protected function _checkStatusCode(ResponseInterface $response){

        if(null !== $this->expectedStatusCode){
            $statusCodeMatches = true;

            if(is_string($this->expectedStatusCode)){
                if($response->getStatusCode() != $this->expectedStatusCode)
                    $statusCodeMatches = false;
            }else if(is_array($this->expectedStatusCode)){
                $statusCode = $response->getStatusCode();
                foreach($this->expectedStatusCode as $expectedStatusCode){
                    if($statusCode != $expectedStatusCode)
                        $statusCodeMatches = false;
                }
            }

            return $statusCodeMatches;

        }

        return true;

    }



    // params and methods the must not be overriden in usual cases
    // they may be overriden by special commands

    /**
     * @param Docker $docker
     * @param null $apiVersion
     * @return CommandOutput
     * @throws RequestCanceledException when request is canceled
     * @throws ResponseNotValidException general not valid response (includes UnexpectedStatusCodeException)
     * @throws \Docker\Exception\UnexpectedStatusCodeException when status code does not match the expected one
     */
    public function run(Docker $docker,$apiVersion = null){

        // REQUEST CREATION
        $request = $this->_createRequest($docker,$apiVersion);

        // BEFORE SEND
        try{
            $beforeSend = $this->beforeSend($request);
        }catch (\Exception $e){
            throw new RequestCanceledException("Request was canceled before being with message : " . $e->getMessage() , 0 , $e);
        }

        if(false === $beforeSend){
            throw new RequestCanceledException("Request was canceled before being sent");
        }
        // BEFORE SEND


        // SEND THE REQUEST
        try{
            $response = $docker->getHttpClient()->send($request);
        }catch (\Exception $e){
            throw new ResponseNotValidException("Response is not valid : " . $e->getMessage());
        }


        // STATUS CODE CHECKING
        $statusCodeMatches = $this->_checkStatusCode($response);
        if(false === $statusCodeMatches){
            throw UnexpectedStatusCodeException::fromResponse($response);
        }
        // STATUS CODE CHECKING



        // AFTER SEND
        try{
            $afterSend = $this->afterSend($response);
        }catch (\Exception $e){
            throw new ResponseNotValidException("Response is not valid : " . $e->getMessage() , 0 , $e);
        }
        if(false === $afterSend){
            throw new ResponseNotValidException("Response is not valid");
        }
        // AFTER SEND



        return new CommandOutput($request,$response,$afterSend);

    }

    /**
     * Get a trace of the request and does not send it
     * @param Docker $docker
     * @param null $apiVersion
     * @return string
     */
    public function debug(Docker $docker,$apiVersion = null){

        $return = "";

        // REQUEST CREATION
        $request = $this->_createRequest($docker,$apiVersion);


        try{
            // BEFORE SEND
            $beforeSend = $this->beforeSend($request);

            if(false === $beforeSend){
                $return .= "Request creation was canceled. But here are the params : " . PHP_EOL . PHP_EOL;
            }

            $request .= $request->__toString();

        }catch (\Exception $e){
            $return .= "Request creation was canceled. Reason : " . $e->getMessage() . PHP_EOL;
        }

        return $return;

    }




} 