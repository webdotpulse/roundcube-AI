# Merge & Fix Contacts Plugin for Roundcube

A sleek, Google Contacts-style **"Merge & fix"** tool designed for Roundcube webmail address books.

Scans your contact list to find duplicate entries and clean up your address book, and suggests adding frequent email correspondents you haven't saved yet.

---

## What It Does

1. **Finds Duplicates**:
   - Analyzes contact cards across your address books for matching email addresses, phone numbers, or full/inverted names.
   - Computes match confidence ratings and displays specific match reasons (e.g. `Matching email: alice@example.org`, `Matching phone: +1 555-0199`).

2. **Combines Entries (Merge)**:
   - When you approve a suggestion, the separate cards are combined into a single unified profile.
   - Automatically preserves all unique emails (with subtypes: `work`, `home`, `other`), telephone numbers, job titles, organizations, notes, and addresses.
   - **Preserves contact groups**: Migrates group memberships so the merged contact belongs to all groups either original card was in.

3. **Suggests Additions (The "Fix" portion)**:
   - Scans your mailbox communication history (Sent items and Inbox) to discover frequent email contacts not yet in your address book.
   - Ranks candidates by interaction frequency and automatically infers company/organization from domain names.

4. **How to Use It**:
   - **Review individually**: Click **Merge** on specific pairs to combine them one by one.
   - **Merge all**: Select **Merge all** to combine all detected duplicate records at once.
   - **Dismiss**: Click **Dismiss** to ignore a suggestion and leave contacts separate.
   - **Add frequent contacts**: Click **Add to contacts** or **Add all** to turn frequent correspondents into contact cards.

---

## Installation & Activation

1. Add `merge_and_fix` to your `$config['plugins']` array in `config/config.inc.php`:
```php
$config['plugins'] = [
    // ...
    'merge_and_fix',
];
```

2. Access **Contacts** in Roundcube. You'll see the **Merge & fix** navigation link with a live duplicate counter badge in the sidebar and a **Merge & fix** toolbar button.
