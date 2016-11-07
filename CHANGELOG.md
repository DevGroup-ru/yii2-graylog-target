# CHANGELOG

## next release
    - [fix] autoload section adds to composer.json
    - [fix] `t.HttpTransport` and `t.UdpTransport`: 'at' symbol makes initialization safely
    - [fix] `k.g.GelfTarget`: `init` method sets a `MessageValidator` and `Publisher` instances to the DI container; publishing message action extracted to the separately method
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
