yii2-graylog-target
===

###### Graylog2 log target for Yii2 framework applications

## Installation

For installation the yii2-graylog-target library in your projects as composer dependency,
run the command: 

`php composer.phar require "devgroup/yii2-graylog-target:*"`

... or just add to composer.json file the following line:

```text
"devgroup/yii2-graylog-target": "*"
```

## Configuration

Configure from config array

```php
'components' => [
  'log' => [
    'grayii' => [
      'transport' => [
        'class' => \Gelf\Transport\HttpTransport::class,
        'host' => 'http://graylog2-server.com',
        'port' => 12201,
      ],
      'appName' => 'app name',
    ]
  ]
]
```

##### Available config parameters:

**Param key**|**Optional**|**Default value**|**Description**
-------------|------------|-----------------|---------------
`transport`|+|Gelf\Transport\HttpTransport|Transport config array for publishing a message to Graylog2 server
`publisher`|+|Gelf\Publisher|Publisher config array
`messageValidator`|+|Gelf\MessageValidator|Message validator class for publisher
`container`|+|\Yii::$container|DI container
`version`|+|1.1|GELF spec version
`appName`|+|ID of the application|Category name for log message

## Usage

- Sent additional data:

  ```php
  Yii::info([
    'short' => 'Short message or title',  // required. alternate usage variant: `Short message or title` (without a key 'short', but first item in array)
    'full' => 'Full message',             // optional. full message of log. For example, may be a stack trace as string or other detalized message
    '_field1' => 'value1',                // optional. additional field starts with '_' symbol
    '_field2' => 'value2',                // optional. additional field starts with '_' symbol
  ]);
  ```

- Sent exception:

  ```php
  try {
    ... running code with harmful bugs...
  } catch (\Throwable $t) {
    Yii::warning($t);
  }
  ```
- Sent short message:

  ```php
  Yii::trace('The short message example'); // or equivalent usage is Yii::trace(['The short message example']);
  ```

## Tests

Tests was placed int the **test** directory. Tests can be running by the command:

`php composer.phar exec codecept run`
