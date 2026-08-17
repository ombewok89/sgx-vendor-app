const assert = require('assert');
const path = require('path');
const dotenv = require('dotenv');

dotenv.config({ path: path.resolve(__dirname, '../../.env') });
dotenv.config();

const { initDatabase, query, get, run } = require('../src/config/database');
const authService = require('../src/services/authService');
const workOrderService = require('../src/services/workOrderService');
const checkInService = require('../src/services/checkInService');
const reviewService = require('../src/services/reviewService');
const baService = require('../src/services/baService');

async function runTests() {
  console.log('>>> STARTING AUTOMATED BACKEND VERIFICATION SUITE <<<');
  await initDatabase();

  // Test 1: User Login
  console.log('\n[Test 1] User Authentication & Token Generation');
  const loginRes = await authService.login({
    email: 'admin@sgx.com',
    password: 'admin123',
    ipAddress: '127.0.0.1'
  });
  assert(loginRes.token, 'Token should be generated');
  assert.strictEqual(loginRes.user.role, 'ADMIN', 'Role should be ADMIN');
  console.log('✓ Passed: Admin login successful with valid JWT');

  // Test 2: Field Team Authentication
  console.log('\n[Test 2] Field Team Authentication');
  const fieldRes = await authService.login({
    email: 'andi.lapangan@sgx.com',
    password: 'admin123',
    ipAddress: '127.0.0.1'
  });
  const fieldSwitch = fieldRes;
  assert.strictEqual(fieldSwitch.user.role, 'FIELD_TEAM');
  console.log('✓ Passed: Field Team authenticated successfully');

  // Test 3: Create Work Order (SPK)
  console.log('\n[Test 3] Create Work Order (SPK)');
  const adminUser = loginRes.user;
  const vendors = await query('SELECT * FROM vendors');
  const areas = await query('SELECT * FROM areas');
  const newSpk = await workOrderService.createWorkOrder({
    title: 'Uji Pemasangan Billboard Test 01',
    vendor_id: vendors[0].id,
    area_id: areas[0].id,
    location_name: 'Jl. R.E. Martadinata No. 10, Bandung',
    target_lat: -6.9050,
    target_lng: 107.6150,
    start_date: '2026-08-16',
    deadline: '2026-08-22',
    doc_mode: 'BEFORE_PROCESS_AFTER',
    notes: 'Test execution workflow'
  }, adminUser, '127.0.0.1');
  assert(newSpk.id, 'Work order ID should exist');
  assert.strictEqual(newSpk.status, 'READY', 'Status should initially be READY');
  console.log(`✓ Passed: Work Order created [${newSpk.spk_number}] with status READY`);

  // Test 4: Assign Team
  console.log('\n[Test 4] Assign PIC and Team Member');
  const fieldUser = fieldSwitch.user;
  const assigned = await workOrderService.assignTeam(newSpk.id, {
    picUserId: fieldUser.id,
    memberUserIds: [fieldUser.id]
  }, adminUser, '127.0.0.1');
  assert.strictEqual(assigned.status, 'ASSIGNED', 'Status should change to ASSIGNED');
  assert.strictEqual(assigned.pic_user_id, fieldUser.id);
  console.log(`✓ Passed: Team assigned to SPK, status progressed to ASSIGNED`);

  // Test 5: GPS Check-in
  console.log('\n[Test 5] GPS Check-in by Field Team');
  const checkInRes = await checkInService.performCheckIn({
    workOrderId: newSpk.id,
    latitude: -6.9052,
    longitude: 107.6151,
    accuracy: 15,
    clientTimestamp: new Date().toISOString(),
    addressNote: 'Tiba di lokasi tiang billboard'
  }, fieldUser, '127.0.0.1');
  assert(checkInRes.id, 'Check-in ID should exist');
  const updatedAfterCheckin = await workOrderService.getWorkOrderById(newSpk.id, fieldUser);
  assert.strictEqual(updatedAfterCheckin.status, 'CHECKED_IN', 'Status should change to CHECKED_IN');
  console.log(`✓ Passed: GPS Check-in recorded, status progressed to CHECKED_IN`);

  // Test 6: Validation Gate for Submission (Must fail without photos)
  console.log('\n[Test 6] Server Validation Gate: Block submission without required photos');
  let threw = false;
  try {
    await workOrderService.submitWorkOrder(newSpk.id, fieldUser, '127.0.0.1');
  } catch (err) {
    threw = true;
    console.log(`✓ Passed: Server-side validation gate correctly blocked incomplete submission: "${err.message}"`);
  }
  assert(threw, 'Should throw error when photos are missing');

  // Test 7: Vendor Data Isolation
  console.log('\n[Test 7] Vendor Data Isolation');
  const vendorUser = await get(`SELECT * FROM users WHERE role = 'VENDOR' LIMIT 1`);
  const vendorOrders = await workOrderService.getWorkOrders({ currentUser: vendorUser });
  for (const order of vendorOrders) {
    assert.strictEqual(order.vendor_id, vendorUser.vendor_id, 'Vendor must only see work orders for their vendor_id');
  }
  console.log(`✓ Passed: Vendor privacy isolation verified (${vendorOrders.length} orders strictly scoped)`);

  console.log('\n====================================================');
  console.log('>>> ALL AUTOMATED BACKEND TESTS PASSED (100%) <<<');
  console.log('====================================================\n');
}

runTests().catch(err => {
  console.error('Test Failed:', err);
  process.exit(1);
});
