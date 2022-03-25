# Testing

All tests are located in the `tests/` directory at the root of the project. 

The best way to run the unit, functional and E2E tests is to run `DEV/ci/run-tests.php`. 
This script will automatically switch your environment to testing mode and set up your testing database using the configuration in `.env.test`.
It will then run the tests, and revert back your environment to its original state afterwards.

You can use the `--type` option to specify which tests you would like to run. 
Ex.: `DEV/ci/run-tests.php --type=frontend`, or `DEV/ci/run-tests.php --type=backend,unit`.

By default, every type available of test will be executed (`unit`, `functional` for both `backend` and `frontend`, as well as end-to-end tests: `e2e`). 

For more information, run `DEV/ci/run-tests.php --help`.

## Frontend-only tests
Frontend-only tests are located in `tests/Frontend`, and are ran with [@web/test-runner](https://modern-web.dev/docs/test-runner/overview/) 
along with [Mocha](https://mochajs.org/) and [Chai](https://www.chaijs.com/).

Make sure you have the dependencies installed locally (by running `npm install` or `npm ci`).

## Backend-only tests
Backend tests are located in `tests/Backend`, and are ran using [PHPUnit](https://phpunit.de/).

## End-to-end tests
End-to-end tests are located in `tests/Full`, and are ran with [Playwright test runner](https://github.com/microsoft/playwright-test).
Make sure you have the dependencies installed locally (by running `npm install` or `npm ci`), and run the following command:

### E2E testing utilities
A few utility functions exist to make E2E testing easier.  
They are located in the `tests/Full/utilities.ts` file.

To use them, simply import them as you would any other module:
```js
import { login, createProject, deleteProject } from "../utilities";
```

The available utility functions are:
- `login`
  - page: `Page`
  - email: `string` (default: `"name@email.com"`)
  - password: `string` (default: `"123456"`)
- `createProject`
  - page: `Page`
  - name: `string` (default: `"Sample website"`)
  - url: `string` (default: `"https://sample.koalati.com"`)
- `deleteProject`
  - page: `Page`
  - projectId: `string`

---

## When and how to add tests
Whenever a new page or feature is added, tests should also be added. 
Here are some guidelines about adding tests when contributing.

- All services and web components should be unit tested _(as close to 100% as reasonably possible)_.
- Any new route should be added to the appropriate smoke tests (in `tests/Backend/Functional/`):
  - Public routes are covered by the `PublicAvailabityTest`.
  - Private app routes are covered by the `AppAvailabityTest`.
  - API routes are covered by the `ApiAvailabityTest`.
  - Routes that are restricted to certain plans are covered by `PlanLimitationsTest` (in addition to their regular test in `AppAvailabityTest` or `ApiAvailabityTest`).
- Functional tests (either end-to-end or integration tests) should cover all of the main features and flows of the platform. _(as close to 100% of the most common scenarios as reasonably possible)_.
- Whenever a bug is reported and fixed, a new test of the most appropriate type should be added to cover this case.
- Any additional test that adds value to the project is welcome (ex.: E2E test covering edge cases).
