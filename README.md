Grayii - Graylog2 log target for Yii2 framework applications
===

## Installation

For installation the Grayii library in your projects as composer dependency,
run the command: 

`php composer.phar require kdjonua/grayii`

... or just add to composer.json file the following line:

```text
"kdjonua/grayii": "*"
```

## Configuration

Configure from config array

```php
'components' => [
  'log' => [
    'grayii' => [
      'class' => \kdjonua\grayii\GelfTarget::class,
      'host' => 'http://graylog2-server.com',
      'port' => 12201,
      'transport' => \Gelf\Transport\HttpTransport::class,
    ]
  ]
]
```

... or as DI component

  ```
  \Yii::createObject(\kdjonua\grayii\GelfTarget::class)
  ```

##### Available config parameters:

**Param key**|**Optional**|**Default value**|**Description**
-------------|------------|-----------------|---------------
`transport`|+|Gelf\Transport\HttpTransport|Transport for publishing a message to Graylog2 server. May accepts next values: `Gelf\Transport\HttpTransport` or `Gelf\Transport\UdpTransport`
`host`|+|127.0.0.1|Host of the Graylog2 server
`port`|+|12201|Port of the Graylog2 input
`sslOptions`|+|-|instance of `\Gelf\Transport\SslOptions`
`version`|+|1.1|GELF spec version
`appName`|+|ID of the application|Category name for log message

## Usage

- Sent additional data:

  ```php
  Yii::info([
    '_field1' => 'value1',
    '_field2' => 'value2',
  ]);
  ```

- Sent exception:

  ```php
  try {
    ... running code...
  } catch (\Throwable $t) {
    Yii::warning($t);
  }
  ```
- Sent short message:

  ```php
  Yii::trace('The short message example');
  ```
