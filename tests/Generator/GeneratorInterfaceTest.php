<?php

declare(strict_types=1);

namespace GeoFriendly\Tests\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\GeneratorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GeneratorInterface::class)]
class GeneratorInterfaceTest extends TestCase
{
    public function testInterfaceCanBeImplemented(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://example.com',
        ]);

        $generator = new class implements GeneratorInterface {
            public function generate(GeofriendlyConfig $config): string
            {
                return 'generated content';
            }

            public function getFilename(): string
            {
                return 'test.txt';
            }
        };

        $this->assertInstanceOf(GeneratorInterface::class, $generator);
        $this->assertSame('generated content', $generator->generate($config));
        $this->assertSame('test.txt', $generator->getFilename());
    }

    public function testInterfaceHasCorrectMethods(): void
    {
        $reflection = new \ReflectionClass(GeneratorInterface::class);

        $this->assertTrue($reflection->isInterface());
        $this->assertTrue($reflection->hasMethod('generate'));
        $this->assertTrue($reflection->hasMethod('getFilename'));

        $generateMethod = $reflection->getMethod('generate');
        $this->assertSame('generate', $generateMethod->getName());
        $this->assertSame(1, $generateMethod->getNumberOfParameters());
        $this->assertSame('string', $generateMethod->getReturnType()?->getName());

        $getFilenameMethod = $reflection->getMethod('getFilename');
        $this->assertSame('getFilename', $getFilenameMethod->getName());
        $this->assertSame(0, $getFilenameMethod->getNumberOfParameters());
        $this->assertSame('string', $getFilenameMethod->getReturnType()?->getName());
    }
}
