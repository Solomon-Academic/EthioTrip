# EthioTrip email setup (Gmail SMTP)

EthioTrip sends payment and booking notifications through **Gmail SMTP** using PHPMailer (PHP equivalent of Nodemailer + Gmail).

## 1. Enable 2-Step Verification

1. Open [Google Account → Security](https://myaccount.google.com/security)
2. Turn on **2-Step Verification** if it is not already enabled.

## 2. Create an App Password

1. Go to [App passwords](https://myaccount.google.com/apppasswords)
2. Select app: **Mail**, device: **Windows Computer** (or Other)
3. Click **Generate**
4. Copy the **16-character password** (no spaces)

## 3. Configure `.env`

Copy `.env.example` to `.env` and set:

```env
GMAIL_USER=workalemzame@gmail.com
GMAIL_APP_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_FROM_NAME=EthioTrip
MAIL_FROM_EMAIL=workalemzame@gmail.com
```

Use the App Password only — never your normal Gmail password.

## 4. Install PHP dependencies

From the `ethiotrip` folder:

```bash
composer install
```

On XAMPP (Windows) if `composer` is not in PATH:

```bash
c:\xampp\php\php.exe composer.phar install
```

(Run `php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"` and `php composer-setup.php` once if `composer.phar` is missing.)

## 5. Test

1. Log in as a user with a real email address.
2. Create a booking and complete payment.
3. As admin: **Payments** → **Approve payment**.
4. The customer should receive the confirmation email.

## Troubleshooting

| Issue | Fix |
|--------|-----|
| Authentication failed | Wrong App Password or 2FA not enabled |
| Could not connect | Allow outbound port **587** (STARTTLS) |
| Email skipped (placeholder) | User must register with a real email, not guest |
