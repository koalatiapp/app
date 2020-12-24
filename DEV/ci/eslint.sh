# Only run eslint if the public/js/ directory contains files
if [ -n "$(ls -A public/js/)" ]; then
	echo "Running ESLint on Javascript files..."
	node_modules/eslint/bin/eslint.js public/js/
fi

# Only run eslint if the templates directory contains files
if [ -n "$(ls -A templates/)" ]; then
	echo "Running ESLint on template files..."
	node_modules/eslint/bin/eslint.js --ext .html,.twig templates/
fi
