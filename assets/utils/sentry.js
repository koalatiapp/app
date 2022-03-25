import * as Sentry from "@sentry/browser";

Sentry.init({
	dsn: env.SENTRY_DSN,
	environment: env.APP_ENV,
});
