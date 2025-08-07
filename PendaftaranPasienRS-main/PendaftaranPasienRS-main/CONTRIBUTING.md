# Contributing to RS Sehat Selalu

🎉 **Thank you for considering contributing to RS Sehat Selalu!** 🎉

We welcome contributions from everyone. By participating in this project, you agree to abide by our code of conduct.

## 🚀 Quick Start

### Getting Started

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/YOUR-USERNAME/PendaftaranPasienRS.git
   cd PendaftaranPasienRS
   ```
3. **Create a branch** for your changes:
   ```bash
   git checkout -b feature/your-feature-name
   ```

### Setting Up Development Environment

1. **Install dependencies:**
   ```bash
   # For PHP dependencies (if using Composer)
   composer install
   
   # For frontend dependencies (if using npm)
   npm install
   ```

2. **Set up database:**
   ```bash
   mysql -u root -p -e "CREATE DATABASE rs_sehat_selalu_dev;"
   mysql -u root -p rs_sehat_selalu_dev < rs_sehat_selalu.sql
   mysql -u root -p rs_sehat_selalu_dev < simple_upgrade.sql
   ```

3. **Configure environment:**
   ```php
   // Copy and edit configuration
   cp config.example.php config.php
   ```

---

## 🔧 Development Guidelines

### Code Style

#### PHP Code Style
- Follow **PSR-12** coding standards
- Use **camelCase** for variables and functions
- Use **PascalCase** for classes
- Add **DocBlocks** for all functions and classes

```php
<?php
/**
 * Calculate patient age from birth date
 *
 * @param string $birthDate Date in Y-m-d format
 * @return int Age in years
 */
function calculateAge(string $birthDate): int
{
    $today = new DateTime();
    $birth = new DateTime($birthDate);
    return $today->diff($birth)->y;
}
```

#### HTML/CSS Guidelines
- Use **semantic HTML5** elements
- Follow **BEM methodology** for CSS classes
- Ensure **accessibility** (WCAG 2.1 AA)
- Make designs **responsive** (mobile-first)

```html
<!-- Good -->
<article class="patient-card patient-card--active">
    <header class="patient-card__header">
        <h2 class="patient-card__name">John Doe</h2>
    </header>
</article>
```

#### JavaScript Guidelines
- Use **ES6+** features
- Follow **Airbnb JavaScript Style Guide**
- Add **JSDoc** comments
- Use **async/await** for promises

```javascript
/**
 * Fetch patient data from API
 * @param {number} patientId - The patient ID
 * @returns {Promise<Object>} Patient data
 */
async function fetchPatientData(patientId) {
    try {
        const response = await fetch(`/api/patients/${patientId}`);
        return await response.json();
    } catch (error) {
        console.error('Failed to fetch patient data:', error);
        throw error;
    }
}
```

### Security Guidelines

🔒 **Security is our top priority**

- **Always sanitize input** using prepared statements
- **Validate all data** on both client and server side
- **Use CSRF tokens** for all forms
- **Never expose sensitive data** in error messages
- **Log security events** appropriately

```php
// Good - Using prepared statements
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND active = 1");
$stmt->bind_param("s", $username);
$stmt->execute();

// Bad - Vulnerable to SQL injection
$query = "SELECT * FROM users WHERE username = '$username'";
```

---

## 🐛 Reporting Issues

### Bug Reports

When reporting bugs, please include:

- **Clear description** of the issue
- **Steps to reproduce** the bug
- **Expected vs actual behavior**
- **System information** (PHP version, MySQL version, OS)
- **Screenshots** (if applicable)

**Template:**
```markdown
**Bug Description:**
A clear description of what the bug is.

**To Reproduce:**
1. Go to '...'
2. Click on '...'
3. See error

**Expected Behavior:**
What you expected to happen.

**Screenshots:**
If applicable, add screenshots.

**System Information:**
- OS: [e.g. Windows 10, Ubuntu 20.04]
- PHP Version: [e.g. 8.1.0]
- MySQL Version: [e.g. 8.0.28]
- Browser: [e.g. Chrome 95.0]
```

### Security Issues

🚨 **For security vulnerabilities:**

- **DO NOT** open a public issue
- **Email us directly** at: security@rs-sehat-selalu.com
- Include detailed steps to reproduce
- We'll respond within 24 hours

---

## 💡 Feature Requests

We love new ideas! When suggesting features:

- **Check existing issues** to avoid duplicates
- **Describe the problem** you're trying to solve
- **Explain your proposed solution**
- **Consider implementation complexity**

**Template:**
```markdown
**Feature Description:**
A clear description of the feature you'd like to see.

