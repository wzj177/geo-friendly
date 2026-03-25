# Security Policy

## Supported Versions

Currently, only the latest version of Geo-Friendly receives security updates.

| Version | Supported          |
|---------|--------------------|
| 1.0.x   | :white_check_mark: Yes |
| < 1.0   | :x: No              |

## Reporting a Vulnerability

We take the security of Geo-Friendly seriously. If you discover a security vulnerability, please follow these guidelines to report it.

### How to Report

**Do not create public GitHub issues for security vulnerabilities.**

Instead, please send an email to our security team at:

- **Email**: [security@example.com](mailto:security@example.com)
- **PGP Key**: Available at [https://example.com/pgp-key](https://example.com/pgp-key)

### What to Include

Please include as much of the following information in your report:

1. **Description**: A detailed description of the vulnerability
2. **Impact**: The potential impact of the vulnerability
3. **Steps to Reproduce**: Clear, step-by-step instructions to reproduce the issue
4. **Proof of Concept**: Code or screenshots demonstrating the vulnerability (if applicable)
5. **Affected Versions**: Which versions are affected
6. **Proposed Fix**: Any suggested fixes or workarounds (if known)

### What Happens Next

Once you submit a vulnerability report:

1. **Confirmation**: We will acknowledge receipt of your report within 48 hours
2. **Investigation**: We will investigate the vulnerability and determine its severity
3. **Resolution**: We will work on a fix and coordinate a release schedule
4. **Disclosure**: We will notify you when the fix is released
5. **Credit**: With your permission, we will credit you in the security advisory

### Response Timeframes

- **Initial Response**: Within 48 hours
- **Detailed Assessment**: Within 7 days
- **Fix Timeline**: Depends on severity, typically:
  - Critical: 48 hours
  - High: 7 days
  - Medium: 14 days
  - Low: 30 days

## Security Best Practices

### For Users

To keep your installation secure:

1. **Keep Updated**: Always use the latest version
2. **Monitor Releases**: Watch for security advisories
3. **Review Permissions**: Ensure proper file permissions
4. **Secure API Keys**: Never commit OpenAI API keys or other secrets
5. **Scan Dependencies**: Regularly run `composer audit`

### Configuration Security

When configuring Geo-Friendly:

1. **Environment Variables**: Use environment variables for sensitive data
2. **File Permissions**: Set appropriate permissions on generated files
3. **Output Directory**: Ensure the output directory is not publicly writable
4. **API Keys**: Rotate API keys regularly

Example secure configuration:

```yaml
# geofriendly.yml
site_name: "My Site"
site_url: "https://example.com"
output_dir: "./public"

# Use environment variables for sensitive data
openai:
    api_key: "%env(OPENAI_API_KEY)%"
    model: "gpt-4"
```

### Deployment Security

When deploying:

1. **Public Access**: Generated files are intended to be public
2. **Private Data**: Ensure no private data is included in generated content
3. **Access Control**: Consider access controls for sensitive endpoints
4. **HTTPS**: Always serve files over HTTPS
5. **CORS**: Configure CORS headers appropriately

## Security Features

Geo-Friendly includes several security features:

- Input validation and sanitization
- Secure file handling with proper permissions
- No arbitrary code execution in generators
- Type-safe code to prevent type confusion vulnerabilities
- Regular dependency audits via CI/CD

## Dependency Security

We monitor and update dependencies regularly:

- **Composer Audit**: Run `composer audit` in CI/CD pipeline
- **Automated Updates**: Dependabot configured for dependency updates
- **Review Process**: All dependency updates are reviewed before merging

## Security Audits

Periodic security audits are conducted:

- **Code Review**: Regular peer reviews of security-critical code
- **Static Analysis**: PHPStan analysis on all code
- **Dependency Scanning**: Automated scanning of dependencies
- **Penetration Testing**: Occasional penetration testing of the package

## Vulnerability Disclosure Policy

When vulnerabilities are fixed:

1. **Security Advisory**: A security advisory will be published
2. **Patch Release**: A patch release will be made available
3. **Disclosure**: The vulnerability will be disclosed after a fix is available
4. **Credits**: Credits will be given to the reporter (with permission)

### Security Advisories

Security advisories are published at:
- GitHub Security Advisories
- Release notes
- CHANGELOG.md

## Security Contact Information

- **Security Email**: [security@example.com](mailto:security@example.com)
- **General Email**: [contact@example.com](mailto:contact@example.com)
- **PGP Key**: [https://example.com/pgp-key](https://example.com/pgp-key)
- **Security Policy**: This document

## Related Resources

- [GitHub Security Best Practices](https://docs.github.com/en/code-security)
- [OWASP PHP Security](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Security_Cheat_Sheet.html)
- [Composer Security](https://getcomposer.org/doc/06-security.md)

## License

This security policy is provided under the MIT License. For the full license, see [LICENSE.md](LICENSE.md).

---

Thank you for helping keep Geo-Friendly secure!
