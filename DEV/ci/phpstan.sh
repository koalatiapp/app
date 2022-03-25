addedFiles=$(git diff --diff-filter=d --cached --name-only ":!tests" | grep ".php")

if [ -z "$addedFiles" ];
then
	php -d memory_limit=-1 vendor/bin/phpstan analyse -n
else
	php -d memory_limit=-1 vendor/bin/phpstan analyse -n $addedFiles
fi
