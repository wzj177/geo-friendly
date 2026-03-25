<?php

declare(strict_types=1);

namespace GeoFriendly\Tests\Integration;

use GeoFriendly\GeoFriendly;
use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\GeneratorInterface;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Integration test for the complete generation workflow.
 *
 * This test verifies that all generators work together correctly
 * to produce the expected output files.
 */
class GenerationTest extends TestCase
{
    private string $tempDir;
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/geo-friendly-test-' . uniqid();
        $this->fixturesDir = __DIR__ . '/../fixtures';

        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    /**
     * Test complete generation workflow with all generators enabled.
     */
    public function testCompleteGenerationWorkflow(): void
    {
        $config = [
            'site_name' => 'Example Site',
            'site_url' => 'https://example.com',
            'site_description' => 'A sample site for testing GEO file generation',
            'output_dir' => $this->tempDir,
            'content_dir' => $this->fixturesDir . '/content',
            'generators' => [
                'robotsTxt' => true,
                'llmsTxt' => true,
                'llmsFullTxt' => true,
                'sitemap' => true,
                'docsJson' => true,
                'aiIndex' => true,
                'schema' => true,
            ],
        ];

        $geo = new GeoFriendly($config);
        [$generated, $errors] = $geo->generate();

        // Assert no errors occurred
        $this->assertEmpty($errors, 'Generation should complete without errors');
        $this->assertNotEmpty($generated, 'At least one file should be generated');

        // Assert all expected files were generated
        $expectedFiles = [
            'robots.txt',
            'llms.txt',
            'llms-full.txt',
            'sitemap.xml',
            'docs.json',
            'ai-index.json',
            'schema.json',
        ];

        foreach ($expectedFiles as $filename) {
            $filepath = $this->tempDir . '/' . $filename;
            $this->assertFileExists(
                $filepath,
                sprintf('Expected file %s should be generated', $filename)
            );

            // Verify file is not empty
            $content = file_get_contents($filepath);
            $this->assertNotEmpty(
                $content,
                sprintf('Generated file %s should not be empty', $filename)
            );

            // Verify file is readable
            $this->assertIsReadable(
                $filepath,
                sprintf('Generated file %s should be readable', $filename)
            );
        }
    }

    /**
     * Test generation with selective generators enabled.
     */
    public function testSelectiveGeneration(): void
    {
        $config = [
            'site_name' => 'Example Site',
            'site_url' => 'https://example.com',
            'output_dir' => $this->tempDir,
            'generators' => [
                'robotsTxt' => true,
                'llmsTxt' => false,
                'llmsFullTxt' => false,
                'sitemap' => true,
                'docsJson' => false,
                'aiIndex' => false,
                'schema' => false,
            ],
        ];

        $geo = new GeoFriendly($config);
        [$generated, $errors] = $geo->generate();

        $this->assertEmpty($errors);
        $this->assertCount(2, $generated, 'Only 2 files should be generated');

        // Verify only enabled generators produced output
        $this->assertFileExists($this->tempDir . '/robots.txt');
        $this->assertFileExists($this->tempDir . '/sitemap.xml');
        $this->assertFileDoesNotExist($this->tempDir . '/llms.txt');
    }

    /**
     * Test robots.txt content structure.
     */
    public function testRobotsTxtContent(): void
    {
        $config = [
            'site_name' => 'Example Site',
            'site_url' => 'https://example.com',
            'output_dir' => $this->tempDir,
            'generators' => ['robotsTxt' => true],
        ];

        $geo = new GeoFriendly($config);
        $geo->generate();

        $content = file_get_contents($this->tempDir . '/robots.txt');

        // Verify basic structure
        $this->assertStringContainsString('User-agent:', $content);
        $this->assertStringContainsString('Sitemap:', $content);
        $this->assertStringContainsString('https://example.com/sitemap.xml', $content);
    }

    /**
     * Test llms.txt content structure.
     */
    public function testLlmsTxtContent(): void
    {
        $config = [
            'site_name' => 'Example Site',
            'site_url' => 'https://example.com',
            'site_description' => 'A sample site',
            'output_dir' => $this->tempDir,
            'content_dir' => $this->fixturesDir . '/content',
            'generators' => ['llmsTxt' => true],
        ];

        $geo = new GeoFriendly($config);
        $geo->generate();

        $content = file_get_contents($this->tempDir . '/llms.txt');

        // Verify content was aggregated
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('Sample Page', $content);
        $this->assertStringContainsString('About Our Company', $content);
    }

    /**
     * Test sitemap.xml content structure.
     */
    public function testSitemapXmlContent(): void
    {
        $config = [
            'site_name' => 'Example Site',
            'site_url' => 'https://example.com',
            'output_dir' => $this->tempDir,
            'content_dir' => $this->fixturesDir . '/content',
            'generators' => ['sitemap' => true],
        ];

        $geo = new GeoFriendly($config);
        $geo->generate();

        $content = file_get_contents($this->tempDir . '/sitemap.xml');

        // Verify XML structure
        $this->assertStringContainsString('<?xml', $content);
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('</urlset>', $content);
        $this->assertStringContainsString('<url>', $content);
        $this->assertStringContainsString('</url>', $content);

        // Verify it's valid XML
        $xml = simplexml_load_string($content);
        $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    }

