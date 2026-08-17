# DATABASE SPECIFICATION & SCHEMA

## 1. Relational Entities Overview

```text
users ───────────────┬────── field_team_members ──── field_teams
  │                  │
  ├─ vendors ────────┼────── work_orders ───────────┬─ work_order_assignments
  │                  │          │                   ├─ check_ins
  └─ areas ──────────┘          │                   ├─ evidence_photos
                                │                   ├─ issues
                                │                   ├─ reviews ── revisions
                                │                   └─ ba_documents
                                └─ audit_logs / notifications
```

## 2. Table Definitions

### `users`
- `id` (INTEGER PRIMARY KEY AUTOINCREMENT)
- `name` (TEXT NOT NULL)
- `email` (TEXT UNIQUE NOT NULL)
- `password_hash` (TEXT NOT NULL)
- `phone` (TEXT)
- `role` (TEXT NOT NULL: 'SUPERUSER' | 'ADMIN' | 'FIELD_TEAM' | 'VENDOR')
- `vendor_id` (INTEGER NULL REFERENCES vendors(id))
- `is_active` (INTEGER DEFAULT 1)
- `created_at` (TEXT NOT NULL)
- `updated_at` (TEXT NOT NULL)

### `vendors`
- `id` (INTEGER PRIMARY KEY AUTOINCREMENT)
- `name` (TEXT NOT NULL)
- `code` (TEXT UNIQUE NOT NULL)
- `contact_person` (TEXT)
- `phone` (TEXT)
- `email` (TEXT)
- `address` (TEXT)
- `is_active` (INTEGER DEFAULT 1)
- `created_at` (TEXT NOT NULL)

### `areas`
- `id` (INTEGER PRIMARY KEY AUTOINCREMENT)
- `name` (TEXT NOT NULL)
- `province` (TEXT)
- `city` (TEXT)
- `district` (TEXT)

### `job_types`
- `id` (INTEGER PRIMARY KEY AUTOINCREMENT)
- `name` (TEXT NOT NULL)
- `code` (TEXT UNIQUE NOT NULL)
- `doc_mode` (TEXT DEFAULT 'BEFORE_PROCESS_AFTER') -- 'AFTER_ONLY' | 'BEFORE_PROCESS_AFTER'
- `min_photos_per_stage` (INTEGER DEFAULT 3)
- `is_active` (INTEGER DEFAULT 1)

### `field_teams` & `field_team_members`
- `field_teams`: `id`, `name`, `leader_user_id` (REFERENCES users(id)), `is_active`, `created_at`
- `field_team_members`: `id`, `team_id` (REFERENCES field_teams(id)), `user_id` (REFERENCES users(id))

### `work_orders`
- `id` (INTEGER PRIMARY KEY AUTOINCREMENT)
- `spk_number` (TEXT UNIQUE NOT NULL)
- `title` (TEXT NOT NULL)
- `vendor_id` (INTEGER NOT NULL REFERENCES vendors(id))
- `area_id` (INTEGER NOT NULL REFERENCES areas(id))
- `job_type_id` (INTEGER REFERENCES job_types(id))
- `location_name` (TEXT NOT NULL)
- `target_lat` (REAL)
- `target_lng` (REAL)
- `pic_user_id` (INTEGER REFERENCES users(id))
- `start_date` (TEXT NOT NULL)
- `deadline` (TEXT NOT NULL)
- `doc_mode` (TEXT NOT NULL DEFAULT 'BEFORE_PROCESS_AFTER') -- 'AFTER_ONLY' | 'BEFORE_PROCESS_AFTER'
- `require_checkin` (INTEGER NOT NULL DEFAULT 1)
- `status` (TEXT NOT NULL DEFAULT 'DRAFT')
- `progress_percent` (INTEGER DEFAULT 0)
- `created_by` (INTEGER REFERENCES users(id))
- `created_at` (TEXT NOT NULL)
- `updated_at` (TEXT NOT NULL)

### `work_order_assignments`
- `id` (INTEGER PRIMARY KEY AUTOINCREMENT)
- `work_order_id` (INTEGER NOT NULL REFERENCES work_orders(id))
- `user_id` (INTEGER NOT NULL REFERENCES users(id))
- `role_in_team` (TEXT NOT NULL DEFAULT 'MEMBER') -- 'PIC' | 'MEMBER'
- `assigned_at` (TEXT NOT NULL)

