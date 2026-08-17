# ROLE-BASED ACCESS CONTROL (RBAC) & SECURITY

## 1. User Roles
- `SUPERUSER`: Full system access, managing users, roles, system settings, viewing complete audit trail.
- `ADMIN`: Operational management, SPK CRUD, assigning teams, reviewing work orders, requesting revisions, approving work orders, generating BA Opname, managing master data, monitoring notification logs.
- `FIELD_TEAM`: Mobile field operator. Can only view work orders assigned to them, check-in with GPS, upload Before/Process/After photo evidence, report issues, and submit for review. Cannot approve own work.
- `VENDOR`: External partner. Can only view work orders, tracking progress, photo evidence, and approved BA documents for their own vendor ID (strict vendor isolation).

## 2. Permission Matrix
| Resource | SUPERUSER | ADMIN | FIELD_TEAM | VENDOR |
| :--- | :--- | :--- | :--- | :--- |
| Work Order (Create/Edit) | Yes | Yes | No | No |
| Work Order (View) | All | All | Assigned Only | Own Vendor Only |
| Team Assignment | Yes | Yes | No | No |
| GPS Check-in | No | No | Assigned Only | No |
| Upload Evidence | No | No | Assigned Only | No |
| Report Issue (Kendala) | No | No | Assigned Only | No |
| Review / Request Revision | Yes | Yes | No | No |
| Approve Work Order | Yes | Yes | No (Forbidden) | No |
| Generate BA Opname | Yes | Yes | No | No |
| View BA Opname | Yes | Yes | No | Own Vendor Only |
| Master Data Management | Yes | Yes | No | No |
| User & System Config | Yes | No | No | No |
| Audit Log Inspection | Yes | Read-only | No | No |

## 3. Server-Side Security Rules
1. **Server as Single Source of Truth**: UI visibility is never a security boundary. All validations (ownership, role, valid state transition) happen in backend middleware and service layers.
2. **Official Geolocation & Timestamp**: Client timestamps are purely informational. Official records use server clock and hardware browser/device GPS.
3. **Vendor Isolation**: All vendor queries automatically append `WHERE vendor_id = :userVendorId`.
