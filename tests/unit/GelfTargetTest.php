<?php

namespace devgroup\grayii\tests;

use devgroup\grayii\GelfTarget;
use Gelf\Message;
use Gelf\PublisherInterface;
use yii\helpers\ArrayHelper;
use yii\log\Logger;

class GelfTargetTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    private function isPhpVersion7OrMore() {
        return PHP_MAJOR_VERSION >= 7;
    }

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
        $this->assertTrue($validateStatus, $reason);
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
        $this->assertTrue($validateStatus, $reason);

        $this->assertEquals('Exception ' . \Exception::class . ' Test message exception', $message->getShortMessage());
        $this->assertTrue(is_string($message->getFullMessage()));
        $this->assertNotNull($message->getFile());
        $this->assertNotNull($message->getLine());
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
        $this->assertTrue($validateStatus, $reason);

        $this->assertEquals('Test short message', $message->getShortMessage());
        $this->assertEquals('Test full message', $message->getFullMessage());
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
        $this->assertTrue($validateStatus, $reason);

        $this->assertEquals('Test short message', $message->getShortMessage());
        $this->assertEquals('Test full message', $message->getFullMessage());
    }


    public function testAppNameAutoFromGelfMessage()
    {
        list($target, $method) = $this->createCreateMessageMethod();

        /** @var Message $message */
        $message = $method->invoke($target, [
            '', Logger::LEVEL_INFO, null, null
        ]);

        $this->assertEquals(\Yii::$app->id, $message->getHost());
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

        $this->assertEquals('value1', $message->getShortMessage()); //First value goes to message
        $this->assertEquals('value2', $message->getAdditional('__correctFieldName2'));
    }

    public function testExceptionDataFromGelfMessage()
    {
        list($target, $method) = $this->createCreateMessageMethod();

        $ex = new \Exception('Test message');

        /** @var Message $message */
        $message = $method->invoke($target, [
            $ex, Logger::LEVEL_INFO, null, null
        ]);

        $this->assertEquals('Exception ' . get_class($ex) . ' Test message', $message->getShortMessage());
    }

    public function testErrorDataFromGelfMessage()
    {
        if (!$this->isPhpVersion7OrMore()) {
            return;
        }

        list($target, $method) = $this->createCreateMessageMethod();

        $ex = new \Error('Test message');

        /** @var Message $message */
        $message = $method->invoke($target, [
            $ex, Logger::LEVEL_INFO, null, null
        ]);

        $this->assertEquals('Error ' . get_class($ex) . ' Test message', $message->getShortMessage());
    }

    /**
     * @dataProvider versionProvider
     */
    public function testArrayMessageWithoutRequiredDataGetsFirstElementAsMessage($version)
    {
        /** @var GelfTarget $target */
        list($target, $method) = $this->createCreateMessageMethod([
            'version' => $version
        ]);

        /** @var Message $message */
        $message = $method->invoke($target, [
            [
                'message' => 'Test short message',
            ], Logger::LEVEL_INFO, null, null
        ]);

        $validator = $target->getMessageValidator();

        $reason = "";
        $validateStatus = $validator->validate($message, $reason);
        $this->assertTrue($validateStatus, $reason);

        $this->assertEquals('Test short message', $message->getShortMessage());
    }

    /**
     * @dataProvider versionProvider
     */
    public function testArrayMessageWithoutRequiredDataWithAdditionalData($version)
    {
        /** @var GelfTarget $target */
        list($target, $method) = $this->createCreateMessageMethod([
            'version' => $version
        ]);

        /** @var Message $message */
        $message = $method->invoke($target, [
            [
                'message' => 'Test short message',
                'additionaldata1' => 'test1',
                'additionaldata2' => 'test2'
            ], Logger::LEVEL_INFO, null, null
        ]);

        $validator = $target->getMessageValidator();

        $reason = "";
        $validateStatus = $validator->validate($message, $reason);
        $this->assertTrue($validateStatus, $reason);

        $this->assertEquals('Test short message', $message->getShortMessage());
        $this->assertEquals('test1', $message->getAdditional('_additionaldata1'));
        $this->assertEquals('test2', $message->getAdditional('_additionaldata2'));
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
        $versionMoreTheSix = $this->isPhpVersion7OrMore();

        $target = \Yii::createObject([
            'class' => GelfTarget::class,
            'messages' => [
                'test message',
                new \Exception(),
                $versionMoreTheSix ? new \Error() : "",
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
                    $this->assertEquals('test message', $message);
                    break;
                case 2:
                    $this->assertEquals(\Exception::class, get_class($message));
                    break;
                case 3:
                    if ($versionMoreTheSix) {
                        $this->assertEquals(\Error::class, get_class($message));
                    }
                    break;
                case 4:
                    $this->assertTrue(is_array($message));
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
