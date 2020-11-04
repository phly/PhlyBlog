<?php

namespace PhlyBlogTest;

use InvalidArgumentException;
use Laminas\EventManager\EventManagerAwareInterface;
use PhlyBlog\CompilerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class CompilerFactoryTest extends TestCase
{
    public function defaultConfigurationProvider(): iterable
    {
        yield 'no config service' => [false, null];
        yield 'empty config service' => [true, []];
        yield 'empty blog config' => [true, ['blog' => []]];
        yield 'empty posts_path config' => [true, ['blog' => ['posts_path' => null]]];
    }

    /**
     * @dataProvider defaultConfigurationProvider
     */
    public function testFactoryUsesDefaultsWhenNoConfigurationPresent(
        bool $hasConfig,
        ?array $config
    ): void {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn($hasConfig);

        if ($hasConfig) {
            $container
                ->expects($this->once())
                ->method('get')
                ->with('config')
                ->willReturn($config);
        }

        $factory  = new CompilerFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(getcwd() . '/data/blog');
        $factory($container);
    }

    public function testFactoryUsesConfigurationToProduceCompilerWhenPresent(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $container
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn([
                'blog' => [
                    'posts_path' => __DIR__,
                ],
            ]);

        $factory  = new CompilerFactory();

        $this->assertInstanceOf(EventManagerAwareInterface::class, $factory($container));
    }
}
