# Koalati Application

![CI](https://github.com/koalatiapp/app/workflows/CI/badge.svg)

This is the official repository for the Koalati web application, available at [app.koalati.com](https://app.koalati.com).

---

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/)
2. Run `docker-compose --env-file .env.local up` (Symfony uses the .env.local file, and considers .env to be the template)
3. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)

---

## Coding standards, linting and error detection

There are multiple code linting and error detection scripts and dependencies configured for the different parts of the application.
Each of them have a corresponding executable file in the `DEV/ci/` directory to simplify their usage.

We recommend you use the Git pre-commit hook provided in `DEV/hooks/pre-commit` to run these tools before every commit. 
This will ensure your code changes are always up to snuff, and it will give you that nice green success message that bumps up 
your self-esteem when your code passes all the checks.

To use the pre-commit, navigate to the repository and run the following command:
```bash
ln DEV/hooks/pre-commit .git/hooks/pre-commit
```

If you prefer to use the tools manually, here is the list of tools available along with their executable file:

### PHP
- [PHP Mess Detector](https://phpmd.org/): `DEV/ci/phpmd.sh`
- [PHP Stan](https://phpstan.org/): `DEV/ci/phpstan.sh`
- [PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer): `DEV/ci/php-cs-fixer.sh`

### Javascript
- [ESLint](https://eslint.org/): `DEV/ci/eslint.sh`

### CSS
- [stylelint](https://stylelint.io/): `DEV/ci/stylelint.sh`

### Twig
- [Twigcs](https://github.com/friendsoftwig/twigcs): `DEV/ci/twigcs.sh` _(this is not currently used in the precommit, but [Twig's coding standards](https://twig.symfony.com/doc/3.x/coding_standards.html) should be respected nonetheless)_

---

## Testing

All tests are located in the `tests/` directory at the root of the project. 

### Frontend-only tests
Frontend-only tests are located in `tests/Frontend`, and are ran with [@web/test-runner](https://modern-web.dev/docs/test-runner/overview/) 
along with [Mocha](https://mochajs.org/) and [Chai](https://www.chaijs.com/).

Make sure you have the dependencies installed locally (by running `npm install` or `npm ci`), and run the following command:

```bash
npm test:unit
```

### Backend-only tests
Backend tests are located in `tests/Backend`, and are ran using [PHPUnit](https://phpunit.de/).

Although the unit tests can be run locally, the functional tests should run inside the docker container as they require a working web server with a test database.

Before you run any of the backend functional tests, you'll want to set up your testing database using the provided Doctrine fixtures. 
Either switch your `.env` configurations to match the testing database, sacrifice your development database, or prepend your environment variables to the `doctrine:fixtures` like so:

```bash
MYSQL_DATABASE=test bin/console doctrine:fixtures:load -n
```

Once you're all set up, you can use the following command to run unit tests within the docker container if you are using the recommended docker-composer setup.

```bash
docker-compose exec -T php ./bin/phpunit
```

For more information about PHPUnit and the available configurations, visit [PHPUnit's documentation](https://phpunit.de/documentation.html).

### End-to-end tests
End-to-end tests are located in `tests/Full`, and are ran with [Playwright test runner](https://github.com/microsoft/playwright-test).
Make sure you have the dependencies installed locally (by running `npm install` or `npm ci`), and run the following command:

```bash
npm test:e2e
```

---

## Contributing

If you would like to contribute to the project, that's awesome!
Take a look at the issues, and take a stab at it!

To get a better understanding of the Koalati ecosystem, take a look at [Koalati's Contributor documentation](https://docs.koalati.com/).

---

## Code of conduct

Every contributor must respect our [code of conduct](https://docs.koalati.com/code-of-conduct).
As a TL;DR: just don't be an asshole. If you respect other people and watch your language, you're ~99% of the way there.

---

## License

Koalati is an open-source platform that is distributed with an MIT license.
For more information about Koalati's licensing, visit [the Licensing page](https://docs.koalati.com/docs/licensing) of our contributor documentation.
