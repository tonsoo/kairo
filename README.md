<div align="center">
<h1>
<code>Kairo</code><br>
Personal Time Tracking
</h1>

Clean, self-hosted time tracking for work hours, shifts, and hour balance.
</div>

Kairo helps you track your own working time without spreadsheets, manual math, or heavy HR software.

It is built for individuals who want a simple place to start a shift, stop a shift, review the day, and understand whether their hours are balanced.

## What You Can Do

- Track work sessions with start and stop times
- Handle split shifts and workdays with breaks
- Compare recorded hours against your expected schedule
- See daily totals, missing hours, extra hours, and balance
- Review past records in a clean history view
- Export your time entries when needed
- Use the app in English or Brazilian Portuguese
- Switch between light, dark, and system themes inside the app

Kairo is not built for employee monitoring or team surveillance. It is meant to be your personal time workspace.

## How It Works

1. Configure your usual work schedule.
2. Start a shift when you begin working.
3. Stop it when you finish or take a break.
4. Continue later if your day has multiple work periods.
5. Review your dashboard to see your totals and balance.

Kairo compares the time you actually recorded with the schedule you configured, so you can quickly tell whether you are ahead, behind, or on track.

## Self-Hosting

Kairo is a Laravel application using Inertia, Vue, MySQL, queues, scheduled tasks, and server-side rendering.

For production, the recommended setup is to run separate processes for:

| Process | Purpose |
| --- | --- |
| Web | Serves the Laravel application |
| SSR | Renders Inertia pages server-side |
| Queue | Processes background jobs |
| Scheduler | Runs Laravel scheduled commands |
| Database | Stores users, shifts, schedules, and settings |

These processes can use the same application image. You do not need separate builds for web, SSR, queue, and scheduler.

### Local Setup With Docker
The repository includes Docker files for local development and simple self-hosted deployments.

1. Copy the environment file:

    ```bash
    cp .env.example .env
    ```

2. Update the important values in `.env`:

    ```dotenv
    APP_NAME=Kairo
    APP_URL=http://localhost:19941
    
    DB_DATABASE=kairo
    DB_USERNAME=kairo
    DB_PASSWORD=secret
    ```

    If you plan to use passkeys, also set:
    
    ```dotenv
    PASSKEYS_USER_HANDLE_SECRET=
    ```

3. Build and start the containers:

    ```bash
    docker compose up -d --build
    ```

4. Install dependencies and initialize the app:

    ```bash
    docker compose exec -T php sh -lc 'cd /app && composer install'
    docker compose exec -T php sh -lc 'cd /app && npm install'
    docker compose exec -T php sh -lc 'cd /app && php artisan key:generate'
    docker compose exec -T php sh -lc 'cd /app && php artisan migrate --force'
    docker compose exec -T php sh -lc 'cd /app && npm run build'
    ```

5. Open the app:

    ```text
    http://localhost:19941
    ```

### Production Notes

Before exposing Kairo publicly:

- Set `APP_URL` to your real HTTPS URL
- Configure a real mail provider for account and password emails
- Run migrations during deployments
- Run the queue worker continuously
- Run the scheduler every minute
- Build frontend assets as part of your deploy
- Keep dependencies and your server packages updated
- Put the app behind HTTPS
- Avoid exposing the database, cache, or internal services publicly

A typical deployment can run the same image with different commands for each process:

| Service | Example command |
| --- | --- |
| Web | PHP/Laravel web server command used by your image |
| SSR | `php artisan inertia:start-ssr` |
| Queue | `php artisan queue:work --tries=3` |
| Scheduler | `php artisan schedule:work` |

Adjust the web command to match your Docker image or hosting platform.

## Development

Run the test suite:

```bash
docker compose exec -T php sh -lc 'cd /app && php artisan test --compact'
```

Format PHP changes:

```bash
docker compose exec -T php sh -lc 'cd /app && vendor/bin/pint --dirty'
```

Build frontend assets:

```bash
docker compose exec -T php sh -lc 'cd /app && npm run build'
```

## Contributing

Issues and pull requests are welcome.

For pull requests, please keep changes focused, include relevant tests when behavior changes, and make sure the app still builds before submitting.

## License

Kairo is open-source software released under the [MIT License](LICENSE).
