const sqlite3 = require('sqlite3').verbose();
const path = require('path');
const fs = require('fs');
const bcrypt = require('bcryptjs');

const dbPath = path.resolve(__dirname, '../../data/sgx_vendor.sqlite');
const dataDir = path.dirname(dbPath);

if (!fs.existsSync(dataDir)) {
  fs.mkdirSync(dataDir, { recursive: true });
}

const db = new sqlite3.Database(dbPath, (err) => {
  if (err) {
    console.error('Failed to connect to SQLite database:', err.message);
  } else {
    console.log('Connected to SQLite database at:', dbPath);
  }
});

// Enable WAL mode & Foreign Keys for integrity & concurrency
db.serialize(() => {
  db.run('PRAGMA foreign_keys = ON');
  db.run('PRAGMA journal_mode = WAL');
});

// Async DB Helpers
const query = (sql, params = []) => {
  return new Promise((resolve, reject) => {
    db.all(sql, params, (err, rows) => {
      if (err) reject(err);
      else resolve(rows);
    });
  });
};

const get = (sql, params = []) => {
  return new Promise((resolve, reject) => {
    db.get(sql, params, (err, row) => {
      if (err) reject(err);
      else resolve(row);
    });
  });
};

const run = (sql, params = []) => {
  return new Promise((resolve, reject) => {
    db.run(sql, params, function (err) {
      if (err) reject(err);
      else resolve({ lastID: this.lastID, changes: this.changes });
    });
  });
};

const exec = (sql) => {
  return new Promise((resolve, reject) => {
    db.exec(sql, (err) => {
      if (err) reject(err);
      else resolve();
    });
  });
};

