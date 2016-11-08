# CHANGELOG

## 8.11.2016: 3.0.0 release
    - [fix] autoload section adds to composer.json
    - [fix] `t.HttpTransport` and `t.UdpTransport`: 'at' symbol makes initialization safely
    - [fix] `k.g.GelfTarget`: `init` method sets a `MessageValidator` and `Publisher` instances to the DI container; publishing message action extracted to the separately method
    - [fix] `getPublisher` method: removed second param from `container->get` function 
    - [fix] `createMessage` method: if $msg is array - message gets a "short" message and "full" message from array keys 
    - [fix] `createMessage` method: if message instanse of Throwable, `fullMessage` will be a string (stack trace as string)
    - [new] add public method `getMessageValidator` to `k.g.GraylogTarget`
    - [new] Created `k.g.p.Publisher` class, which extends `Gelf\Publisher` but have not optional construct params

## 7.11.2016: 2.0.0 release
    - [new] Implements configuration with using DI cotainer
    - [brake] Method `getTransport` now is NOT protected
    - [new] Method `getTransport` now is public
    - [brake] Method `getPublisher` now is NOT protected
    - [new] Method `getPublisher` now is public
    - [new] `MessageValidator` for `Publisher` now is configurable
    - [new] You can specify DI container in config 
    
## 6.11.2016: 1.0.0 release
