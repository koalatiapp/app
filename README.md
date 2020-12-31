# Koalati Application

![CI](https://github.com/koalatiapp/app/workflows/CI/badge.svg)

This is the official repository for the Koalati web application, available at [app.koalati.com](https://app.koalati.com).

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/)
2. Run `docker-compose --env-file .env.local up` (Symfony uses the .env.local file, and considers .env to be the template)
3. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
