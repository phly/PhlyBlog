<?php

namespace PhlyBlogTest;

use InvalidArgumentException;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\EventsCapableInterface;
use PhlyBlog\CompilerFactory;
use PHPUnit\Framework\MockObject\MockObject;
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

    public function prepareContainerRetrievalExpectations(MockObject $container, array $expectations): void
    {
        $arguments = [];
        $services  = [];
        foreach ($expectations as $name => $service) {
            $arguments[] = [$name];
            $services[]  = $service;
        }

        $container
            ->expects($this->exactly(count($expectations)))
            ->method('get')
            ->withConsecutive(...$arguments)
            ->willReturnOnConsecutiveCalls(...$services);
    }

    /**
     * @dataProvider defaultConfigurationProvider
     */
    public function testFactoryUsesDefaultsWhenNoConfigurationPresent(
        bool $hasConfig,
        ?array $config
    ): void {
        $containerServices = [];

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn($hasConfig);

        if ($hasConfig) {
            $containerServices['config'] = $config;
        }

        $factory  = new CompilerFactory();

        $this->prepareContainerRetrievalExpectations($container, $containerServices);
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

        $containerServices = [
            'config' => [
                'blog' => [
                    'posts_path' => __DIR__,
                ],
            ],
            EventManagerInterface::class => $this->createMock(EventManagerInterface::class),
        ];

        $this->prepareContainerRetrievalExpectations($container, $containerServices);

        $factory  = new CompilerFactory();

        $this->assertInstanceOf(EventsCapableInterface::class, $factory($container));
    }
}
