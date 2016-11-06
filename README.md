Grayii - Graylog2 log target for Yii2 framework applications
===

## Configuration

###### Configure from config array

```
'components' => [
    'log' => [
        'grayii' => [
            'class' => \kdjonua\grayii\GelfTarget::class,
            <other config params>
        ]
    ]
]
```

###### ... or as DI component

`\Yii::createObject(\kdjonua\grayii\GelfTarget::class)`

##### Available config parameters:

**Param key**|**Optional**|**Default value**|**Description**
-------------|------------|-----------------|---------------
`transport`|+|Gelf\Transport\HttpTransport|Transport for publishing a message to Graylog2 server. May accepts next values: `HttpTransport` or `HttpTransport`
`host`|+|127.0.0.1|Host of the Graylog2 server
`port`|+|12201|Port of the Graylog2 input
`sslOptions`|+|-|instance of `\Gelf\Transport\SslOptions`
`version`|+|1.1|GELF spec version
`appName`|+|ID of the application|Category name for log message
