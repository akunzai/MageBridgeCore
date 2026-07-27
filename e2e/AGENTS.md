# E2E Testing — Agent Guidelines

Playwright against real Joomla + OpenMage in the Docker stack (@.devcontainer/AGENTS.md).

## Quick Commands

```bash
cd e2e && aube install
aubr test                                        # all projects
aubr test:ui                                     # interactive UI
aubr test:headed                                 # headed browser
aubr test --project=joomla-admin
aubr test --project=joomla-site
aubr test --project=openmage-admin
aubr test -- tests/joomla/admin/config.spec.ts   # single file
```

## Joomla 5 Selectors (a11y-first)

| UI element | Selector |
|------------|----------|
| Tab | `getByRole('tab', { name: 'API' })` |
| Toolbar button | `getByRole('button', { name: 'Save', exact: true })` |
| Table header link | `getByRole('link', { name: 'Label', exact: true })` |
| Admin form | `page.locator('#adminForm')` |
| Alert | `getByRole('alert')` |

## Layout

```text
e2e/
├── playwright.config.ts
├── fixtures/{auth,openmage}.setup.ts
└── tests/
    ├── helpers/
    ├── joomla/{admin,site}/
    └── openmage/admin/
```

Gold-standard specs: @e2e/tests/joomla/admin/config.spec.ts, @e2e/tests/joomla/admin/home.spec.ts
