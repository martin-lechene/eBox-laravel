# Contributing

Thanks for your interest in improving `martin-lechene/ebox-laravel`.

## Setup

```bash
git clone https://github.com/martin-lechene/eBox-laravel.git
cd eBox-laravel
composer install
```

Requirements: PHP 8.2+ with `ext-openssl` and `ext-json`.

## Checks to run before opening a pull request

```bash
composer test           # PHPUnit (Orchestra Testbench)
composer format-check   # Laravel Pint, no changes expected
```

Use `composer format` to apply Pint fixes.

## Guidelines

- Open an issue first for large changes.
- Add or update tests for any behavior change.
- Use conventional commit messages (`feat:`, `fix:`, `docs:`, `chore:` ...).
- Update `CHANGELOG.md` under `## [Unreleased]`.
- Never commit secrets or real certificates. Test fixtures must be throw-away material.
- Security issues: see [SECURITY.md](SECURITY.md), do not open a public issue.