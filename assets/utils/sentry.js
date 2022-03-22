import * as Sentry from "@sentry/browser";

Sentry.init({
	dsn: "https://fa227bdc68d14d56a06247191a71db8b@o1000146.ingest.sentry.io/5959436",
	environment: window.location.host == "localhost" ? "test" : "prod",
});
