Command Implementation
======================

Commands are requests that aim to be sent to the Docker restFull API.

To ease the implementation of Docker API methods (aka endpoints) into Docker-php the requests are built
on a common way by extending a base abstract class.


Simple example with the ``/version`` method
--------------------------------------------

We are going to build a command to call /version of the docker api.

Firstly create the php class that will represent this api method : ``Docker\Command\Get\Version`` .

```php
<?php

namespace Docker\Command\Get;


use Docker\Command\AbstractCommand;
use GuzzleHttp\Message\ResponseInterface;

class Version extends AbstractCommand {

    protected $path = "/version";
    protected $method = "get";

    protected $expectedStatusCode = "200";

    protected function afterSend(ResponseInterface $response){
        return $response->json()["Version"];
    }

}

```

Actually the ``Version`` command class extends the abstract class ``Docker\Command\AbstractCommand``
that makes easy the way we use commands.

Of course this is fully extendable, but let's explain it after. Now we just want to call ``/version``  :

```
<?php

    $client = new Docker\Http\DockerClient(array(), 'unix:///var/run/docker.sock');
    $docker = new Docker\Docker($client);

    $versionCommand = new \Docker\Command\Get\Version();
    $output = $versionCommand->run($docker);

    echo $output->getValue(); // 1.0.0

```

The ``Docker\Command\AbstractCommand::run()`` method will generate the request according to params defined in the child
class, e.g ``Docker\Command\Get\Version`` defines the path ``/version`` the method to use : ``get`` and the expected status code : ``200`` ,
and thus for the given docker instance
Also it will manage errors, versions and response handling.

Let's see how much customizable it is.



Available command configurations
--------------------------------

When you create a command by extending ``Docker\Command\AbstractCommand``  there are a few config availables.

The following list explains what properties and option can be overriden to controller the request :

**$path**, **$method** :

the properties ``path`` and ``method`` are the most important. They allow to define how to access the method.

* ``$path`` is the url that we want to request ( "/version","/containers/json","/containers/create"...). Sometimes the
path contains a variable (e.g containers/(id)) then you can use the constructor to build it.
* ``$method`` is the http method to use ("post","get","put","delete"...)


 **getOptions()**

* ``getOptions(Docker\Docker)`` once overriden this method must return the options as an associative array that will be
used as third parameter for ``GuzzleHttp\Client::createRequest().
Called with the docker instance as first parameter Defaultly return an empty array



**$expectedStatusCode** :

This property can be either ``null`` or an ``array`` or a ``string``.

It allows to control the checking of the status code

* ``null`` (default) nothing will happen. The status code is ignored
* ``string`` the expected status code of the http response. If it does not match then an exception is thrown by ``Docker\Command\AbstractCommand::run()``
* ``array`` list of status code. Works as the string but many status code are possible


**$minVersion** and **maxVerion** :

The two property that allow to control the api version available of the command. Implemented but not used yet.


**beforeSend(GuzzleHttp\Message\RequestInterface)**

 * Allows to modify the request before it to be sent.
 * Allows to stop the request if something is wrong. To stop it **return false** or **throw an exception** (exception allows to send a message,
  returning false will just output a default message). In both cases a ``Docker\Exception\RequestCanceledException``
  will be thrown by ``Docker\Command\AbstractCommand::run()``



**afterSend(GuzzleHttp\Message\ResponseInterface)**

 * Allows to check the response and do some actions according to the result
 * Allows to return a specific result to the user. This result returned by ``afterSend`` will be available through ``Docker\Command\CommandOutput::getValue()``.
 from the instance of ``Docker\Command\CommandOutput``  returned by ``Docker\Command\AbstractCommand::run()``
 * Allows to stop the standard return and to tell that the result is not the one expected. To do it **return false** or **throw an exception**
 (exception allows to send a message, returning false will just output a default message)


Namespace convention
--------------------

By implementing a new command you should respect a logic for namespaces.

All commands will be namespaced by Docker\Command\{{METHOD}}\{{CommandPath}} :

 * ``GET /version`` is ``Docker\Command\Get\Version``
 * ``POST /containers/create`` is ``Docker\Command\Post\Containers\Create``
 * ``DELETE /containers/(id)`` is ``Docker\Command\Delete\Containers\Id``