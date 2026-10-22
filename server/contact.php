<?php

declare(strict_types=1);

const WEBSITE_URL = 'https://prismatichq.com/';
const RECIPIENT = 'team@prismatichq.com';
const SENDER = 'Prismatic Website <prismatic@gioia.email>';
const CONTACT_SUBJECT = 'New message from prismatichq.com';
const NEWSLETTER_SUBJECT = 'New Prismatic newsletter signup';
const MAX_NAME_LENGTH = 120;
const MAX_EMAIL_LENGTH = 254;
const MAX_MESSAGE_LENGTH = 5000;

function finish(string $form, string $result): never
{
    header('Location: ' . WEBSITE_URL . "#{$form}-{$result}", true, 303);
    exit;
}

function field(string $name): ?string
{
    $value = $_POST[$name] ?? null;

    return is_string($value) ? trim($value) : null;
}

function invalidEmail(?string $email): bool
{
    return $email === null
        || $email === ''
        || strlen($email) > MAX_EMAIL_LENGTH
        || str_contains($email, "\r")
        || str_contains($email, "\n")
        || filter_var($email, FILTER_VALIDATE_EMAIL) === false;
}

function sendMessage(string $form, string $subject, string $body, ?string $replyTo = null): never
{
    $headers = [
        'From' => SENDER,
        'Content-Type' => 'text/plain; charset=UTF-8',
    ];

    if ($replyTo !== null) {
        $headers['Reply-To'] = $replyTo;
    }

    if (!mail(RECIPIENT, $subject, $body, $headers)) {
        error_log("Prismatic {$form} form: SMTP submission failed. sendmail_path=" . ini_get('sendmail_path'));
        finish($form, 'error');
    }

    finish($form, 'sent');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

$form = field('form') === 'newsletter' ? 'newsletter' : 'contact';
$website = field('website');

// Bots commonly fill fields that are visually hidden from real visitors.
if ($website !== null && $website !== '') {
    finish($form, 'sent');
}

if ($form === 'newsletter') {
    $email = field('subscriber');

    if (invalidEmail($email)) {
        finish($form, 'error');
    }

    $body = implode("\n", [
        NEWSLETTER_SUBJECT,
        '',
        "Email: {$email}",
    ]);

    sendMessage($form, NEWSLETTER_SUBJECT, $body);
}

$name = field('name');
$email = field('email');
$message = field('message');

$invalid = $name === null
    || $message === null
    || $name === ''
    || $message === ''
    || strlen($name) > MAX_NAME_LENGTH
    || strlen($message) > MAX_MESSAGE_LENGTH
    || invalidEmail($email);

if ($invalid) {
    finish($form, 'error');
}

$body = implode("\n", [
    'New message from prismatichq.com',
    '',
    "Name: {$name}",
    "Email: {$email}",
    '',
    'Message:',
    $message,
]);

sendMessage($form, CONTACT_SUBJECT, $body, $email);
