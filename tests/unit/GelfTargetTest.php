<?php

namespace kdjonua\grayii\tests;

use Gelf\Message;
use Gelf\PublisherInterface;
use kdjonua\grayii\GelfTarget;
use yii\helpers\ArrayHelper;
use yii\log\Logger;

class GelfTargetTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function versionProvider() {
        return [
            'GELF message version 1.0' => ['1.0'],
            'GELF message version 1.1' => ['1.1']
        ];
    }

    /**
     * @dataProvider versionProvider
     */
    public function testValidateSimpleMessage($version)
    {
        /** @var GelfTarget $target */
        list($target, $method) = $this->createCreateMessageMethod([
            'version' => $version
        ]);

        /** @var Message $message */
        $message = $method->invoke($target, [
            'this is short message', Logger::LEVEL_INFO, null, null
        ]);

        $validator = $target->getMessageValidator();

        $reason = "";
        $validateStatus = $validator->validate($message, $reason);
        self::assertTrue($validateStatus, $reason);
    }

    /**
     * @dataProvider versionProvider
     */
    public function testValidateExceptionMessage($version)
    {
        /** @var GelfTarget $target */
        list($target, $method) = $this->createCreateMessageMethod([
            'version' => $version
        ]);

        /** @var Message $message */
        $message = $method->invoke($target, [
            new \Exception('Test message exception'), Logger::LEVEL_INFO, null, null
        ]);

        $validator = $target->getMessageValidator();

        $reason = "";
        $validateStatus = $validator->validate($message, $reason);
        self::assertTrue($validateStatus, $reason);

        self::assertEquals('Exception ' . \Exception::class . ' Test message exception', $message->getShortMessage());
        self::assertTrue(is_string($message->getFullMessage()));
        self::assertNotNull($message->getFile());
        self::assertNotNull($message->getLine());
    }

    /**
     * @dataProvider versionProvider
     */
    public function testValidateCombinedMessage($version)
    {
        /** @var GelfTarget $target */
        list($target, $method) = $this->createCreateMessageMethod([
            'version' => $version
        ]);

        /** @var Message $message */
        $message = $method->invoke($target, [
            [
                'short' => 'Test short message',
                'full' => 'Test full message',
                '_one' => 1,
                '_two' => 2
            ], Logger::LEVEL_INFO, null, null
        ]);

        $validator = $target->getMessageValidator();

        $reason = "";
        $validateStatus = $validator->validate($message, $reason);
        self::assertTrue($validateStatus, $reason);

        self::assertEquals('Test short message', $message->getShortMessage());
        self::assertEquals('Test full message', $message->getFullMessage());
    }

    /**
     * @dataProvider versionProvider
     */
    public function testValidateCombinedMessageWithSimplifiedShort($version)
    {
        /** @var GelfTarget $target */
        list($target, $method) = $this->createCreateMessageMethod([
            'version' => $version
        ]);

        /** @var Message $message */
        $message = $method->invoke($target, [
            [
                'Test short message',
                'full' => 'Test full message',
                '_one' => 1,
                '_two' => 2
            ], Logger::LEVEL_INFO, null, null
        ]);

        $validator = $target->getMessageValidator();

        $reason = "";
        $validateStatus = $validator->validate($message, $reason);
        self::assertTrue($validateStatus, $reason);

        self::assertEquals('Test short message', $message->getShortMessage());
        self::assertEquals('Test full message', $message->getFullMessage());
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

    public function testGetPublisher() {
        $target = \Yii::createObject(GelfTarget::class);
        self::assertInstanceOf(PublisherInterface::class, $target->getPublisher());
    }
}
