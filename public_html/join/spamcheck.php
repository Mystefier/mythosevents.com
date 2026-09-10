<?php
// Shared spam check for public-facing forms.
//
// Two cheap signals that catch the overwhelming majority of automated form spam:
//   1. A honeypot field ("nickname") that's hidden from real users but that
//      form-filling bots populate. Any value in it = bot.
//   2. Submissions where key fields are still the literal placeholder text
//      ("First name", "your@email.com", "(555) 000-0000", ...) — a human who
//      actually typed something never leaves these exactly as-is.
//
// Usage:
//   require_once __DIR__ . '/spamcheck.php';   // adjust relative path
//   if (is_spam_submission($_POST)) {
//       // Pretend it worked so the bot doesn't retry — just skip the insert/emails.
//   }
//
// Pair it with spam_honeypot_field() in the form's HTML.

function is_spam_submission(array $post): bool {
    // 1. Honeypot
    if (isset($post['nickname']) && trim((string)$post['nickname']) !== '') {
        return true;
    }

    // 2. Untouched placeholder values
    $placeholders = [
        'first name', 'last name', 'your name', 'full name', 'first', 'last',
        'your@email.com', 'you@business.com', 'you@example.com',
        'email@example.com', 'name@example.com', 'your@email', 'test@test.com',
        '(555) 000-0000', '555-000-0000', '5550000000', '(555) 555-5555',
        'your business name', 'business name', 'company name', 'venue name',
        'https://yourbusiness.com', 'https://example.com',
    ];
    foreach ($post as $key => $val) {
        if (!is_string($val)) {
            continue;
        }
        $norm = strtolower(trim($val));
        if ($norm !== '' && in_array($norm, $placeholders, true)) {
            return true;
        }
    }

    return false;
}

// Drop this into any public form, just before the submit button.
function spam_honeypot_field(): string {
    return '<div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden;">'
         . '<label>Leave this field blank <input type="text" name="nickname" tabindex="-1" autocomplete="off"></label>'
         . '</div>';
}
