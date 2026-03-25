# Contributing to Geo-Friendly

Thank you for your interest in contributing to Geo-Friendly! This document provides guidelines and instructions for contributing to the project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Code Style Guidelines](#code-style-guidelines)
- [Submitting Changes](#submitting-changes)
- [Testing Guidelines](#testing-guidelines)
- [Documentation](#documentation)

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to [contact@example.com](mailto:contact@example.com).

## Getting Started

### Prerequisites

- PHP 7.4 or higher
- Composer 2.x
- Git
- A text editor or IDE (VS Code, PHPStorm, etc.)

### Recommended Tools

- PHPUnit for testing
- PHPStan for static analysis
- Xdebug for code coverage and debugging

## Development Setup

### 1. Fork and Clone

Fork the repository and clone your fork:

```bash
git clone https://github.com/YOUR_USERNAME/geo-friendly.git
cd geo-friendly
```

### 2. Install Dependencies

Install all dependencies including development dependencies:

```bash
composer install
```

### 3. Run Tests

Verify your setup by running the test suite:

```bash
composer test
```

### 4. Run Static Analysis

Check code quality with PHPStan:

```bash
composer analyse
```

## Code Style Guidelines

### PHP Standards

We follow PSR-12 coding standards with some additional guidelines:

#### Type Declarations

- All methods MUST have return type declarations
- All parameters MUST have type declarations where possible
- Use strict types (`declare(strict_types=1);`) in all PHP files

```php
<?php

declare(strict_types=1);

namespace GeoFriendly\Example;

class ExampleClass
{
    public function process(array $data): bool
    {
        // Implementation
        return true;
    }
}
```

#### Naming Conventions

- **Classes**: PascalCase (e.g., `GeoFriendlyGenerator`)
- **Methods**: camelCase (e.g., `generateContent`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `MAX_ITEMS`)
- **Variables**: camelCase (e.g., `$siteUrl`)

#### Documentation

All public methods MUST have PHPDoc blocks:

```php
/**
 * Generate GEO content for the given configuration.
 *
 * @param GeofriendlyConfig $config The configuration object
 * @return string The generated content
 * @throws InvalidArgumentException If configuration is invalid
 */
public function generate(GeofriendlyConfig $config): string
{
    // Implementation
}
```

#### Whitespace and Formatting

- Use 4 spaces for indentation (no tabs)
- No trailing whitespace
- One blank line between methods
- Opening braces on the same line for classes and methods

### Git Commit Messages

Follow conventional commit format:

```
type(scope): subject

body (optional)

footer (optional)
```

**Types**: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`

**Example**:
```
feat(generators): add support for custom schemas

This commit adds the ability to define custom schema types
in the configuration file.

Closes #123
```

## Submitting Changes

### 1. Create a Branch

Create a descriptive branch for your changes:

```bash
git checkout -b feature/my-new-feature
# or
git checkout -b fix/issue-123
```

### 2. Make Your Changes

- Write clean, well-documented code
- Follow the code style guidelines
- Add tests for new functionality
- Update documentation as needed

### 3. Test Your Changes

Run the full test suite:

```bash
composer test
```

Run static analysis:

```bash
composer analyse
```

### 4. Commit Your Changes

Write clear, descriptive commit messages:

```bash
git add .
git commit -m "feat(generators): add support for custom schemas"
```

### 5. Push to Your Fork

Push your branch to your fork:

```bash
git push origin feature/my-new-feature
```

### 6. Create a Pull Request

- Go to the original repository on GitHub
- Click "New Pull Request"
- Provide a clear description of your changes
- Link any related issues
- Ensure CI checks pass

### Pull Request Guidelines

- Keep PRs focused and small
- Provide clear descriptions of changes
- Link related issues
- Ensure all tests pass
- Update documentation
- Respond to review feedback

## Testing Guidelines

### Writing Tests

- Write tests for all new functionality
- Follow Arrange-Act-Assert (AAA) pattern
- Use descriptive test names

```php
public function test_it_generates_robots_txt_with_sitemap_reference(): void
{
    // Arrange
    $config = new GeofriendlyConfig(['site_url' => 'https://example.com']);
    $generator = new RobotsTxtGenerator();

    // Act
    $result = $generator->generate($config);

    // Assert
    $this->assertStringContainsString('Sitemap: https://example.com/sitemap.xml', $result);
}
```

### Test Organization

- **Unit Tests**: tests/Unit - Test individual classes in isolation
- **Integration Tests**: tests/Integration - Test multiple components together
- **Feature Tests**: tests/Feature - Test end-to-end functionality

### Coverage Requirements

- Aim for at least 80% code coverage
- Critical paths should have 100% coverage
- Add tests before fixing bugs (regression tests)

## Documentation

### Code Documentation

- PHPDoc blocks for all public methods
- Inline comments for complex logic
- Type hints for all parameters and return values

### User Documentation

- Update README.md for user-facing changes
- Add examples for new features
- Update CHANGELOG.md
- Keep Chinese README in sync

### API Documentation

- Document all public APIs
- Provide usage examples
- Note breaking changes clearly

## Development Workflow

### Feature Development

1. Create an issue to discuss the feature
2. Get feedback from maintainers
3. Create a feature branch
4. Implement the feature with tests
5. Update documentation
6. Submit a pull request

### Bug Fixes

1. Create an issue describing the bug
2. Write a test that reproduces the bug
3. Fix the bug
4. Ensure all tests pass
5. Submit a pull request

## Getting Help

- **Issues**: Use GitHub Issues for bug reports and feature requests
- **Discussions**: Use GitHub Discussions for questions and ideas
- **Email**: Contact [contact@example.com](mailto:contact@example.com) for private matters

## Recognition

Contributors will be recognized in:
- CONTRIBUTORS.md file
- Release notes for significant contributions
- Project documentation

Thank you for contributing to Geo-Friendly!