    /**
     * Test docs.json content structure.
     */
    public function testDocsJsonContent(): void
    {
        $config = [
            'site_name' => 'Example Site',
            'site_url' => 'https://example.com',
            'site_description' => 'A sample site',
            'output_dir' => $this->tempDir,
            'content_dir' => $this->fixturesDir . '/content',
            'generators' => ['docsJson' => true],
        ];

        $geo = new GeoFriendly($config);
        $geo->generate();

        $content = file_get_contents($this->tempDir . '/docs.json');
        $data = json_decode($content, true);

        // Verify JSON structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('pages', $data);
        $this->assertIsArray($data['pages']);
        $this->assertNotEmpty($data['pages']);
    }

    /**
     * Test ai-index.json content structure.
     */
    public function testAiIndexJsonContent(): void
    {
        $config = [
            'site_name' => 'Example Site',
            'site_url' => 'https://example.com',
            'output_dir' => $this->tempDir,
            'content_dir' => $this->fixturesDir . '/content',
            'generators' => ['aiIndex' => true],
        ];

        $geo = new GeoFriendly($config);
        $geo->generate();

        $content = file_get_contents($this->tempDir . '/ai-index.json');
        $data = json_decode($content, true);

        // Verify JSON structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('metadata', $data);
        $this->assertArrayHasKey('content', $data);
        $this->assertArrayHasKey('version', $data['metadata']);
        $this->assertArrayHasKey('site_name', $data['metadata']);
    }

    /**
     * Test schema.json content structure.
     */
    public function testSchemaJsonContent(): void
    {
        $config = [
            'site_name' => 'Example Site',
            'site_url' => 'https://example.com',
            'site_description' => 'A sample site',
            'output_dir' => $this->tempDir,
            'generators' => ['schema' => true],
        ];

        $geo = new GeoFriendly($config);
        $geo->generate();

        $content = file_get_contents($this->tempDir . '/schema.json');
        $data = json_decode($content, true);

        // Verify JSON structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('@context', $data);
        $this->assertEquals('https://schema.org', $data['@context']);
        $this->assertArrayHasKey('@type', $data);
    }

    /**
     * Test custom generator registration.
     */
    public function testCustomGeneratorRegistration(): void
    {
        $config = [
            'site_name' => 'Example Site',
            'site_url' => 'https://example.com',
            'output_dir' => $this->tempDir,
        ];

        $geo = new GeoFriendly($config);

        // Register a custom generator
        $customGenerator = new class implements GeneratorInterface {
            public function generate(GeofriendlyConfig $config): string
            {
                return 'Custom content';
            }

            public function getFilename(): string
            {
                return 'custom.txt';
            }
        };

        $geo->registerGenerator('custom', $customGenerator);
        [$generated, $errors] = $geo->generate();

        $this->assertEmpty($errors);
        $this->assertContains('custom.txt', $generated);
        $this->assertFileExists($this->tempDir . '/custom.txt');
        $this->assertStringEqualsFile($this->tempDir . '/custom.txt', 'Custom content');
    }

    /**
     * Test output directory creation.
     */
    public function testOutputDirectoryCreation(): void
    {
        $nonExistentDir = $this->tempDir . '/nested/output/dir';

        $config = [
            'site_name' => 'Example Site',
            'site_url' => 'https://example.com',
            'output_dir' => $nonExistentDir,
            'generators' => ['robotsTxt' => true],
        ];

        $geo = new GeoFriendly($config);
        [$generated, $errors] = $geo->generate();

        $this->assertEmpty($errors);
        $this->assertDirectoryExists($nonExistentDir);
        $this->assertFileExists($nonExistentDir . '/robots.txt');
    }

    /**
     * Test configuration object retrieval.
     */
    public function testConfigRetrieval(): void
    {
        $config = [
            'site_name' => 'Test Site',
            'site_url' => 'https://test.com',
        ];

        $geo = new GeoFriendly($config);
        $retrievedConfig = $geo->getConfig();

        $this->assertInstanceOf(GeofriendlyConfig::class, $retrievedConfig);
        $this->assertEquals('Test Site', $retrievedConfig->siteName);
        $this->assertEquals('https://test.com', $retrievedConfig->siteUrl);
    }

    /**
     * Test generators retrieval.
     */
    public function testGeneratorsRetrieval(): void
    {
        $geo = new GeoFriendly();
        $generators = $geo->getGenerators();

        $this->assertIsArray($generators);
        $this->assertNotEmpty($generators);

        foreach ($generators as $name => $generator) {
            $this->assertInstanceOf(GeneratorInterface::class, $generator);
        }
    }

    /**
     * Remove a directory and all its contents.
     */
    private function removeDirectory(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }
}
