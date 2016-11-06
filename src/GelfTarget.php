<?php
/**
 * Created by Kirill Djonua <k.djonua@gmail.com>
 */

namespace kdjonua\grayii;

use Gelf\Message;
use Gelf\Publisher;
use Gelf\Transport\AbstractTransport;
use Gelf\Transport\HttpTransport;
use Gelf\Transport\TransportInterface;
use Gelf\Transport\UdpTransport;
use kdjonua\grayii\exceptions\InvalidTransportException;
use Psr\Log\LogLevel;
use Yii;
use yii\log\Logger;
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
    public $version = '1.1';
    public $appName;

    protected $_logLevels = [
        Logger::LEVEL_ERROR => LogLevel::ERROR,
        Logger::LEVEL_INFO => LogLevel::INFO,
        Logger::LEVEL_PROFILE_BEGIN => LogLevel::DEBUG,
        Logger::LEVEL_PROFILE => LogLevel::DEBUG,
        Logger::LEVEL_PROFILE_END => LogLevel::DEBUG,
        Logger::LEVEL_TRACE => LogLevel::DEBUG,
        Logger::LEVEL_WARNING => LogLevel::WARNING,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->appName = $this->appName ?: Yii::$app->id;
    }

    /**
     * @inheritdoc
     */
    public function export()
    {
        $transport = $this->getTransport();
        $publisher = $this->getPublisher($transport);

        $messageGenerator = $this->messageGeneratorExtractor();
        foreach ($messageGenerator as $message) {
            $gelfMessage = $this->createMessage($message);
            $publisher->publish($gelfMessage);
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
     * @return AbstractTransport
     * @throws InvalidTransportException
     */
    protected function getTransport()
    {
        switch ($this->transport) {
            case HttpTransport::class:
                return new HttpTransport($this->host, $this->port, $this->path, $this->sslOptions);
                break;
            case UdpTransport::class:
                return new UdpTransport($this->host, $this->port, UdpTransport::CHUNK_MAX_COUNT);
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

    /**
     * @param array $data
     * @return Message
     */
    private function createMessage($data)
    {
        list($msg, $level, $category, $time) = $data;
        $message = new Message();

        $message->setLevel($this->yii2LevelToPsrLevel($level));
        $message->setTimestamp($time);
        $message->setVersion($this->version);
        $message->setHost($this->appName ?: Yii::$app->id);

        if ($msg instanceof \Throwable) {
            /** @var \Throwable $short */
            $short = 'Exception';
            if ($msg instanceof \Error) {
                $short = 'Error';
            }

            $message->setShortMessage($short . ' ' . get_class($msg) . ' ' . $msg->getMessage());
            $message->setFullMessage($msg->getTrace());
            $message->setFile($msg->getFile());
            $message->setLine($msg->getLine());
        } elseif (is_string($msg)) {
            $message->setShortMessage($msg);
        } elseif (is_array($msg)) {
            foreach ($msg as $key => $value) {
                if (strpos($key, '_') === 0) {
                    $message->setAdditional($key, $value);
                }
            }
        }

        $message->setAdditional('category', $category);

        return $message;
    }

    /**
     * @param int $yiiLevel
     * @return mixed
     */
    protected function yii2LevelToPsrLevel($yiiLevel) {
        return $this->_logLevels[$yiiLevel];
    }
}
