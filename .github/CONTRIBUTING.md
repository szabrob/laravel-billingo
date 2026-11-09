# Contribution Guide

Thank you for considering contributing to Laravel Billingo! Please review the following guidelines before submitting a pull request.

For significant changes, please open an issue first so we can discuss the approach.

## Process

1. Fork the project
2. Create a new branch
3. Code, test, commit, and push
4. Open a pull request detailing your changes

## Guidelines

- Ensure the coding style passes by running `composer lint`.
- Send a coherent commit history, making sure each commit in your pull request is meaningful.
- You may need to [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.
- Please remember that we follow [SemVer](http://semver.org/).

## Setup

Clone your fork, then install the dev dependencies:

```bash
composer install
```

## Lint

Lint your code:

```bash
composer lint
```

## Tests

Run all tests:

```bash
composer test
```

## Integration Tests

`tests/Integration` calls the real Billingo API. These tests are not part of `composer test` and are skipped unless an API key is set. Use a **dedicated Billingo test account**, never a production account: the tests create and delete partners, products, spendings and drafts.

```bash
BILLINGO_SANDBOX_API_KEY=your-test-account-key composer test:integration
```

Tests that issue real documents (invoices, proformas, modification invoices and receipts) only run when you opt in, and only if the account has no NAV connection. Issued documents cannot be deleted, so the tests cancel them afterwards:

```bash
BILLINGO_SANDBOX_API_KEY=your-test-account-key BILLINGO_SANDBOX_ISSUE_DOCUMENTS=true composer test:integration
```

| Variable | Purpose |
| --- | --- |
| `BILLINGO_SANDBOX_API_KEY` | API key of the Billingo test account; without it every integration test is skipped |
| `BILLINGO_SANDBOX_ISSUE_DOCUMENTS` | Set to `true` to issue invoices, proformas and receipts |
| `BILLINGO_SANDBOX_EMAIL` | Optional; the e-mail sending test sends an invoice to this address |
| `BILLINGO_SANDBOX_BASE_URL` | Optional; defaults to `https://api.billingo.hu/v3` |

On GitHub, run the **integration** workflow manually (Actions → integration → Run workflow) after adding the `BILLINGO_SANDBOX_API_KEY` repository secret and, optionally, the `BILLINGO_SANDBOX_EMAIL` repository variable.

## Workbench Playground

The workbench ships a browser UI for calling every resource method against the real Billingo API. Copy the environment file, set your API key, then start the server:

```bash
cp workbench/.env.example workbench/.env # then set BILLINGO_API_KEY
composer serve
```

Open the printed URL, pick a method, fill in its arguments, and inspect the result and the raw HTTP requests. Methods marked `write` or `danger` change real data in the Billingo account, so use a test account.
