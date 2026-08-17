# TESTING & VERIFICATION GUIDE

## 1. Automated Test Suites
Run automated backend unit/integration tests:
```bash
cd server
npm test
```

### Coverage:
1. **Auth & RBAC**: Validate token creation, role access violations, vendor isolation.
2. **State Machine Integrity**: Attempt illegal transitions (e.g. submitting without check-in, approving as field team).
3. **Evidence Validation Gate**: Verify submitting requires exact photo count per stage and issue log.
4. **Audit Logs**: Verify every mutating state transition records an audit log row.

## 2. End-to-End Persona Verification Checklist
- [ ] **Admin Flow**: Login -> Create SPK -> Assign PIC -> Monitor live status.
- [ ] **Field Team Flow**: Login as PIC -> View assigned task -> Perform GPS Check-in -> Upload Before/Process/After -> Report Issue -> Submit.
- [ ] **Review & Revision Flow**: Login as Admin -> Open Review -> Request Revision on After photo -> Field Team receives open revision -> Field Team resubmits -> Admin Approves.
- [ ] **BA Opname Flow**: Generate BA Opname -> Preview with Header Kop Surat -> Print/PDF.
- [ ] **Vendor Flow**: Login as Vendor -> View isolated list -> Inspect progress stepper.
- [ ] **Superuser Flow**: View audit logs of all actions above -> Manage users.