// Initialize schema and seed data
async function initDatabase() {
  const schema = `
    CREATE TABLE IF NOT EXISTS roles (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      code TEXT UNIQUE NOT NULL,
      name TEXT NOT NULL,
      description TEXT
    );

    CREATE TABLE IF NOT EXISTS vendors (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      code TEXT UNIQUE NOT NULL,
      name TEXT NOT NULL,
      contact_person TEXT,
      phone TEXT,
      email TEXT,
      address TEXT,
      is_active INTEGER DEFAULT 1,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS areas (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      province TEXT,
      city TEXT,
      district TEXT,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS job_types (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      code TEXT UNIQUE NOT NULL,
      name TEXT NOT NULL,
      doc_mode TEXT NOT NULL DEFAULT 'BEFORE_PROCESS_AFTER',
      min_photos_per_stage INTEGER DEFAULT 3,
      is_active INTEGER DEFAULT 1,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      email TEXT UNIQUE NOT NULL,
      password_hash TEXT NOT NULL,
      phone TEXT,
      role TEXT NOT NULL,
      vendor_id INTEGER REFERENCES vendors(id),
      is_active INTEGER DEFAULT 1,
      created_at TEXT NOT NULL,
      updated_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS field_teams (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      leader_user_id INTEGER REFERENCES users(id),
      area_id INTEGER REFERENCES areas(id),
      is_active INTEGER DEFAULT 1,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS field_team_members (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      team_id INTEGER NOT NULL REFERENCES field_teams(id) ON DELETE CASCADE,
      user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
      created_at TEXT NOT NULL,
      UNIQUE(team_id, user_id)
    );

    CREATE TABLE IF NOT EXISTS work_orders (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      spk_number TEXT UNIQUE NOT NULL,
      title TEXT NOT NULL,
      vendor_id INTEGER NOT NULL REFERENCES vendors(id),
      area_id INTEGER NOT NULL REFERENCES areas(id),
      job_type_id INTEGER REFERENCES job_types(id),
      location_name TEXT NOT NULL,
      target_lat REAL,
      target_lng REAL,
      pic_user_id INTEGER REFERENCES users(id),
      start_date TEXT NOT NULL,
      deadline TEXT NOT NULL,
      doc_mode TEXT NOT NULL DEFAULT 'BEFORE_PROCESS_AFTER',
      require_checkin INTEGER NOT NULL DEFAULT 1,
      status TEXT NOT NULL DEFAULT 'DRAFT',
      progress_percent INTEGER DEFAULT 0,
      notes TEXT,
      created_by INTEGER REFERENCES users(id),
      created_at TEXT NOT NULL,
      updated_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS work_order_assignments (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      work_order_id INTEGER NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
      user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
      role_in_team TEXT NOT NULL DEFAULT 'MEMBER',
      assigned_at TEXT NOT NULL,
      UNIQUE(work_order_id, user_id)
    );

    CREATE TABLE IF NOT EXISTS work_order_items (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      work_order_id INTEGER NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
      item_name TEXT NOT NULL,
      job_type_id INTEGER REFERENCES job_types(id),
      doc_mode TEXT NOT NULL DEFAULT 'BEFORE_PROCESS_AFTER',
      weight_percent INTEGER DEFAULT 100,
      status TEXT NOT NULL DEFAULT 'PENDING',
      notes TEXT,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS check_ins (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      work_order_id INTEGER NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
      user_id INTEGER NOT NULL REFERENCES users(id),
      server_timestamp TEXT NOT NULL,
      client_timestamp TEXT,
      latitude REAL NOT NULL,
      longitude REAL NOT NULL,
      accuracy REAL NOT NULL,
      address_note TEXT,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS evidence_photos (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      work_order_id INTEGER NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
      item_id INTEGER REFERENCES work_order_items(id) ON DELETE SET NULL,
      user_id INTEGER NOT NULL REFERENCES users(id),
      stage TEXT NOT NULL,
      sequence INTEGER NOT NULL DEFAULT 1,
      file_path TEXT NOT NULL,
      file_name TEXT NOT NULL,
      file_size INTEGER NOT NULL,
      mime_type TEXT NOT NULL,
      file_hash TEXT NOT NULL,
      server_timestamp TEXT NOT NULL,
      latitude REAL,
      longitude REAL,
      accuracy REAL,
      notes TEXT,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS issues (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      work_order_id INTEGER NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
      user_id INTEGER NOT NULL REFERENCES users(id),
      has_issue INTEGER NOT NULL DEFAULT 0,
      issue_type TEXT,
      notes TEXT,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS reviews (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      work_order_id INTEGER NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
      reviewer_user_id INTEGER NOT NULL REFERENCES users(id),
      status TEXT NOT NULL,
      review_notes TEXT,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS revisions (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      work_order_id INTEGER NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
      review_id INTEGER REFERENCES reviews(id),
      target_stage TEXT NOT NULL,
      reason TEXT NOT NULL,
      requested_by INTEGER NOT NULL REFERENCES users(id),
      requested_at TEXT NOT NULL,
      status TEXT NOT NULL DEFAULT 'OPEN',
      resolved_at TEXT
    );

    CREATE TABLE IF NOT EXISTS document_templates (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      code TEXT UNIQUE NOT NULL,
      header_html TEXT,
      footer_html TEXT,
      body_template TEXT,
      is_default INTEGER DEFAULT 0,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS ba_documents (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      work_order_id INTEGER UNIQUE NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
      ba_number TEXT UNIQUE NOT NULL,
      ba_date TEXT NOT NULL,
      template_id INTEGER REFERENCES document_templates(id),
      generated_by INTEGER REFERENCES users(id),
      content_json TEXT,
      pdf_path TEXT,
      status TEXT DEFAULT 'FINAL',
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS notifications (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      provider TEXT DEFAULT 'FONNTE',
      recipient TEXT NOT NULL,
      message_type TEXT NOT NULL,
      payload TEXT,
      status TEXT DEFAULT 'SENT',
      error TEXT,
      sent_at TEXT,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS audit_logs (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER REFERENCES users(id),
      user_name TEXT,
      action TEXT NOT NULL,
      entity_type TEXT NOT NULL,
      entity_id INTEGER,
      old_value TEXT,
      new_value TEXT,
      ip_address TEXT,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS system_settings (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      key TEXT UNIQUE NOT NULL,
      value TEXT NOT NULL,
      description TEXT,
      updated_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS notifications_feed (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      work_order_id INTEGER REFERENCES work_orders(id) ON DELETE CASCADE,
      client_id INTEGER REFERENCES vendors(id),
      target_user_id INTEGER REFERENCES users(id),
      target_role TEXT DEFAULT 'ALL',
      category TEXT NOT NULL,
      title TEXT NOT NULL,
      message TEXT NOT NULL,
      is_read INTEGER DEFAULT 0,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS user_read_notifications (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      notification_id INTEGER REFERENCES notifications_feed(id) ON DELETE CASCADE,
      user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
      read_at TEXT NOT NULL,
      UNIQUE(notification_id, user_id)
    );
  `;

  await exec(schema);

  // Column migration for evidence_photos.item_id
  try {
    await run(`ALTER TABLE evidence_photos ADD COLUMN item_id INTEGER REFERENCES work_order_items(id) ON DELETE SET NULL`);
  } catch (e) {}

  // Column migration for job_types.standard_price
  try {
    await run(`ALTER TABLE job_types ADD COLUMN standard_price REAL DEFAULT 0`);
  } catch (e) {}

  // Column migration for work_orders.contract_value & geofencing
  try {
    await run(`ALTER TABLE work_orders ADD COLUMN contract_value REAL DEFAULT 0`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE work_orders ADD COLUMN require_geofence INTEGER NOT NULL DEFAULT 1`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE work_orders ADD COLUMN geofence_radius INTEGER NOT NULL DEFAULT 200`);
  } catch (e) {}

  // Column migration for check_ins geofencing verification
  try {
    await run(`ALTER TABLE check_ins ADD COLUMN distance_meters REAL`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE check_ins ADD COLUMN is_out_of_range INTEGER DEFAULT 0`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE check_ins ADD COLUMN geofence_status TEXT DEFAULT 'NOT_VERIFIED'`);
  } catch (e) {}

  // Column migration for document_templates
  try {
    await run(`ALTER TABLE document_templates ADD COLUMN logo_url TEXT`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE document_templates ADD COLUMN header_image_url TEXT`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE document_templates ADD COLUMN background_image_url TEXT`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE document_templates ADD COLUMN footer_image_url TEXT`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE document_templates ADD COLUMN signatories_json TEXT`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE document_templates ADD COLUMN signatory_first_party_name TEXT DEFAULT 'Dian Anggraini'`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE document_templates ADD COLUMN signatory_first_party_role TEXT DEFAULT 'Koordinator Pengawas Proyek'`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE document_templates ADD COLUMN signatory_second_party_name TEXT DEFAULT 'Andi Pratama'`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE document_templates ADD COLUMN signatory_second_party_role TEXT DEFAULT 'Project Manager Mitra Vendor'`);
  } catch (e) {}

  // Clean default opening text and guarantee clause for templates
  const defaultOpening = `<p>Pada hari ini <strong>{{ba_date}}</strong>, telah dilakukan pemeriksaan dan verifikasi lapangan atas pelaksanaan seluruh item pekerjaan untuk <strong>{{title}}</strong> di lokasi <strong>{{location_name}}</strong> dengan rincian sebagai berikut:</p>`;
  const defaultClause = `<p>Berdasarkan hasil pemeriksaan bukti foto digital (Before, Process, After) dan verifikasi teknis di lapangan, kedua belah pihak menyatakan bahwa seluruh item pekerjaan telah <strong>SELESAI 100% SECARA BAIK DAN MEMENUHI SPESIFIKASI MUTU</strong>.</p><p>Mitra Vendor memberikan jaminan masa pemeliharaan (garansi mutu) selama <strong>90 (sembilan puluh) hari kalender</strong> terhitung sejak tanggal penandatanganan Berita Acara ini.</p>`;

  try {
    await run(`UPDATE document_templates SET 
      header_html = ?, 
      body_template = ? 
      WHERE (header_html LIKE '%<table%' OR body_template LIKE '%<table%' OR header_html LIKE '%<div%')`,
      [defaultOpening, defaultClause]
    );
  } catch (e) {}

  // Column migrations for issues
  try {
    await run(`ALTER TABLE issues ADD COLUMN status TEXT DEFAULT 'OPEN'`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE issues ADD COLUMN resolution_notes TEXT`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE issues ADD COLUMN resolved_by INTEGER REFERENCES users(id)`);
  } catch (e) {}
  try {
    await run(`ALTER TABLE issues ADD COLUMN resolved_at TEXT`);
  } catch (e) {}

  // Seed sample issues if empty
  const issueCount = await get(`SELECT COUNT(*) as count FROM issues`);
  if (issueCount && issueCount.count === 0) {
    const spk = await get(`SELECT id FROM work_orders LIMIT 1`);
    if (spk) {
      const nowStr = new Date().toISOString();
      await run(`
        INSERT INTO issues (work_order_id, user_id, has_issue, issue_type, notes, status, resolution_notes, resolved_by, resolved_at, created_at)
        VALUES 
        (?, 3, 1, 'AKSES_LOKASI', 'Izin kerja malam tertunda oleh security gedung cabang karena surat izin belum distempel pengelola kawasan.', 'OPEN', NULL, NULL, NULL, ?),
        (?, 3, 1, 'CUACA_BURUK', 'Hujan lebat disertai angin kencang di lokasi pada pukul 14:30 WIB sehingga pemasangan tiang signboard ditunda 2 jam demi keselamatan K3.', 'RESOLVED', 'Pekerjaan dilanjutkan setelah hujan reda pukul 16:30 WIB dengan pengawasan ketat safety officer.', 1, ?, ?)
      `, [spk.id, nowStr, spk.id, nowStr, nowStr]);
    }
  }

  // Seed default prices for standard job types
  await run(`UPDATE job_types SET standard_price = 15000000 WHERE code = 'SIGNBOARD' AND (standard_price = 0 OR standard_price IS NULL)`);
  await run(`UPDATE job_types SET standard_price = 15000000 WHERE code = 'BRANDING_FASADE' AND (standard_price = 0 OR standard_price IS NULL)`);
  await run(`UPDATE job_types SET standard_price = 8000000 WHERE code = 'STIKER_ATM' AND (standard_price = 0 OR standard_price IS NULL)`);
  await run(`UPDATE job_types SET standard_price = 15000000 WHERE (code = 'MAINTENANCE_PYLON' OR code = 'MAINTENANCE') AND (standard_price = 0 OR standard_price IS NULL)`);
  await run(`UPDATE job_types SET standard_price = 10000000 WHERE (code = 'ELECTRICAL_LED' OR code = 'KELISTRIKAN') AND (standard_price = 0 OR standard_price IS NULL)`);

  // Update clients/vendors names to real enterprise partners
  try {
    await run(`UPDATE vendors SET name = 'PT INDOMARCO PRISMATAMA (INDOMARET)', code = 'INDOMARCO' WHERE id = 1`);
    await run(`UPDATE vendors SET name = 'PT SMARTFREN TELECOM TBK', code = 'SMARTFREN' WHERE id = 2`);
    await run(`UPDATE users SET name = 'Bapak Reza (PIC Indomarco Prismatama)', email = 'reza.indomarco@sgx-partner.com' WHERE id = 6 OR email = 'vendor1@sinargraha.com'`);
    await run(`UPDATE users SET name = 'Ibu Maya (Project Manager Smartfren)', email = 'maya.smartfren@sgx-partner.com' WHERE id = 7 OR email = 'vendor2@mahakarya.com'`);
  } catch (e) {}

  // Auto-seed items for existing work orders if work_order_items is empty
  const itemCount = await get('SELECT COUNT(*) as count FROM work_order_items');
  if (itemCount && itemCount.count === 0) {
    const existingWos = await query('SELECT * FROM work_orders');
    const nowStr = new Date().toISOString();
    for (const wo of existingWos) {
      if (wo.spk_number === 'SPK-2026-00125') {
        await run(`INSERT INTO work_order_items (work_order_id, item_name, job_type_id, doc_mode, weight_percent, status, created_at) VALUES
          (?, 'Pemasangan Palang Merek Utama 6M', ?, 'BEFORE_PROCESS_AFTER', 50, 'IN_PROGRESS', ?),
          (?, 'Pengecatan Kanopi & Fasade Cabang', ?, 'BEFORE_PROCESS_AFTER', 30, 'IN_PROGRESS', ?),
          (?, 'Pemasangan Stiker Sandblast Kaca ATM', 3, 'AFTER_ONLY', 20, 'PENDING', ?)`,
          [wo.id, wo.job_type_id || 1, nowStr, wo.id, wo.job_type_id || 1, nowStr, wo.id, nowStr]
        );
      } else {
        await run(`INSERT INTO work_order_items (work_order_id, item_name, job_type_id, doc_mode, weight_percent, status, created_at) VALUES
          (?, ?, ?, ?, 100, 'PENDING', ?)`,
          [wo.id, wo.title, wo.job_type_id || 1, wo.doc_mode || 'BEFORE_PROCESS_AFTER', nowStr]
        );
      }
    }
  }

  await seedInitialData();
}

