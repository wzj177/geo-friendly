# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial project setup with composer.json
- Basic project structure and documentation
- CLI entry point script

## [1.0.0] - 2024-03-25

### Added

#### Core Features
- Complete GEO (Generative Engine Optimization) file generation system
- Support for multiple AI-friendly file formats (robots.txt, llms.txt, sitemap.xml, docs.json, ai-index.json, schema.json)
- Flexible configuration system with YAML and array-based config support
- CLI tool for easy integration and automation

#### Generators
- **RobotsTxtGenerator**: Generates robots.txt with sitemap reference
- **LlmsTxtGenerator**: Creates LLM-optimized content summaries
- **LlmsFullTxtGenerator**: Generates comprehensive LLM training data
- **SitemapGenerator**: Creates standards-compliant XML sitemaps
- **DocsJsonGenerator**: Produces structured documentation index
- **AiIndexGenerator**: Generates AI-optimized content index
- **SchemaGenerator**: Creates Schema.org structured data

#### AI-Enhanced Features
- **AiLlmsTxtGenerator**: AI-powered llms.txt generation with OpenAI integration
- **AiContentEnhancer**: Content enhancement using AI models
- **AiSchemaGenerator**: Dynamic schema generation with AI assistance
- OpenAI configuration support for AI features

#### CLI Commands
- `geo init`: Initialize configuration file
- `geo generate`: Generate all GEO files
- `geo check`: Check GEO score and audit
- `geo report`: Generate detailed audit report

#### Platform Integrations
- WordPress integration plugin
- Shopify integration app
- Laravel service provider
- Symfony bundle

#### Development Tools
- PHPUnit configuration with coverage reporting
- PHPStan static analysis configuration
- Test fixtures and integration tests
- Comprehensive documentation

#### Documentation
- Complete README with installation and usage instructions
- Chinese README (README.zh-CN.md)
- Contributing guidelines
- Security policy
- Code of conduct
- License information

#### Quality Assurance
- Test suite with unit, integration, and feature tests
- GitHub Actions CI/CD pipeline
- PHP 7.4, 8.0, 8.1, 8.2, 8.3, and 8.4 support
- Code coverage with HTML and text reports
- Static analysis with PHPStan

### Configuration
- YAML-based configuration (`geofriendly.yml`)
- Array-based configuration for programmatic use
- Generator-specific enable/disable options
- Custom output and content directory support
- Site metadata configuration (name, URL, description)
- OpenAI API configuration for AI features

### Testing
- Integration tests for complete generation workflow
- Test fixtures with sample content and expected outputs
- Coverage thresholds configured (50% low, 80% high)
- CI/CD pipeline with multi-version PHP testing

### Developer Experience
- PSR-4 autoloading
- Type-safe code with strict types
- Comprehensive inline documentation
- Example code and usage patterns
- Clear separation of concerns

### Security
- Input validation and sanitization
- Secure file handling
- Security audit workflow in CI
- Vulnerability reporting process

[Unreleased]: https://github.com/wzj177/geo-friendly/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/wzj177/geo-friendly/releases/tag/v1.0.0
