# BUSINESS RULES & VALIDATION GATES

## 1. Work Order Lifecycle & Status Progression
```text
[DRAFT] -> [READY] -> [ASSIGNED] -> [CHECKED_IN] -> [IN_PROGRESS] -> [SUBMITTED] -> [UNDER_REVIEW] -> [REVISION] -> [APPROVED] -> [BA_OPNAME] -> [COMPLETED]
```

### Transition Criteria:
1. **DRAFT → READY**: Required fields filled (SPK Number, Title, Vendor, Area, Location, Dates, Documentation Mode).
2. **READY → ASSIGNED**: At least 1 PIC user must be assigned from field team.
3. **ASSIGNED → CHECKED_IN**: PIC or assigned team member performs GPS check-in (requires valid latitude, longitude, and accuracy <= 500m).
4. **CHECKED_IN → IN_PROGRESS**: Field team uploads initial photo evidence or logs progress.
5. **IN_PROGRESS / REVISION → SUBMITTED**:
   - Validation Gate checked by server:
     - Check-in verified.
     - Assignment verified.
     - Evidence complete:
       - If `AFTER_ONLY`: Minimum photos in `AFTER` stage (default: 1-3).
       - If `BEFORE_PROCESS_AFTER`: Minimum photos in `BEFORE`, `PROCESS`, and `AFTER` stages (default: 3 photos each).
     - Issue question answered (Yes/No with structured details if Yes).
6. **SUBMITTED → UNDER_REVIEW**: Automatically set or triggered when Admin opens review console.
7. **UNDER_REVIEW → REVISION**: Admin specifies targeted stage (e.g. `AFTER`) and specific reason (e.g. "Foto nomor 2 buram/tidak fokus"). Revisions log created with status `OPEN`.
8. **UNDER_REVIEW → APPROVED**: Admin approves all evidence stages and issues.
9. **APPROVED → BA_OPNAME**: Generated after approval. Automatically compiles SPK info, vendor details, team signatures, GPS coordinates, and photo evidence into formatted Berita Acara.
10. **BA_OPNAME → COMPLETED**: Work order is officially closed and archived.

## 2. Server Validation Guards
- **No Manual GPS**: Field users cannot type coordinates manually.
- **Official Timestamps**: Server timestamps override client device clock.
- **SHA-256 Checksum**: Every uploaded photo generates SHA-256 hash to guarantee immutability.
- **Vendor Privacy Isolation**: Vendor users only receive data filtered by their `vendor_id`.