async function seedInitialData() {
  const userCount = await get('SELECT COUNT(*) as count FROM users');
  if (userCount.count > 0) {
    return; // Already seeded
  }

  console.log('Seeding initial master data and users...');
  const now = new Date().toISOString();
  const passwordHash = await bcrypt.hash('admin123', 10);

  // 1. Roles
  await run(`INSERT INTO roles (code, name, description) VALUES 
    ('SUPERUSER', 'Superuser System Administrator', 'Full system management and configuration access'),
    ('ADMIN', 'Operational Admin', 'Work order management, assignments, review, approval and BA Opname'),
    ('FIELD_TEAM', 'Field Team / Tim Lapangan', 'Field operations, check-in, photo evidence upload and task submission'),
    ('VENDOR', 'Vendor Partner', 'Vendor-isolated progress tracking and BA document viewer')
  `);

  // 2. Vendors
  const v1 = await run(`INSERT INTO vendors (code, name, contact_person, phone, email, address, is_active, created_at) VALUES 
    ('VND-001', 'PT Sinar Graha Konstruksi', 'Ir. Bambang Wijaya', '081234567890', 'bambang@sinargraha.co.id', 'Jl. Sukarno Hatta No. 45, Bandung', 1, ?)`, [now]);
  const v2 = await run(`INSERT INTO vendors (code, name, contact_person, phone, email, address, is_active, created_at) VALUES 
    ('VND-002', 'CV Mahakarya Tehnik Sentosa', 'Hendra Gunawan', '081398765432', 'hendra@mahakarya.com', 'Jl. R.E. Martadinata No. 88, Bandung', 1, ?)`, [now]);

  // 3. Areas
  const a1 = await run(`INSERT INTO areas (name, province, city, district, created_at) VALUES ('Bandung Raya', 'Jawa Barat', 'Kota Bandung', 'Coblong', ?)`, [now]);
  const a2 = await run(`INSERT INTO areas (name, province, city, district, created_at) VALUES ('Bekasi & Cikarang', 'Jawa Barat', 'Kabupaten Bekasi', 'Cikarang Selatan', ?)`, [now]);
  const a3 = await run(`INSERT INTO areas (name, province, city, district, created_at) VALUES ('Semarang Kota', 'Jawa Tengah', 'Kota Semarang', 'Banyumanik', ?)`, [now]);

  // 4. Job Types
  await run(`INSERT INTO job_types (code, name, doc_mode, min_photos_per_stage, is_active, created_at) VALUES 
    ('JOB-PALANG', 'Pemasangan Palang Merek & Signboard', 'BEFORE_PROCESS_AFTER', 3, 1, ?),
    ('JOB-MAINT', 'Maintenance & Perbaikan Rutin', 'BEFORE_PROCESS_AFTER', 3, 1, ?),
    ('JOB-SURVEY', 'Survey Lokasi Lapangan', 'AFTER_ONLY', 2, 1, ?)`, [now, now, now]);

  // 5. Users
  // Superuser
  await run(`INSERT INTO users (name, email, password_hash, phone, role, vendor_id, is_active, created_at, updated_at) VALUES 
    ('Super Admin SGX', 'superuser@sgx.com', ?, '081100000001', 'SUPERUSER', NULL, 1, ?, ?)`, [passwordHash, now, now]);

  // Operational Admin
  const adminUser = await run(`INSERT INTO users (name, email, password_hash, phone, role, vendor_id, is_active, created_at, updated_at) VALUES 
    ('Dian Anggraini (Admin)', 'admin@sgx.com', ?, '081100000002', 'ADMIN', NULL, 1, ?, ?)`, [passwordHash, now, now]);

  // Field Team Users
  const fieldPic = await run(`INSERT INTO users (name, email, password_hash, phone, role, vendor_id, is_active, created_at, updated_at) VALUES 
    ('Andi Pratama (PIC Lapangan)', 'andi.lapangan@sgx.com', ?, '081211112222', 'FIELD_TEAM', NULL, 1, ?, ?)`, [passwordHash, now, now]);
  const fieldMember1 = await run(`INSERT INTO users (name, email, password_hash, phone, role, vendor_id, is_active, created_at, updated_at) VALUES 
    ('Budi Santoso', 'budi.lapangan@sgx.com', ?, '081211113333', 'FIELD_TEAM', NULL, 1, ?, ?)`, [passwordHash, now, now]);
  const fieldMember2 = await run(`INSERT INTO users (name, email, password_hash, phone, role, vendor_id, is_active, created_at, updated_at) VALUES 
    ('Candra Wijaya', 'candra.lapangan@sgx.com', ?, '081211114444', 'FIELD_TEAM', NULL, 1, ?, ?)`, [passwordHash, now, now]);

  // Vendor Users
  await run(`INSERT INTO users (name, email, password_hash, phone, role, vendor_id, is_active, created_at, updated_at) VALUES 
    ('Bambang (Vendor PT Sinar Graha)', 'vendor1@sinargraha.com', ?, '081234567890', 'VENDOR', ?, 1, ?, ?)`, [passwordHash, v1.lastID, now, now]);
  await run(`INSERT INTO users (name, email, password_hash, phone, role, vendor_id, is_active, created_at, updated_at) VALUES 
    ('Hendra (Vendor CV Mahakarya)', 'vendor2@mahakarya.com', ?, '081398765432', 'VENDOR', ?, 1, ?, ?)`, [passwordHash, v2.lastID, now, now]);

  // 6. Field Teams
  const team1 = await run(`INSERT INTO field_teams (name, leader_user_id, area_id, is_active, created_at) VALUES 
    ('Tim Alpha Bandung', ?, ?, 1, ?)`, [fieldPic.lastID, a1.lastID, now]);
  await run(`INSERT INTO field_team_members (team_id, user_id, created_at) VALUES 
    (?, ?, ?), (?, ?, ?), (?, ?, ?)`, [team1.lastID, fieldPic.lastID, now, team1.lastID, fieldMember1.lastID, now, team1.lastID, fieldMember2.lastID, now]);

  // 7. Default Document Template
  const headerHtml = `
    <div style="border-bottom: 2px solid #1e3a8a; padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h2 style="margin: 0; color: #1e3a8a; font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">PT SINAR GRAHA KONSTRUKSI (SGX)</h2>
        <p style="margin: 2px 0 0 0; color: #64748b; font-size: 11px;">Jasa Konstruksi, Fabrikasi Signboard & Pemeliharaan Gedung</p>
        <p style="margin: 2px 0 0 0; color: #64748b; font-size: 10px;">Jl. Raya Utama Bisnis No. 108, Jakarta Selatan | Telp: (021) 789-0123</p>
      </div>
      <div style="text-align: right;">
        <span style="display: inline-block; background: #e0e7ff; color: #3730a3; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 11px;">EVIDENCE CERTIFIED</span>
      </div>
    </div>
  `;

  const footerHtml = `
    <div style="margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; font-size: 10px; color: #94a3b8; display: flex; justify-content: space-between;">
      <span>Dokumen ini diterbitkan secara resmi melalui Sistem Digital SGX Vendor Work Evidence.</span>
      <span>Otorisasi & Audit Trail Tercatat di Server</span>
    </div>
  `;

  const bodyTemplate = `
    <div style="text-align: center; margin-bottom: 20px;">
      <h3 style="margin: 0; text-decoration: underline; font-size: 15px; text-transform: uppercase;">BERITA ACARA HASIL PEKERJAAN & OPNAME LAPANGAN</h3>
      <p style="margin: 4px 0 0 0; font-size: 12px; color: #475569;">Nomor: <strong>{{ba_number}}</strong></p>
    </div>
    <p style="font-size: 12px; line-height: 1.6;">
      Pada hari ini <strong>{{ba_date_formatted}}</strong>, telah dilakukan pemeriksaan dan verifikasi lapangan atas pelaksanaan pekerjaan dengan rincian sebagai berikut:
    </p>
    <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 16px;">
      <tr><td style="width: 25%; padding: 5px 0; color: #64748b;">Nomor SPK</td><td style="font-weight: 600;">: {{spk_number}}</td></tr>
      <tr><td style="padding: 5px 0; color: #64748b;">Nama Pekerjaan</td><td style="font-weight: 600;">: {{title}}</td></tr>
      <tr><td style="padding: 5px 0; color: #64748b;">Mitra Vendor</td><td>: {{vendor_name}}</td></tr>
      <tr><td style="padding: 5px 0; color: #64748b;">Lokasi Pekerjaan</td><td>: {{location_name}} (Area: {{area_name}})</td></tr>
      <tr><td style="padding: 5px 0; color: #64748b;">PIC Pelaksana</td><td>: {{pic_name}} ({{pic_phone}})</td></tr>
      <tr><td style="padding: 5px 0; color: #64748b;">Waktu Check-In Resmi</td><td>: {{checkin_timestamp}} (GPS: {{checkin_gps}} - Akurasi: {{checkin_accuracy}}m)</td></tr>
    </table>
    <p style="font-size: 12px; line-height: 1.6;">
      Berdasarkan hasil verifikasi foto Before, Process, dan After serta pengecekan kendala teknis, pekerjaan dinyatakan <strong>TELAH SELESAI DENGAN BAIK & MEMENUHI STANDAR MUTU PEKERJAAN</strong>.
    </p>
  `;

  await run(`INSERT INTO document_templates (name, code, header_html, footer_html, body_template, is_default, created_at) VALUES 
    ('Standard BA Opname SGX', 'TMPL-BA-DEFAULT', ?, ?, ?, 1, ?)`, [headerHtml, footerHtml, bodyTemplate, now]);

  // 8. Seed Sample Work Orders
  const spk1 = await run(`INSERT INTO work_orders (spk_number, title, vendor_id, area_id, job_type_id, location_name, target_lat, target_lng, pic_user_id, start_date, deadline, doc_mode, require_checkin, status, progress_percent, notes, created_by, created_at, updated_at) VALUES 
    ('SPK-2026-00125', 'Pemasangan Palang Merek Cabang Dago Bandung', ?, ?, 1, 'Jl. Ir. H. Juanda No. 120, Dago, Bandung', -6.8850, 107.6136, ?, '2026-08-16', '2026-08-20', 'BEFORE_PROCESS_AFTER', 1, 'ASSIGNED', 25, 'Pastikan ketinggian tiang sesuai standar keselamatan 4.5 meter.', ?, ?, ?)`,
    [v1.lastID, a1.lastID, fieldPic.lastID, adminUser.lastID, now, now]
  );

  await run(`INSERT INTO work_order_assignments (work_order_id, user_id, role_in_team, assigned_at) VALUES 
    (?, ?, 'PIC', ?), (?, ?, 'MEMBER', ?)`, [spk1.lastID, fieldPic.lastID, now, spk1.lastID, fieldMember1.lastID, now]);

  const spk2 = await run(`INSERT INTO work_orders (spk_number, title, vendor_id, area_id, job_type_id, location_name, target_lat, target_lng, pic_user_id, start_date, deadline, doc_mode, require_checkin, status, progress_percent, notes, created_by, created_at, updated_at) VALUES 
    ('SPK-2026-00126', 'Maintenance Panel Listrik & Signboard Cikarang', ?, ?, 2, 'Kawasan Industri GIIC Blok AB No. 12, Cikarang', -6.3530, 107.1645, NULL, '2026-08-18', '2026-08-25', 'BEFORE_PROCESS_AFTER', 1, 'READY', 10, 'Gunakan APD lengkap dan koordinasikan dengan security kawasan.', ?, ?, ?)`,
    [v2.lastID, a2.lastID, adminUser.lastID, now, now]
  );

  // 9. System settings
  await run(`INSERT INTO system_settings (key, value, description, updated_at) VALUES 
    ('fonnte_api_key', 'FONNTE_DEMO_KEY_SGX_2026', 'API Token untuk WhatsApp Gateway Fonnte', ?),
    ('app_name', 'SGX Vendor Work Evidence', 'Nama resmi platform aplikasi', ?),
    ('require_strict_gps', '1', 'Wajibkan GPS browser terverifikasi saat check-in', ?)`, [now, now, now]);

  console.log('Database initialized and successfully seeded!');
}

module.exports = {
  db,
  query,
  get,
  run,
  exec,
  initDatabase
};
