# Public learning pages release reference

Use the general [YARUB deployment runbook](DEPLOYMENT.md) for deployment commands, including Docker Artisan, host asset builds, migrations, and both guarded content importers.

Release `f66bf35` requires an asset build and route/view cache clearing. It changes no dependency lockfiles or migrations and requires no content reimport.

If the server also receives the preceding Arabic Skills feature (`5ec63bc`) for the first time, follow the runbook's migration and Arabic Skills import sections. The earlier deployment chat records successful Qudrat migrations; this public-page release does not require repeating them.

See [public page details and verification](PUBLIC_LEARNING_PAGES.md).
