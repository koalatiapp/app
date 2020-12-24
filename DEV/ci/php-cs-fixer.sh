IFS='
'
CHANGED_FILES=$(git diff --name-only --diff-filter=ACMRTUXB HEAD~..HEAD)
if ! echo "${CHANGED_FILES}" | grep -qE "^(\\.php_cs(\\.dist)?|composer\\.lock)$"; then EXTRA_ARGS=$(printf -- '--path-mode=intersection\n--\n%s' "${CHANGED_FILES}"); else EXTRA_ARGS=''; fi
# TODO: Remove the "PHP_CS_FIXER_IGNORE_ENV=1" when PHP-CS-Fixer starts supporting PHP 8
PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --dry-run --stop-on-violation --using-cache=no ${EXTRA_ARGS}
