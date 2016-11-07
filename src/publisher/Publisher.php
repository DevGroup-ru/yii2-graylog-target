<?php
/**
 * Created by Kirill Djonua <k.djonua@gmail.com>
 */

namespace kdjonua\grayii\publisher;

use Gelf\MessageValidatorInterface;
use Gelf\Transport\TransportInterface;

/**
 * Class Publisher
 * @package kdjonua\grayii\publisher
 */
class Publisher extends \Gelf\Publisher
{
    public function __construct(TransportInterface $transport, MessageValidatorInterface $messageValidator)
    {
        parent::__construct($transport, $messageValidator);
    }
}
