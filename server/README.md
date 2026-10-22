# Contact endpoint

`contact.php` receives the website contact form and sends it from `prismatic@gioia.email` to `team@prismatichq.com`. It uses PHP's built-in `mail()` function with `msmtp` as the authenticated SMTP transport. It has no application dependencies or secrets in the repository.

## 1. Mail server

The gioia.email mail server must allow `prismatic@gioia.email` to submit mail using authenticated SMTP. Collect these values from the server:

- SMTP hostname
- Submission port, normally 587
- Whether the connection uses STARTTLS, normally yes on port 587
- Username, normally `prismatic@gioia.email`
- Password

The gioia.email domain should already have working SPF and DKIM records for this mail server because the message will be delivered externally to Fastmail.

## 2. Install the API runtime

On the `api.gioia.dev` Ubuntu server, install PHP-FPM and the lightweight SMTP client with `sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get install -y php-fpm msmtp-mta ca-certificates`.

Create the deployment and configuration directories with `sudo install -d -m 755 -o prismatic-deploy -g www-data /var/www/api.gioia.dev/prismatic && sudo install -d -m 750 -o root -g www-data /etc/prismatic`.

## 3. Configure SMTP

Create the password file with `sudo install -m 640 -o root -g www-data /dev/null /etc/prismatic/smtp-password && sudoedit /etc/prismatic/smtp-password`, then paste only the password for `prismatic@gioia.email`.

Create the Prismatic msmtp configuration with `sudo install -m 600 -o www-data -g www-data /dev/null /etc/prismatic/msmtprc && sudoedit /etc/prismatic/msmtprc` and copy [msmtprc.example](./msmtprc.example). A file selected with msmtp's `--file` option is treated as a user configuration and must be owned by the sending user with no group permissions.

The configuration uses implicit TLS on port 465. The `msmtp-mta` package provides a sendmail-compatible command, and nginx selects this Prismatic-specific configuration for the contact endpoint only.

Ubuntu's msmtp AppArmor profile does not allow custom configuration paths by default. Open its local override with `sudoedit /etc/apparmor.d/local/usr.bin.msmtp` and add:

```text
/etc/prismatic/msmtprc r,
/etc/prismatic/smtp-password r,
```

If the profile has a disable symlink, remove only that symlink with `if [ -L /etc/apparmor.d/disable/usr.bin.msmtp ]; then sudo unlink /etc/apparmor.d/disable/usr.bin.msmtp; fi`, then load the profile with `sudo apparmor_parser -r /etc/apparmor.d/usr.bin.msmtp`.

Test SMTP submission as the PHP user with `printf 'To: team@prismatichq.com\nFrom: Prismatic Website <prismatic@gioia.email>\nSubject: Prismatic SMTP test\n\nThe SMTP relay is working.\n' | sudo -u www-data /usr/bin/msmtp --file=/etc/prismatic/msmtprc -t`.

## 4. Configure nginx

Add the rate-limit zone inside the `http` block in `/etc/nginx/nginx.conf`:

```nginx
limit_req_zone $binary_remote_addr zone=contact:10m rate=5r/m;
```

Add this exact-match location to the `api.gioia.dev` HTTPS `server` block:

```nginx
location = /prismatic/contact {
    client_max_body_size 16k;
    limit_req zone=contact burst=2 nodelay;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME /var/www/api.gioia.dev/prismatic/contact.php;
    fastcgi_param PHP_ADMIN_VALUE "sendmail_path=/usr/bin/msmtp --file=/etc/prismatic/msmtprc -t";
    fastcgi_pass unix:/run/php/php-fpm.sock;
}

location / {
    return 404;
}
```

The version-neutral `/run/php/php-fpm.sock` symlink currently selects the server's default PHP-FPM version.

If the HTTPS virtual host does not exist yet, create the HTTP `api.gioia.dev` server block first and obtain its certificate with `sudo certbot --nginx -d api.gioia.dev`.

Validate and reload the configuration with `sudo nginx -t && sudo systemctl reload nginx`.

## 5. Deploy

The Forgejo workflow deploys `server/contact.php` to `/var/www/api.gioia.dev/prismatic/contact.php`. The destination directory must exist and be writable by the deployment user before the first run.

The website submits directly to `https://api.gioia.dev/prismatic/contact`. Standard HTML form posts do not require CORS headers. After processing, the endpoint redirects the browser to the matching status message at `https://prismatichq.com/#contact-sent` or `https://prismatichq.com/#contact-error`.
