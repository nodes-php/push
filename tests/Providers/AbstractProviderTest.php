<?php

use Nodes\Push\Exceptions\ApplicationNotFoundException;
use Nodes\Push\Exceptions\ConfigErrorException;
use Nodes\Push\Exceptions\InvalidArgumentException;
use Nodes\Push\Providers\AbstractProvider;
use Nodes\Push\ServiceProvider;

class AbstractProviderTest extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    public function testSetAliasError()
    {
        $abstractProvider = $this->getProvider();
        $this->expectException(\Throwable::class);
        $abstractProvider->setAlias(['channel']);
    }

    public function testSetAliasSuccess()
    {
        $abstractProvider = $this->getProvider();
        $abstractProvider->setAlias('alias');

        $aliases = $abstractProvider->getAliases();
        $this->assertSame('alias', $aliases[0]);
    }

    public function testSetAliasesFail()
    {
        $abstractProvider = $this->getProvider();
        $this->expectException(\Throwable::class);
        $abstractProvider->setAliases([$abstractProvider]);
    }

    public function testSetAliasesSuccess()
    {
        $abstractProvider = $this->getProvider();
        $abstractProvider->setAliases(['alias', 1, 1.21]);

        $aliases = $abstractProvider->getAliases();
        $this->assertSame('alias', $aliases[0]);
        $this->assertSame('1', $aliases[1]);
        $this->assertSame('1.21', $aliases[2]);
    }

    public function testIosContentAvailableError()
    {
        $abstractProvider = $this->getProvider();
        $this->expectException(\Throwable::class);
        $abstractProvider->setIosContentAvailable([]);
    }

    public function testIosContentAvailableSuccess()
    {
        $abstractProvider = $this->getProvider();
        $abstractProvider->setIosContentAvailable(true);

        $this->assertSame(true, $abstractProvider->isIosContentAvailable());

        $abstractProvider->setIosContentAvailable(false);
        $this->assertSame(false, $abstractProvider->isIosContentAvailable());
    }

    public function testSetSoundError()
    {
        $abstractProvider = $this->getProvider();
        $this->expectException(\Throwable::class);
        $abstractProvider->setSound(['sound']);
    }

    public function testRemoveSoundSuccess()
    {
        $abstractProvider = $this->getProvider();
        $abstractProvider->setSound('sound');

        $this->assertSame('sound', $abstractProvider->getSound());

        $abstractProvider->removeSound();
        $this->assertSame(null, $abstractProvider->getSound());
    }

    public function testSetSoundSuccess()
    {
        $abstractProvider = $this->getProvider();
        $abstractProvider->setSound('sound');

        $this->assertSame('sound', $abstractProvider->getSound());
    }

    public function testSetIOSBadgeError()
    {
        $urbanAirshipV3 = $this->getProvider();
        $this->expectException(InvalidArgumentException::class);
        $urbanAirshipV3->setIOSBadge([]);

        $this->expectException(InvalidArgumentException::class);
        $urbanAirshipV3->setIOSBadge($urbanAirshipV3);
    }

    /**
     * @dataProvider setIOSBadgeSuccessProviderSuccess
     */
    public function testSetIOSBadgeSuccess($a, $b, $expect)
    {
        $urbanAirshipV3 = $this->getProvider();
        $urbanAirshipV3->setIOSBadge($a);
        $this->assertSame($a, $urbanAirshipV3->getIOSBadge());
    }

    public function setIOSBadgeSuccessProviderSuccess()
    {
        return [
            [1, 0, true],
            ['1', 0, true],
            ['auto', 0, true],
        ];
    }

    public function testSetExtraErrorObject()
    {
        $abstractProvider = $this->getProvider();
        $this->expectException(InvalidArgumentException::class);
        $extra = [
            'key' => [
                'key2' => $abstractProvider,
            ],
        ];

        $abstractProvider->setExtra($extra);
    }

    public function testSetExtraErrorArray()
    {
        $abstractProvider = $this->getProvider();
        $this->expectException(InvalidArgumentException::class);
        $extra = [
            'key' => [
                'key2' => 'value',
            ],
        ];

        $abstractProvider->setExtra($extra);
    }

    public function testSetExtra()
    {
        $extra = [
            'key' => uniqid(),
        ];

        $abstractProvider = $this->getProvider();
        $abstractProvider->setExtra($extra);

        $this->assertSame($extra, $abstractProvider->getExtra());
    }

    public function testSetMessageError()
    {
        $abstractProvider = $this->getProvider();
        $this->expectException(\Throwable::class);
        $abstractProvider->setMessage(['channel']);
    }

    public function testSetMessageSuccess()
    {
        $abstractProvider = $this->getProvider();
        $abstractProvider->setMessage('message');

        $this->assertSame('message', $abstractProvider->getMessage());
    }

    public function testSetChannelError()
    {
        $abstractProvider = $this->getProvider();
        $this->expectException(\Throwable::class);
        $abstractProvider->setChannel(['channel']);
    }

    public function testSetChannelSuccess()
    {
        $abstractProvider = $this->getProvider();
        $abstractProvider->setChannel('channel');

        $channels = $abstractProvider->getChannels();
        $this->assertSame('channel', $channels[0]);
    }

    public function testSetChannelsFail()
    {
        $abstractProvider = $this->getProvider();
        $this->expectException(\Throwable::class);
        $abstractProvider->setChannels([$abstractProvider]);
    }

    public function testSetChannelsSuccess()
    {
        $abstractProvider = $this->getProvider();
        $abstractProvider->setChannels(['channel', 1, 1.21]);

        $channels = $abstractProvider->getChannels();
        $this->assertSame('channel', $channels[0]);
        $this->assertSame('1', $channels[1]);
        $this->assertSame('1.21', $channels[2]);
    }

    public function testSetApplicationError()
    {
        $abstractProvider = $this->getProvider();
        $this->expectException(ApplicationNotFoundException::class);
        $abstractProvider->setAppGroup('default-app-group-not-found');
    }

    public function testSetApplicationSuccess()
    {
        $appGroup = 'default-app-group';
        $abstractProvider = $this->getProvider();
        $abstractProvider->setAppGroup($appGroup);
        $this->assertSame($appGroup, $abstractProvider->getAppGroup());
    }

    public function testInitProvideDefaultAppGroupDoesNotExist()
    {
        $this->expectException(ApplicationNotFoundException::class);
        new AbstractProviderTester([
            'default-app-group' => 'default-app-group-not-found',
            'app-groups'        => [
                'default-app-group' => [
                    'app-1' => [
                        'app_key'       => 'app-key',
                        'app_secret'    => 'app-secret',
                        'master_secret' => 'master-secret',
                    ],
                ],
            ],
        ]);
    }

    public function testInitProvideAppGroupIsNotAnArray()
    {
        $this->expectException(ConfigErrorException::class);
        new AbstractProviderTester([
            'default-app-group' => 'default-app-group',
            'app-groups'        => 'string',
        ]);
    }

    public function testInitProvideEmptyAppGroup()
    {
        $this->expectException(ConfigErrorException::class);
        new AbstractProviderTester([
            'default-app-group' => 'default-app-group',
        ]);
    }

    public function testInitProviderSuccess()
    {
        $abstractProvider = $this->getProvider();
        $this->assertInstanceOf(AbstractProviderTester::class, $abstractProvider);
    }

    public function testInitProvideNullDefaultAppGroup()
    {
        $this->expectException(ConfigErrorException::class);
        new AbstractProviderTester([
            'default-app-group' => null,
            'app-groups'        => [
                'default-app-group' => [
                    'app-1' => [
                        'app_key'       => 'app-key',
                        'app_secret'    => 'app-secret',
                        'master_secret' => 'master-secret',
                    ],
                ],
            ],
        ]);
    }

    public function testInitProvideEmptyDefaultAppGroup()
    {
        $this->expectException(ConfigErrorException::class);
        new AbstractProviderTester([
            'app-groups' => [
                'default-app-group' => [
                    'app-1' => [
                        'app_key'       => 'app-key',
                        'app_secret'    => 'app-secret',
                        'master_secret' => 'master-secret',
                    ],
                ],
            ],
        ]);
    }

    public function testInitProvideDefaultAppGroupIsNotAString()
    {
        $this->expectException(ConfigErrorException::class);
        new AbstractProviderTester([
            'default-app-group' => [
                'Not a string',
            ],
            'app-groups'        => [
                'default-app-group' => [
                    'app-1' => [
                        'app_key'       => 'app-key',
                        'app_secret'    => 'app-secret',
                        'master_secret' => 'master-secret',
                    ],
                ],
            ],
        ]);
    }

    private function getProvider()
    {
        return new AbstractProviderTester([
            'default-app-group' => 'default-app-group',
            'app-groups'        => [
                'default-app-group' => [
                    'app-1' => [
                        'app_key'       => 'app-key',
                        'app_secret'    => 'app-secret',
                        'master_secret' => 'master-secret',
                    ],
                ],
            ],
        ]);
    }
}

class AbstractProviderTester extends AbstractProvider
{
    public function send() : array
    {
        throw new \Exception('Feature not supported', 500);
    }

    public function sendAsync() : array
    {
        throw new \Exception('Feature not supported', 500);
    }

    public function getRequestData() : array
    {
        throw new \Exception('Feature not supported', 500);
    }
}
