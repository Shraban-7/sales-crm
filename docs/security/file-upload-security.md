# File Upload & Attachment Security — Sales CRM

**Last Updated:** 2026-11-17  
**Implementation Status:** `Proposed (Greenfield Blueprint)`  
**Related Docs:** `docs/security/security-policy.md`, `docs/modules/quotations-and-proposals.md`, `docs/modules/import-and-export.md`

## 1. Threat Overview

CRM users upload deal attachments (contracts, POs, PDFs) and CSV/XLSX import sheets. Unvalidated uploads stored in `public/` could allow Remote Code Execution (RCE), Stored XSS (via `.svg` or `.html` files), or unauthorized direct URL scraping.

## 2. Six Mandatory File Upload Controls (`Proposed`)

1. **Strict MIME & Extension Allowlist:**
   - Attachments: `mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg` (`max:10240` = 10 MB).
   - Executable, script, and active markup formats (`.php`, `.phtml`, `.phar`, `.sh`, `.exe`, `.js`, `.html`, `.htm`, `.svg`, `.xml`) are strictly blocked.
2. **Server-Side Magic-Byte MIME Inspection:**
   - Laravel's `mimes` and `mimetypes` rules use PHP `finfo` to inspect the actual file header bytes, not the client-provided `Content-Type` header.
3. **Non-Public Storage Disk (`private`):**
   - Store all CRM attachments and imports under `storage/app/private/attachments/{team_id}/{yyyy}/{mm}/`—never inside `public/` or `storage/app/public/`.
4. **Randomized Storage Filenames:**
   - Generate a random UUID/ULID storage filename (`Str::ulid() . '.' . $file->extension()`) while storing the sanitized `original_name` in the `attachments` table strictly for display metadata.
5. **SHA-256 Integrity Checksum:**
   - Compute `hash_file('sha256', $file->getRealPath())` on upload and store in `attachments.sha256_checksum`.
6. **Authorized Download Delivery:**
   - Serve downloads exclusively through an authenticated controller (`AttachmentDownloadController`) that verifies `$this->authorize('view', $attachment->attachable)` and returns `Storage::disk('private')->download($attachment->storage_path, $attachment->original_name, ['X-Content-Type-Options' => 'nosniff'])`.
