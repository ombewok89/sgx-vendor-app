# TECHNICAL ARCHITECTURE SPECIFICATION

## 1. System Topology

```text
┌────────────────────────────────────────────────────────┐
│                   React 19 + Vite UI                   │
│   (Desktop: Admin/Superuser  |  Mobile: Field Team)    │
└───────────────────────────┬────────────────────────────┘
                            │ REST API + JSON / Multipart
┌───────────────────────────▼────────────────────────────┐
│                  Express.js Backend                    │
│ ┌────────────────┐ ┌────────────────┐ ┌──────────────┐ │
│ │ Auth & RBAC    │ │ State Machine  │ │ Audit Logger │ │
│ └────────────────┘ └────────────────┘ └──────────────┘ │
│ ┌────────────────┐ ┌────────────────┐ ┌──────────────┐ │
│ │ Evidence Engine│ │ BA PDF Gen     │ │ Notification │ │
│ └────────────────┘ └────────────────┘ └──────────────┘ │
└───────────────────────────┬────────────────────────────┘
                            │
┌───────────────────────────▼────────────────────────────┐
│                   SQLite + WAL Mode                    │
│                  Uploads File Storage                  │
└────────────────────────────────────────────────────────┘
```

## 2. Directory Structure
```text
SGX_VENDOR/
├── AI_CONTEXT.md
├── CURRENT_STATE.md
├── PRODUCT BLUEPRINT — SGX VENDOR WORK EVIDENCE.MD
├── docs/
│   ├── ARCHITECTURE.md
│   ├── DATABASE.md
│   ├── RBAC.md
│   ├── BUSINESS_RULES.md
│   ├── API.md
│   └── TESTING.md
├── server/
│   ├── package.json
│   ├── src/
│   │   ├── index.js
│   │   ├── config/
│   │   │   └── database.js
│   │   ├── middleware/
│   │   │   ├── auth.js
│   │   │   ├── rbac.js
│   │   │   └── upload.js
│   │   ├── services/
│   │   │   ├── authService.js
│   │   │   ├── workOrderService.js
│   │   │   ├── checkInService.js
│   │   │   ├── evidenceService.js
│   │   │   ├── reviewService.js
│   │   │   ├── baService.js
│   │   │   ├── notificationService.js
│   │   │   ├── auditService.js
│   │   │   └── masterDataService.js
│   │   ├── routes/
│   │   │   ├── authRoutes.js
│   │   │   ├── workOrderRoutes.js
│   │   │   ├── checkInRoutes.js
│   │   │   ├── evidenceRoutes.js
│   │   │   ├── reviewRoutes.js
│   │   │   ├── baRoutes.js
│   │   │   ├── masterDataRoutes.js
│   │   │   ├── reportRoutes.js
│   │   │   └── systemRoutes.js
│   │   └── utils/
│   │       └── helpers.js
│   └── tests/
├── client/
│   ├── package.json
│   ├── index.html
│   ├── vite.config.js
│   └── src/
│       ├── App.jsx
│       ├── main.jsx
│       ├── context/
│       │   └── AuthContext.jsx
│       ├── components/
│       │   ├── Navbar.jsx
│       │   ├── Sidebar.jsx
│       │   ├── StatusBadge.jsx
│       │   ├── GeolocationCapture.jsx
│       │   ├── PhotoUploader.jsx
│       │   ├── StepperProgress.jsx
│       │   └── BaOpnameViewer.jsx
│       ├── pages/
│       │   ├── Login.jsx
│       │   ├── admin/
│       │   │   ├── AdminDashboard.jsx
│       │   │   ├── WorkOrderList.jsx
│       │   │   ├── WorkOrderCreate.jsx
│       │   │   ├── WorkOrderDetail.jsx
│       │   │   ├── ReviewConsole.jsx
│       │   │   ├── BaOpnameList.jsx
│       │   │   ├── MasterData.jsx
│       │   │   └── Reports.jsx
│       │   ├── field/
│       │   │   ├── FieldDashboard.jsx
│       │   │   ├── FieldWorkDetail.jsx
│       │   │   ├── FieldCheckIn.jsx
│       │   │   └── FieldEvidence.jsx
│       │   ├── vendor/
│       │   │   ├── VendorDashboard.jsx
│       │   │   └── VendorWorkDetail.jsx
│       │   └── superuser/
│       │       ├── SuperDashboard.jsx
│       │       ├── UserManagement.jsx
│       │       ├── SystemSettings.jsx
│       │       └── AuditLogs.jsx
│       └── services/
│           └── api.js
└── uploads/
```
