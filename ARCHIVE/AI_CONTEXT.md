# AI CONTEXT — SGX VENDOR WORK EVIDENCE

## Project Overview
- **System**: SGX Vendor Work Evidence (Digital Evidence & Reporting System)
- **Primary Goal**: Manage and control vendor work orders from start to completion, ensuring verifiable field execution via GPS, server-timestamped multi-stage photo evidence (Before/Process/After), structured review & revision workflows, and automated BA Opname generation.
- **Core Principle**: Server is the single source of truth for authorization, status, progress, GPS, timestamps, evidence validation, and audit logs.

## Roles
1. **SUPERUSER**: System administration, user & role management, global configuration, full audit log inspection.
2. **ADMIN**: Operational hub. Creates SPKs, assigns field teams, monitors progress, reviews submissions, requests specific revisions, approves jobs, generates BA Opname, manages master data, and oversees WhatsApp notifications.
3. **FIELD_TEAM**: Mobile-first field operator. Receives assigned work orders, performs mandatory GPS check-in, captures & uploads structured photo evidence, logs technical issues (kendala), and submits for review.
4. **VENDOR**: Isolated portal. Tracks real-time work order progress via completion stepper, reviews uploaded evidence, and views approved BA Opname reports.

## Official Status Vocabulary
`DRAFT` → `READY` → `ASSIGNED` → `CHECKED_IN` → `IN_PROGRESS` → `SUBMITTED` → `UNDER_REVIEW` → `REVISION` → `APPROVED` → `BA_OPNAME` → `COMPLETED`

## On-Demand Documentation Links
- [Architecture & Tech Specs](file:///d:/ANTIGRAFYTI/SGX_VENDOR/docs/ARCHITECTURE.md)
- [Database Schema & Data Dictionary](file:///d:/ANTIGRAFYTI/SGX_VENDOR/docs/DATABASE.md)
- [RBAC & Security Rules](file:///d:/ANTIGRAFYTI/SGX_VENDOR/docs/RBAC.md)
- [Business Workflow & Rules](file:///d:/ANTIGRAFYTI/SGX_VENDOR/docs/BUSINESS_RULES.md)
- [REST API Specifications](file:///d:/ANTIGRAFYTI/SGX_VENDOR/docs/API.md)
- [Testing & Verification Guide](file:///d:/ANTIGRAFYTI/SGX_VENDOR/docs/TESTING.md)
