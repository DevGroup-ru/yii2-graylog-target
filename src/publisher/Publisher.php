<?php
/**
 * Created by Kirill Djonua <k.djonua@gmail.com>
 */

namespace devgroup\grayii\publisher;

use Gelf\MessageValidatorInterface;
use Gelf\Transport\TransportInterface;

/**
 * Class Publisher
 * @package devgroup\grayii\publisher
 */
class Publisher extends \Gelf\Publisher
{
    /**
     * Publisher constructor.
     * @param TransportInterface $transport
     * @param MessageValidatorInterface $messageValidator
     */
    public function __construct(TransportInterface $transport, MessageValidatorInterface $messageValidator)
    {
        parent::__construct($transport, $messageValidator);
    }
}
