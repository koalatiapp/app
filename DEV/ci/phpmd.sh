addedFiles=`echo "$(git diff --diff-filter=d --cached --name-only)" | paste -s -d, /dev/stdin`

if [ -z "$addedFiles" ];
then
	vendor/phpmd/phpmd/src/bin/phpmd src text .phpmd.xml --exclude DEV/ci/*.php,tests/**/*.php
else
	vendor/phpmd/phpmd/src/bin/phpmd $addedFiles text .phpmd.xml --exclude DEV/ci/*.php,tests/**/*.php
fi
