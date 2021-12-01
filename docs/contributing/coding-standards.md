# Coding standards, linting and error detection

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

## PHP
- [PHP Mess Detector](https://phpmd.org/): `DEV/ci/phpmd.sh`
- [PHP Stan](https://phpstan.org/): `DEV/ci/phpstan.sh`
- [PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer): `DEV/ci/php-cs-fixer.sh`

## Javascript
- [ESLint](https://eslint.org/): `DEV/ci/eslint.sh`

## CSS
- [stylelint](https://stylelint.io/): `DEV/ci/stylelint.sh`

## Twig
- [Twigcs](https://github.com/friendsoftwig/twigcs): `DEV/ci/twigcs.sh`
