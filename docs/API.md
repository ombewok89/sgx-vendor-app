# REST API SPECIFICATION

## 1. Authentication Endpoints
- `POST /api/auth/login`
  - Body: `{ email, password }`
  - Response: `{ token, user: { id, name, email, role, vendor_id } }`
- `GET /api/auth/me`
  - Headers: `Authorization: Bearer <token>`
  - Response: User profile and assigned permissions
- `POST /api/auth/quick-switch` (Development helper)
  - Body: `{ role }`

## 2. Work Orders (SPK)
- `GET /api/work-orders` (Filter: status, vendor_id, area_id, search)
- `POST /api/work-orders` (Admin/Superuser: Create SPK)
- `GET /api/work-orders/:id` (Full detail + assignments + checkin + evidence + issues + reviews + BA)
- `PUT /api/work-orders/:id` (Edit draft/ready SPK)
- `POST /api/work-orders/:id/assign` (Assign PIC & Field Team members)
- `POST /api/work-orders/:id/submit` (Field Team: Validate gates & submit)

## 3. Check-In (GPS)
- `POST /api/check-ins`
  - Body: `{ work_order_id, latitude, longitude, accuracy, client_timestamp, address_note }`
  - Response: `{ id, server_timestamp, status: 'SUCCESS' }`

## 4. Evidence Photos
- `POST /api/evidence/upload`
  - Form-data: `file` (image), `work_order_id`, `stage` (BEFORE/PROCESS/AFTER/ISSUE), `sequence`, `latitude`, `longitude`, `accuracy`, `notes`
  - Response: `{ id, file_hash, file_path, server_timestamp, stage }`
- `DELETE /api/evidence/:id` (Only if status is before SUBMITTED)

## 5. Technical Issues (Kendala)
- `POST /api/issues`
  - Body: `{ work_order_id, has_issue, issue_type, notes }`

## 6. Review & Revision
- `POST /api/reviews/approve`
  - Body: `{ work_order_id, review_notes }`
- `POST /api/reviews/request-revision`
  - Body: `{ work_order_id, target_stage, reason }`

## 7. BA Opname & Documents
- `POST /api/ba/generate`
  - Body: `{ work_order_id, template_id, ba_number, ba_date }`
  - Response: Generated BA Opname with formatted letterhead, signatures & photos.
- `GET /api/ba/:id`
- `GET /api/ba/work-order/:work_order_id`

## 8. Master Data
- `GET/POST/PUT /api/master/vendors`
- `GET/POST/PUT /api/master/areas`
- `GET/POST/PUT /api/master/job-types`
- `GET/POST/PUT /api/master/field-teams`
- `GET/POST/PUT /api/master/users`
- `GET/POST/PUT /api/master/templates`

## 9. System, Reports & Audit
- `GET /api/reports/summary` (KPI statistics)
- `GET /api/reports/export-work-orders` (CSV / Excel format)
- `GET /api/audit-logs` (Filtered by entity, user, date)
- `GET /api/notifications` (Fonnte gateway logs)
