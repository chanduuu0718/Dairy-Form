# Production Security Checklist

Before deploying PM Dairy Farm:

- Copy `config/config.example.php` to `config/config.php` and provide real environment values.
- Never commit `config/config.php`, `.env` files, API keys, payment secrets, or JWT secrets.
- Use HTTPS in production. Authentication cookies become Secure automatically when served over HTTPS.
- Configure the Cashfree production App ID and Secret Key only through environment/server configuration.
- Configure Cashfree webhook delivery to `/api/payment/?action=webhook` and keep webhook signature verification enabled.
- Run database setup from the CLI only with `ADMIN_PHONE`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` environment variables.
- Do not expose `database/`, `config/`, or `uploads/` PHP execution to the public web.
- Run the GitHub PHP syntax workflow before merging changes.
- Replace any legacy/default administrator credentials in existing installations before going live.
- Review inventory reservation and subscription automation before accepting production orders.
