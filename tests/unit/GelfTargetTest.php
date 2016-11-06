<?php

namespace kdjonua\grayii\tests;

use Gelf\Message;
use Gelf\Transport\HttpTransport;
use Gelf\Transport\SslOptions;
use Gelf\Transport\UdpTransport;
use kdjonua\grayii\exceptions\InvalidTransportException;
use kdjonua\grayii\GelfTarget;
use Psr\Log\LogLevel;
use yii\helpers\ArrayHelper;
use yii\log\Logger;

class GelfTargetTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testConfiguration()
    {
        $config = [
            'transport' => HttpTransport::class,
            'host' => '127.0.0.1',
            'port' => '12345',
            'path' => 'testpath',
            'sslOptions' => new SslOptions(),
            'version' => '1.1',
            'appName' => 'testappname'
        ];

        $target = \Yii::createObject(
            ArrayHelper::merge(
                [
                    'class' => GelfTarget::class
                ],
                $config
            )
        );

        self::assertEquals(HttpTransport::class, $target->transport);
        self::assertEquals('127.0.0.1', $target->host);
        self::assertEquals('12345', $target->port);
        self::assertEquals('testpath', $target->path);
        self::assertEquals(SslOptions::class, get_class($target->sslOptions));
        self::assertEquals('1.1', $target->version);
        self::assertEquals('testappname', $target->appName);
    }

    public function transportClassDataProvider()
    {
        return [
            'http transport correct' => [
                ['transport' => HttpTransport::class],
                ['exceptionClass' => null]
            ],
            'udp transport correct' => [
                ['transport' => UdpTransport::class],
                ['exceptionClass' => null]
            ],
            'unknown transport with InvalidTransportException' => [
                ['transport' => 'unknown transport'],
                ['exceptionClass' => InvalidTransportException::class]
            ],

        ];
    }

    /**
     * @dataProvider transportClassDataProvider
     */
    public function testTransport($attrs, $haveException) {
        $transportClass = $attrs['transport'];

        $target = \Yii::createObject([
            'class' => GelfTarget::class,
            'transport' => $transportClass
        ]);

        $refl = new \ReflectionClass(get_class($target));
        $method = $refl->getMethod('getTransport');
        $method->setAccessible(true);

        try {
            /** @var HttpTransport $transport */
            $transport = $method->invoke($target);

            self::assertEquals($transportClass, get_class($transport));
        } catch (\Throwable $t) {
            if ($haveException) {
                self::assertEquals(get_class($t), $haveException['exceptionClass']);
            }
        }
    }

    public function testShortMessageFromGelfMessage()
    {
        list($target, $method) = $this->createCreateMessageMethod();

        /** @var Message $message */
        $message = $method->invoke($target, [
            'this is short message', Logger::LEVEL_INFO, null, null
        ]);

        self::assertEquals('this is short message', $message->getShortMessage());
    }

    public function testLogCategoryFromGelfMessage()
    {
        list($target, $method) = $this->createCreateMessageMethod();

        /** @var Message $message */
        $message = $method->invoke($target, [
            '', Logger::LEVEL_INFO, null, null
        ]);

        self::assertEquals(LogLevel::INFO, $message->getLevel());
    }

    public function testTimestampFromGelfMessage()
    {
        list($target, $method) = $this->createCreateMessageMethod();

        $ts = time();
        /** @var Message $message */
        $message = $method->invoke($target, [
            '', Logger::LEVEL_INFO, null, $ts
        ]);

        self::assertEquals($ts, $message->getTimestamp());
    }

    public function testVersionFromGelfMessage()
    {
        list($target, $method) = $this->createCreateMessageMethod([
            'version' => '2.0'
        ]);

        /** @var Message $message */
        $message = $method->invoke($target, [
            '', Logger::LEVEL_INFO, null, null
        ]);

        self::assertEquals('2.0', $message->getVersion());
    }

    public function testAppNameFromGelfMessage()
    {
        list($target, $method) = $this->createCreateMessageMethod([
            'appName' => 'testapp'
        ]);

        /** @var Message $message */
        $message = $method->invoke($target, [
            '', Logger::LEVEL_INFO, null, null
        ]);

        self::assertEquals('testapp', $message->getHost());
    }

    public function testAppNameAutoFromGelfMessage()
    {
        list($target, $method) = $this->createCreateMessageMethod();

        /** @var Message $message */
        $message = $method->invoke($target, [
            '', Logger::LEVEL_INFO, null, null
        ]);

        self::assertEquals(\Yii::$app->id, $message->getHost());
    }

    public function testAdditionalDataFromGelfMessage()
    {
        list($target, $method) = $this->createCreateMessageMethod();

        /** @var Message $message */
        $message = $method->invoke($target, [
            [
                '_correctFieldName1' => 'value1',
                '_correctFieldName2' => 'value2'
            ], Logger::LEVEL_INFO, null, null
        ]);

        self::assertEquals('value1', $message->getAdditional('_correctFieldName1'));
        self::assertEquals('value2', $message->getAdditional('_correctFieldName2'));
    }

    public function testExceptionDataFromGelfMessage()
    {
        list($target, $method) = $this->createCreateMessageMethod();

        $ex = new \Exception('Test message');

        /** @var Message $message */
        $message = $method->invoke($target, [
            $ex, Logger::LEVEL_INFO, null, null
        ]);

        self::assertEquals('Exception ' . get_class($ex) . ' Test message', $message->getShortMessage());
    }

    public function testErrorDataFromGelfMessage()
    {
        list($target, $method) = $this->createCreateMessageMethod();

        $ex = new \Error('Test message');

        /** @var Message $message */
        $message = $method->invoke($target, [
            $ex, Logger::LEVEL_INFO, null, null
        ]);

        self::assertEquals('Error ' . get_class($ex) . ' Test message', $message->getShortMessage());
    }

    protected function createCreateMessageMethod($config = null)
    {
        if ($config) {
            $target = \Yii::createObject(ArrayHelper::merge($config, ['class' => GelfTarget::class]));
        } else {
            $target = \Yii::createObject(GelfTarget::class);
        }

        $refl = new \ReflectionClass(get_class($target));
        $method = $refl->getMethod('createMessage');
        $method->setAccessible(true);
        return array($target, $method);
    }

    public function testMessageGenerator()
    {
        $target = \Yii::createObject([
            'class' => GelfTarget::class,
            'messages' => [
                'test message',
                new \Exception(),
                new \Error(),
                [
                    '_testAdditionalField'
                ]
            ]
        ]);
        $refl = new \ReflectionClass(get_class($target));
        $method = $refl->getMethod('messageGeneratorExtractor');
        $method->setAccessible(true);

        $counter = 0;
        foreach ($method->invoke($target) as $message) {
            $counter++;

            switch ($counter) {
                case 1:
                    self::assertEquals('test message', $message);
                    break;
                case 2:
                    self::assertEquals(\Exception::class, get_class($message));
                    break;
                case 3:
                    self::assertEquals(\Error::class, get_class($message));
                    break;
                case 4:
                    self::assertTrue(is_array($message));
                    break;
                default:
                    throw new \Exception();
            }
        }
    }
}
