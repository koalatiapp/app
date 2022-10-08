import * as Sentry from "@sentry/browser";

if (typeof env != "undefined" && env?.SENTRY_DSN) {
	Sentry.init({
		dsn: env.SENTRY_DSN,
		environment: env.APP_ENV,
	});
}