### `check_ins`
- `id` (INTEGER PRIMARY KEY AUTOINCREMENT)
- `work_order_id` (INTEGER NOT NULL REFERENCES work_orders(id))
- `user_id` (INTEGER NOT NULL REFERENCES users(id))
- `server_timestamp` (TEXT NOT NULL)
- `client_timestamp` (TEXT)
- `latitude` (REAL NOT NULL)
- `longitude` (REAL NOT NULL)
- `accuracy` (REAL NOT NULL)
- `address_note` (TEXT)
- `created_at` (TEXT NOT NULL)

### `evidence_photos`
- `id` (INTEGER PRIMARY KEY AUTOINCREMENT)
- `work_order_id` (INTEGER NOT NULL REFERENCES work_orders(id))
- `user_id` (INTEGER NOT NULL REFERENCES users(id))
- `stage` (TEXT NOT NULL) -- 'BEFORE' | 'PROCESS' | 'AFTER' | 'ISSUE'
- `sequence` (INTEGER NOT NULL DEFAULT 1)
- `file_path` (TEXT NOT NULL)
- `file_name` (TEXT NOT NULL)
- `file_size` (INTEGER NOT NULL)
- `mime_type` (TEXT NOT NULL)
- `file_hash` (TEXT NOT NULL) -- SHA-256
- `server_timestamp` (TEXT NOT NULL)
- `latitude` (REAL)
- `longitude` (REAL)
- `accuracy` (REAL)
- `notes` (TEXT)
- `created_at` (TEXT NOT NULL)

### `issues` (Kendala Teknis)
- `id` (INTEGER PRIMARY KEY AUTOINCREMENT)
- `work_order_id` (INTEGER NOT NULL REFERENCES work_orders(id))
- `user_id` (INTEGER NOT NULL REFERENCES users(id))
- `has_issue` (INTEGER NOT NULL DEFAULT 0) -- 0: Tidak, 1: Ya
- `issue_type` (TEXT)
- `notes` (TEXT)
- `created_at` (TEXT NOT NULL)

### `reviews` & `revisions`
- `reviews`: `id`, `work_order_id`, `reviewer_user_id`, `status` ('APPROVED' | 'REVISION_REQUESTED'), `review_notes`, `created_at`
- `revisions`: `id`, `work_order_id`, `review_id`, `target_stage` ('BEFORE' | 'PROCESS' | 'AFTER' | 'ALL'), `reason` (TEXT NOT NULL), `requested_by`, `requested_at`, `status` ('OPEN' | 'RESOLVED'), `resolved_at`

### `ba_documents` (Berita Acara Opname)
- `id` (INTEGER PRIMARY KEY AUTOINCREMENT)
- `work_order_id` (INTEGER UNIQUE NOT NULL REFERENCES work_orders(id))
- `ba_number` (TEXT UNIQUE NOT NULL)
- `ba_date` (TEXT NOT NULL)
- `template_id` (INTEGER REFERENCES document_templates(id))
- `generated_by` (INTEGER REFERENCES users(id))
- `content_json` (TEXT)
- `pdf_path` (TEXT)
- `status` (TEXT DEFAULT 'FINAL')
- `created_at` (TEXT NOT NULL)

### `document_templates`
- `id` (INTEGER PRIMARY KEY AUTOINCREMENT)
- `name` (TEXT NOT NULL)
- `code` (TEXT UNIQUE NOT NULL)
- `header_html` (TEXT)
- `footer_html` (TEXT)
- `body_template` (TEXT)
- `is_default` (INTEGER DEFAULT 0)

### `notifications`
- `id` (INTEGER PRIMARY KEY AUTOINCREMENT)
- `provider` (TEXT DEFAULT 'FONNTE')
- `recipient` (TEXT NOT NULL)
- `message_type` (TEXT NOT NULL)
- `payload` (TEXT)
- `status` (TEXT DEFAULT 'PENDING') -- 'PENDING' | 'SENT' | 'DELIVERED' | 'FAILED'
- `error` (TEXT)
- `sent_at` (TEXT)
- `created_at` (TEXT NOT NULL)

### `audit_logs`
- `id` (INTEGER PRIMARY KEY AUTOINCREMENT)
- `user_id` (INTEGER REFERENCES users(id))
- `user_name` (TEXT)
- `action` (TEXT NOT NULL)
- `entity_type` (TEXT NOT NULL)
- `entity_id` (INTEGER)
- `old_value` (TEXT)
- `new_value` (TEXT)
- `ip_address` (TEXT)
- `created_at` (TEXT NOT NULL)
