# Backup & Disaster Recovery Plan — Sale Fusion CRM

**Last Updated:** 2026-11-19  
**Implementation Status:** `Implemented (Active Operational Runbook)`  
**Related Docs:** `docs/database/data-integrity.md`, `docs/security/data-protection.md`

## 1. Recovery Objectives

- **Recovery Point Objective (RPO):** `<= 1 hour` for MySQL database (via binary logs / hourly incremental dumps) and `<= 24 hours` for private file attachments.
- **Recovery Time Objective (RTO):** `<= 30 minutes` to provision and restore a clean database and storage volume.

## 2. Backup Scope & Schedule

| Asset | Backup Method | Frequency | Retention Policy |
| :--- | :--- | :--- | :--- |
| **MySQL 8.0 Database** | `mysqldump --single-transaction --routines --triggers` + AES-256 encryption | Every 6 hours + Daily at `02:00 Asia/Dhaka` | 14 daily, 8 weekly, 12 monthly |
| **Private Attachments (`storage/app/private`)** | Encrypted `rsync` / S3 versioned bucket sync | Daily at `02:30 Asia/Dhaka` | 30 daily, 12 monthly |
| **Environment Configuration (`.env`)** | Encrypted secrets vault (offline from public Git) | On every configuration change | Versioned history |

## 3. Restoration & Verification Drill Procedure

1. Put application into maintenance mode: `php artisan down --render="errors::503"`.
2. Stop Supervisor queue workers (`supervisorctl stop sales-crm-worker:*`).
3. Verify backup checksum (`sha256sum`) and decrypt the `.sql.gz.enc` archive.
4. Restore database into target schema: `gunzip < backup.sql.gz | mysql -u root -p sales_crm`.
5. Restore `storage/app/private/` attachments and verify file ownership (`chown -R www-data:www-data storage`).
6. Run `php artisan migrate --force` and `php artisan optimize:clear && php artisan optimize`.
7. Start queue workers and exit maintenance mode: `php artisan up`.
