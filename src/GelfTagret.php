<?php
/**
 * Created by Kirill Djonua <k.djonua@gmail.com>
 */

namespace kdjonua\grayii;

use Gelf\Publisher;
use Gelf\Transport\HttpTransport;
use Gelf\Transport\TransportInterface;
use kdjonua\grayii\exceptions\InvalidTransportException;
use yii\log\Target;

/**
 * Class GelfTarget
 * @package kdjonua\grayii
 */
class GelfTarget extends Target
{
    public $transport = HttpTransport::class;

    public $host = "0.0.0.0";
    public $port = "12201";
    public $path;
    public $sslOptions;

    /**
     * Exports log [[messages]] to a specific destination.
     * Child classes must implement this method.
     */
    public function export()
    {
        $transport = $this->getTransport();
        $publisher = $this->getPublisher($transport);

        $messageGenerator = $this->messageGeneratorExtractor();
        foreach ($messageGenerator as $message) {
            list($msg, $level, $category, $time, $traces) = $message;

        }
    }

    /**
     * @return \Generator
     */
    protected function messageGeneratorExtractor()
    {
        foreach ($this->messages as $message) {
            yield $message;
        }
    }

    /**
     * @return HttpTransport
     * @throws InvalidTransportException
     */
    protected function getTransport()
    {
        switch ($this->transport) {
            case HttpTransport::class:
                return new HttpTransport($this->host, $this->port, $this->path, $this->sslOptions);
                break;
            default:
                throw new InvalidTransportException($this->transport);
        }
    }

    /**
     * @param TransportInterface $transport
     * @return Publisher
     */
    protected function getPublisher($transport)
    {
        return new Publisher($transport);
    }
}