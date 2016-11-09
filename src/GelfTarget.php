<?php
/**
 * Created by Kirill Djonua <k.djonua@gmail.com>
 */

namespace devgroup\grayii;

use Gelf\Message;
use Gelf\MessageValidator;
use Gelf\MessageValidatorInterface;
use Gelf\PublisherInterface;
use Gelf\Transport\TransportInterface;
use devgroup\grayii\publisher\Publisher;
use devgroup\grayii\transport\HttpTransport;
use devgroup\grayii\helper\PhpVersionChecker;
use devgroup\grayii\helper\PhpVersionCheckerInterface;
use Psr\Log\LogLevel;
use Yii;
use yii\di\Container;
use yii\log\Logger;
use yii\log\Target;

/**
 * Class GelfTarget
 * @package devgroup\grayii
 */
class GelfTarget extends Target
{
    public $transport = [
        'class' => HttpTransport::class
    ];

    public $publisher = [
        'class' => Publisher::class
    ];

    public $messageValidator = [
        'class' => MessageValidator::class
    ];

    public $phpVersionChecker = [
        'class' => PhpVersionChecker::class
    ];

    /**
     * @var Container
     */
    public $container;

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
        $this->container = $this->container ?: Yii::$container;

        $this->container->set(TransportInterface::class, $this->transport);
        $this->container->set(MessageValidatorInterface::class, $this->messageValidator);
        $this->container->set(PublisherInterface::class, $this->publisher);

        $this->container->set(PhpVersionCheckerInterface::class, $this->phpVersionChecker);
    }

    /**
     * @inheritdoc
     */
    public function export()
    {
        $messageGenerator = $this->messageGeneratorExtractor();
        foreach ($messageGenerator as $message) {
            $gelfMessage = $this->createMessage($message);
            $this->publishMessage($gelfMessage);
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
     * @return TransportInterface
     */
    public function getTransport()
    {
        return $this->container->get(TransportInterface::class);
    }

    /**
     * @return PublisherInterface
     */
    public function getPublisher()
    {
        return $this->container->get(PublisherInterface::class);
    }

    /**
     * @return MessageValidatorInterface
     */
    public function getMessageValidator() {
        return $this->container->get(MessageValidatorInterface::class);
    }

    /**
     * @return PhpVersionCheckerInterface
     */
    public function getPhpVersionChecker() {
        return $this->container->get(PhpVersionCheckerInterface::class);
    }

    /**
     * @param array $data
     * @return Message
     */
    protected function createMessage($data)
    {
        list($msg, $level, $category, $time) = $data;
        $message = new Message();

        $message->setLevel($this->yii2LevelToPsrLevel($level));
        $message->setTimestamp($time);
        $message->setVersion($this->version);
        $message->setHost($this->appName ?: Yii::$app->id);

        if ($this->isThrowableMessage($msg)) {
            $short = 'Exception';

            if ($this->getPhpVersionChecker()->isPhp70() && $msg instanceof \Error) {
                $short = 'Error';
            }

            $message->setShortMessage($short . ' ' . get_class($msg) . ' ' . $msg->getMessage());
            $message->setFullMessage($msg->getTraceAsString());
            $message->setFile($msg->getFile());
            $message->setLine($msg->getLine());
        } elseif (is_string($msg)) {
            $message->setShortMessage($msg);
        } elseif (is_array($msg)) {
            if (!empty($msg['short'])) {
                $message->setShortMessage($msg['short']);
            } elseif (!empty($msg[0])) {
                $message->setShortMessage($msg[0]);
            }

            if (!empty($msg['full'])) {
                $message->setFullMessage($msg['full']);
            }

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

    /**
     * @param $gelfMessage
     */
    protected function publishMessage($gelfMessage)
    {
        $this->getPublisher()->publish($gelfMessage);
    }

    private function isThrowableMessage($msg)
    {
        if ($this->getPhpVersionChecker()->isPhp70()) {
            return $msg instanceof \Throwable;
        } else {
            return $msg instanceof \Exception;
        }
    }
}
