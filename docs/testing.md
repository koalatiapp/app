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
