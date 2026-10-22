<?php

declare(strict_types=1);

const WEBSITE_URL = 'https://prismatichq.com/';
const RECIPIENT = 'team@prismatichq.com';
const SENDER = 'Prismatic Website <prismatic@gioia.email>';
const SUBJECT = 'New message from prismatichq.com';
const MAX_NAME_LENGTH = 120;
const MAX_EMAIL_LENGTH = 254;
const MAX_MESSAGE_LENGTH = 5000;

function finish(string $result): never
{
    header('Location: ' . WEBSITE_URL . "#contact-{$result}", true, 303);
    exit;
}

function field(string $name): ?string
{
    $value = $_POST[$name] ?? null;

    return is_string($value) ? trim($value) : null;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

$name = field('name');
$email = field('email');
$message = field('message');
$website = field('website');

// Bots commonly fill fields that are visually hidden from real visitors.
if ($website !== null && $website !== '') {
    finish('sent');
}

$invalid = $name === null
    || $email === null
    || $message === null
    || $name === ''
    || $email === ''
    || $message === ''
    || strlen($name) > MAX_NAME_LENGTH
    || strlen($email) > MAX_EMAIL_LENGTH
    || strlen($message) > MAX_MESSAGE_LENGTH
    || str_contains($email, "\r")
    || str_contains($email, "\n")
    || filter_var($email, FILTER_VALIDATE_EMAIL) === false;

if ($invalid) {
    finish('error');
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

$sent = mail(RECIPIENT, SUBJECT, $body, [
    'From' => SENDER,
    'Reply-To' => $email,
    'Content-Type' => 'text/plain; charset=UTF-8',
]);

if (!$sent) {
    error_log('Prismatic contact form: SMTP submission failed.');
    finish('error');
}

finish('sent');