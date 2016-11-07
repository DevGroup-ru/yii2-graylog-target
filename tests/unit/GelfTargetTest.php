<?php

namespace kdjonua\grayii\tests;

use Gelf\Message;
use Gelf\Transport\HttpTransport;
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

    public function testConfiguration()
    {
        $config = [
            'transport' => HttpTransport::class
        ];

        /** @var GelfTarget $target */
        $target = \Yii::createObject(
            ArrayHelper::merge(
                [
                    'class' => GelfTarget::class
                ],
                $config
            )
        );

        $transport = $target->getTransport();
        self::assertInstanceOf(HttpTransport::class, $transport);
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
