# Contributing to REIA Utilities Bundle

We welcome contributions to the REIA Utilities Bundle! This document provides guidelines for contributing to the project.

## 🤝 How to Contribute

### Reporting Issues
- Use the GitHub Issues page to report bugs
- Include WordPress version, PHP version, and plugin version
- Provide detailed steps to reproduce the issue
- Include any error messages or screenshots

### Suggesting Features
- Open an issue with the "enhancement" label
- Clearly describe the feature and its benefits
- Explain how it fits with the plugin's goals

### Code Contributions
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Test thoroughly
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## 🛠️ Development Setup

### Prerequisites
- WordPress development environment
- PHP 7.4 or higher
- Git

### Local Development
1. Clone the repository to your WordPress plugins directory:
   ```bash
   cd /path/to/wordpress/wp-content/plugins/
   git clone https://github.com/DavidEngland/reia-utilities-bundle.git
   ```

2. Activate the plugin in WordPress admin

3. Navigate to Tools > REIA Utilities to test

### Code Standards
- Follow WordPress Coding Standards
- Use proper PHP DocBlocks
- Sanitize and validate all inputs
- Use WordPress APIs when available
- Test on multiple WordPress versions

### Testing
- Test all utilities with dry run mode first
- Verify security checks work properly
- Test with different WordPress configurations
- Ensure compatibility with popular plugins

## 📝 Coding Guidelines

### PHP
- Use WordPress coding standards
- Proper error handling and validation
- Follow PSR-4 autoloading standards where applicable
- Use WordPress nonces for security

### JavaScript
- Use modern ES6+ syntax
- Follow WordPress JavaScript standards
- Include proper error handling
- Use jQuery safely with noConflict mode

### CSS
- Use BEM methodology for class naming
- Mobile-first responsive design
- Follow WordPress admin design patterns

## 🧪 Testing Checklist

Before submitting a pull request:

- [ ] Code follows WordPress standards
- [ ] All utilities work in dry run mode
- [ ] Security features function properly
- [ ] No PHP errors or warnings
- [ ] JavaScript console is error-free
- [ ] Responsive design works on mobile
- [ ] Plugin activation/deactivation works
- [ ] Database operations are safe and reversible
- [ ] Backup functionality works correctly

## 📚 Documentation

When contributing:
- Update README.md if adding features
- Update code comments and DocBlocks
- Include inline documentation for complex logic
- Update the WordPress readme.txt if needed

## 🏷️ Release Process

1. Update version numbers in main plugin file and readme.txt
2. Update changelog in readme.txt
3. Create GitHub release with changelog
4. Submit to WordPress.org repository (if applicable)

## 🆘 Getting Help

- Open an issue for questions
- Join WordPress Slack #plugins channel
- Contact: Real Estate Intelligence Agency

## 📄 License

By contributing, you agree that your contributions will be licensed under the MIT License.
