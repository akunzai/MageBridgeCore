# E2E Testing Agent Instructions

E2E tests use Playwright to test MageBridge in a real browser environment.

## Commands

```bash
cd e2e && pnpm install
pnpm test                                        # Run all tests
pnpm test:ui                                     # Interactive UI mode
pnpm test:headed                                 # Show browser execution
pnpm test --project=joomla-admin                 # Run only Joomla admin tests
pnpm test --project=joomla-site                  # Run only Joomla site tests
pnpm test --project=openmage-admin               # Run only OpenMage admin tests
pnpm test -- tests/joomla/admin/config.spec.ts   # Run specific test
```

## Joomla 5 Playwright Selectors

| UI Element | Recommended Selector |
|------------|---------------------|
| Tab | `getByRole('tab', { name: 'API' })` |
| Toolbar button | `getByRole('button', { name: 'Save', exact: true })` |
| Table header link | `getByRole('link', { name: 'Label', exact: true })` |
| Admin form | `page.locator('#adminForm')` |
| Error message | `getByRole('alert')` |

## Test File Structure

```
e2e/
├── playwright.config.ts
├── fixtures/
│   ├── auth.setup.ts               # Joomla admin authentication
│   └── openmage.setup.ts           # OpenMage admin authentication
└── tests/
    ├── helpers/                    # Shared test utilities
    │   └── index.ts
    ├── joomla/
    │   ├── admin/                  # Joomla admin tests
    │   └── site/                   # Joomla frontend tests
    └── openmage/
        └── admin/                  # OpenMage admin tests
```
