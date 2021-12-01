# Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/)
2. Run `docker-compose --env-file .env.local up` (Symfony uses the .env.local file, and considers .env to be the template)
3. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)

Once your environment is running, you'll likely want to look at some other documentation articles, especially:

- [Setting up your environment variables](docs/contributing/environment-variables.md)
- [Testing](docs/contributing/testing.md)
- [Coding standards, linting and error detection](docs/contributing/coding-standards.md)
