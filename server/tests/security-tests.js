const assert = require('assert');
const path = require('path');
const dotenv = require('dotenv');

dotenv.config({ path: path.resolve(__dirname, '../../.env') });
dotenv.config();

const { initDatabase, query, get, run } = require('../src/config/database');
const authService = require('../src/services/authService');
const workOrderService = require('../src/services/workOrderService');
const evidenceService = require('../src/services/evidenceService');
const checkInService = require('../src/services/checkInService');
const baService = require('../src/services/baService');
const masterDataService = require('../src/services/masterDataService');

async function runSecurityTests() {
  console.log('================================================================');
  console.log('>>> SGX VENDOR SECURITY & BOUNDARY VERIFICATION TEST SUITE <<<');
  console.log('================================================================');

  await initDatabase();

  // Seed / Authenticate Accounts
  const adminRes = await authService.login({ email: 'admin@sgx.com', password: 'admin123', ipAddress: '127.0.0.1' });
  const adminUser = adminRes.user;

  const fieldRes = await authService.login({ email: 'andi.lapangan@sgx.com', password: 'admin123', ipAddress: '127.0.0.1' });
  const fieldUser = fieldRes.user;

  const clientRes = await authService.login({ email: 'reza.indomarco@sgx-partner.com', password: 'admin123', ipAddress: '127.0.0.1' });
  const clientUser = clientRes.user;

  // Use Budi (budi.lapangan@sgx.com) for IDOR testing
  let otherField = await get(`SELECT * FROM users WHERE email = 'budi.lapangan@sgx.com'`);
  if (!otherField) {
    const bcrypt = require('bcryptjs');
    const hash = await bcrypt.hash('field123', 10);
    const now = new Date().toISOString();
    const res = await run(
      `INSERT INTO users (name, email, password_hash, phone, role, is_active, created_at, updated_at)
       VALUES (?, ?, ?, ?, 'FIELD_TEAM', 1, ?, ?)`,
      ['Budi Teknisi', 'budi@sgx.com', hash, '081234567890', now, now]
    );
    otherField = await get(`SELECT * FROM users WHERE id = ?`, [res.lastID]);
  }

  // Ensure a second vendor exists for multi-tenant isolation testing
  let otherVendor = await get(`SELECT * FROM vendors WHERE code = 'VND-SMARTFREN'`);
  if (!otherVendor) {
    const now = new Date().toISOString();
    const res = await run(
      `INSERT INTO vendors (code, name, contact_person, phone, email, address, is_active, created_at)
       VALUES ('VND-SMARTFREN', 'PT Smartfren Telecom', 'Ibu Rina', '0811223344', 'rina@smartfren.com', 'Jakarta', 1, ?)`,
      [now]
    );
    otherVendor = await get(`SELECT * FROM vendors WHERE id = ?`, [res.lastID]);
  }

  const vendors = await query('SELECT * FROM vendors');
  const vendorA = vendors[0];
  const vendorB = otherVendor;
  const areas = await query('SELECT * FROM areas');

  console.log('\n[Security Test 1] Authentication Enforcement (Wrong Password Rejection)');
  try {
    await authService.login({ email: 'admin@sgx.com', password: 'wrongpassword999', ipAddress: '127.0.0.1' });
    assert.fail('Should have rejected invalid password');
  } catch (err) {
    assert.strictEqual(err.message, 'Email atau password salah');
    console.log('  ✓ PASSED: Invalid password rejected with security error');
  }

  console.log('\n[Security Test 2] IDOR Field Team Assignment Check (Point 1.4)');
  // Create SPK assigned only to fieldUser (Andi)
  const spk1 = await workOrderService.createWorkOrder({
    title: 'Pemasangan Pylon SPK Andi',
    vendor_id: vendorA.id,
    area_id: areas[0].id,
    location_name: 'Bandung Utara',
    target_lat: -6.9000,
    target_lng: 107.6000,
    start_date: '2026-08-17',
    deadline: '2026-08-25',
    pic_user_id: fieldUser.id
  }, adminUser, '127.0.0.1');

  // Attempt upload by unassigned otherField (Budi)
  try {
    await evidenceService.uploadPhotoEvidence({
      file: { path: __filename, filename: 'test.jpg', originalname: 'test.jpg', size: 1024, mimetype: 'image/jpeg' },
      workOrderId: spk1.id,
      stage: 'BEFORE',
      latitude: -6.9000,
      longitude: 107.6000
    }, otherField, '127.0.0.1');
    assert.fail('Should have blocked unassigned technician from uploading');
  } catch (err) {
    assert(err.message.includes('Akses ditolak'), `Expected access denied error, got: ${err.message}`);
    console.log('  ✓ PASSED: Unassigned technician blocked from uploading evidence (IDOR prevented)');
  }

  console.log('\n[Security Test 3] Multi-Vendor Data Isolation (Point 1.3 & 3.2)');
  // Create SPK for Vendor B
  const spk2 = await workOrderService.createWorkOrder({
    title: 'Pemasangan Billboard Vendor B',
    vendor_id: vendorB.id,
    area_id: areas[0].id,
    location_name: 'Jakarta Selatan',
    target_lat: -6.2000,
    target_lng: 106.8000,
    start_date: '2026-08-17',
    deadline: '2026-08-25'
  }, adminUser, '127.0.0.1');

  // Client user (Indomarco / Vendor A) tries to fetch single work order of Vendor B
  try {
    await workOrderService.getWorkOrderById(spk2.id, clientUser);
    assert.fail('Should have blocked Client A from accessing SPK of Client B');
  } catch (err) {
    assert(err.message.includes('Akses ditolak'), `Expected vendor isolation error, got: ${err.message}`);
    console.log('  ✓ PASSED: Cross-vendor work order access blocked (Vendor isolation enforced)');
  }

  // Master data vendors scoping for Vendor Client
  const vendorListForClient = await masterDataService.getVendors(clientUser);
  assert.strictEqual(vendorListForClient.length, 1, 'Client should only see their own vendor profile');
  assert.strictEqual(vendorListForClient[0].id, clientUser.vendor_id);
  console.log('  ✓ PASSED: Master data vendors scoped strictly to client vendor profile');

  console.log('\n[Security Test 4] Geofencing Check-in & Distance Validation (Point 3.1)');
  // 4a. Check-in within 200m radius -> VERIFIED_MATCH
  const checkinMatch = await checkInService.performCheckIn({
    workOrderId: spk1.id,
    latitude: -6.9001, // ~15 meters away
    longitude: 107.6001,
    accuracy: 10,
    addressNote: 'Tiba di lokasi Bandung'
  }, fieldUser, '127.0.0.1');

  assert.strictEqual(checkinMatch.is_out_of_range, 0);
  assert.strictEqual(checkinMatch.geofence_status, 'VERIFIED_MATCH');
  assert(checkinMatch.distance_meters <= 200, 'Distance should be under 200m');
  console.log(`  ✓ PASSED: Check-in within target (${checkinMatch.distance_meters}m) flagged as VERIFIED_MATCH`);

  // 4b. Check-in far away (>5 km) -> OUT_OF_RANGE
  const checkinFar = await checkInService.performCheckIn({
    workOrderId: spk1.id,
    latitude: -6.9500, // ~5.5 km away
    longitude: 107.6500,
    accuracy: 10,
    addressNote: 'Check-in dari luar radius'
  }, fieldUser, '127.0.0.1');

  assert.strictEqual(checkinFar.is_out_of_range, 1);
  assert.strictEqual(checkinFar.geofence_status, 'OUT_OF_RANGE');
  assert(checkinFar.distance_meters > 200, 'Distance should exceed 200m');
  console.log(`  ✓ PASSED: Check-in out-of-range (${checkinFar.distance_meters}m) correctly flagged as OUT_OF_RANGE`);

  console.log('\n================================================================');
  console.log('>>> ALL 4 SECURITY & BOUNDARY TEST SCENARIOS PASSED (100%) <<<');
  console.log('================================================================\n');
}

if (require.main === module) {
  runSecurityTests()
    .then(() => process.exit(0))
    .catch((err) => {
      console.error('\n❌ Security Test Failed:', err);
      process.exit(1);
    });
}

module.exports = { runSecurityTests };
