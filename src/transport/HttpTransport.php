<?php
/**
 * Created by Kirill Djonua <k.djonua@gmail.com>
 */

namespace kdjonua\grayii\transport;

use yii\base\Configurable;

/**
 * Class HttpTransport
 * @package kdjonua\grayii\transport
 */
class HttpTransport extends \Gelf\Transport\HttpTransport implements Configurable
{

    public function __construct($config = [])
    {
        parent::__construct(
            @$config['host'] ?: null,
            @$config['port'] ?: null,
            @$config['path'] ?: null,
            @$config['sslOptions'] ?: null
        );
    }
}
