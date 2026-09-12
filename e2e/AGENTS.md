# E2E Testing — Agent Guidelines

Playwright against real Joomla + OpenMage in the Docker stack (`.devcontainer/AGENTS.md`).

## Quick Commands

```bash
cd e2e && aube install
aube test                                        # all projects
aube run test:ui                                 # interactive UI
aube run test:headed                             # headed browser
aube test -- --project=joomla-admin
aube test -- --project=joomla-site
aube test -- --project=openmage-admin
aube test -- tests/joomla/admin/config.spec.ts   # single file
```

## Joomla 5 Selectors (a11y-first)

| UI element | Selector |
| --- | --- |
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

Gold-standard specs: `e2e/tests/joomla/admin/config.spec.ts`, `e2e/tests/joomla/admin/home.spec.ts`

## Config Save and catalog fixtures

Admin Configuration **Save writes the whole form to the shared Docker DB**. `fullyParallel` workers in other projects see that mutation. Assert rendered booleans against `Defaults.php` (and `install.sh` overrides such as `enable_sso`), not against XML `default="0"`.

Chelsea Tee (`chelsea-tee-720.html`) is configurable with MAP — do not treat add-to-cart success as a golden path. Magento sends an empty checkout to the cart; stop guest shop-flow at store / product / cart.