**Problem it Solves:**
What problem does this feature address?

**Proposed Solution:**
How would you like this to work?

**Alternatives Considered:**
Any alternative solutions you've thought about?
```

---

## 🔄 Pull Request Process

### Before Submitting

1. **Update documentation** if needed
2. **Add tests** for new functionality
3. **Run existing tests** to ensure nothing breaks
4. **Follow coding standards**
5. **Update CHANGELOG.md** if applicable

### Pull Request Guidelines

- **One feature per PR** - Keep changes focused
- **Write clear commit messages** following conventional commits
- **Add descriptive PR title and description**
- **Link related issues** using keywords (fixes #123)
- **Request review** from maintainers

### Commit Message Format

```
type(scope): description

[optional body]

[optional footer]
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```bash
feat(auth): add two-factor authentication
fix(login): resolve session timeout issue
docs(readme): update installation instructions
```

---

## 🧪 Testing

### Running Tests

```bash
# Run PHP tests
php tests/run_tests.php

# Run security tests
php tests/security_tests.php

# Check code style
php -l *.php
```

### Writing Tests

- **Write tests** for new features
- **Update tests** when modifying existing code
- **Include edge cases** in your tests
- **Test security features** thoroughly

Example test:
```php
<?php
class AuthTest extends PHPUnit\Framework\TestCase
{
    public function testLoginWithValidCredentials()
    {
        $auth = new AuthManager();
        $result = $auth->login('testuser', 'validpassword');
        
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('testuser', $result->getUsername());
    }
    
    public function testLoginWithInvalidCredentials()
    {
        $auth = new AuthManager();
        $result = $auth->login('testuser', 'wrongpassword');
        
        $this->assertFalse($result->isSuccess());
        $this->assertNull($result->getUsername());
    }
}
```

---

## 📝 Documentation

### Types of Documentation

- **Code comments** - Explain complex logic
- **API documentation** - Document all endpoints
- **User guides** - Help users understand features
- **Developer docs** - Setup and contribution guides

### Documentation Style

- **Write clearly** and concisely
- **Use examples** to illustrate concepts
- **Keep it up-to-date** with code changes
- **Include screenshots** for UI features

---

## 🏷️ Issue Labels

We use labels to categorize issues:

### Type Labels
- `bug` - Something isn't working
- `enhancement` - New feature or request
- `documentation` - Improvements or additions to docs
- `security` - Security-related issues

### Priority Labels
- `priority: high` - Critical issues
- `priority: medium` - Important issues
- `priority: low` - Nice to have

### Status Labels
- `status: needs-review` - Awaiting review
- `status: in-progress` - Currently being worked on
- `status: blocked` - Blocked by dependencies

---

## 🌟 Recognition

Contributors will be recognized in:

- **README.md** contributors section
- **CHANGELOG.md** for significant contributions
- **GitHub releases** notes
- **Project documentation**

---

## 📞 Getting Help

Need help? Reach out to us:

- **Discord**: Join our [community chat](https://discord.gg/your-invite)
- **Email**: Send us an email at dev@rs-sehat-selalu.com
- **GitHub Discussions**: Use our [discussions board](https://github.com/whympxx/PendaftaranPasienRS/discussions)

---

## 🤝 Code of Conduct

### Our Pledge

We pledge to make participation in our project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, personal appearance, race, religion, or sexual identity and orientation.

### Our Standards

**Positive behavior includes:**
- Being respectful and inclusive
- Gracefully accepting constructive criticism
- Focusing on what's best for the community
- Showing empathy towards other community members

**Unacceptable behavior includes:**
- Harassment of any kind
- Trolling, insulting, or derogatory comments
- Publishing others' private information
- Any conduct which could reasonably be considered inappropriate

### Enforcement

Instances of abusive, harassing, or otherwise unacceptable behavior may be reported by contacting us at conduct@rs-sehat-selalu.com. All complaints will be reviewed and investigated.

---

## 🎉 Thank You!

Thank you for taking the time to contribute to RS Sehat Selalu! Your efforts help make healthcare technology better for everyone.

**Happy Coding!** 🚀

---

*This contributing guide is adapted from the best practices of open source projects and is continuously updated based on community feedback.*
