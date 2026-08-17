/**
 * SGX Vendor Work Evidence — Unified App Launcher
 * Automatically starts Backend API Server (port 5000) and Frontend Client (port 3000)
 */

const { spawn, exec } = require('child_process');
const path = require('path');

const rootDir = __dirname;
const serverDir = path.join(rootDir, 'server');
const clientDir = path.join(rootDir, 'client');

console.log('\x1b[36m%s\x1b[0m', '=======================================================');
console.log('\x1b[33m%s\x1b[0m', '  🚀 SGX VENDOR WORK EVIDENCE — LAUNCHING APPLICATION');
console.log('\x1b[36m%s\x1b[0m', '=======================================================');

// 1. Start Backend API Server
console.log('\x1b[32m%s\x1b[0m', '▶ Memulai Backend API Server (Port 5000)...');
const isWin = process.platform === 'win32';
const npmCmd = isWin ? 'npm.cmd' : 'npm';

const serverProcess = spawn(npmCmd, ['start'], {
  cwd: serverDir,
  shell: true,
  stdio: 'pipe'
});

serverProcess.stdout.on('data', (data) => {
  const line = data.toString().trim();
  if (line) {
    console.log('\x1b[32m[SERVER]\x1b[0m %s', line);
  }
});

serverProcess.stderr.on('data', (data) => {
  const line = data.toString().trim();
  if (line) {
    console.error('\x1b[31m[SERVER ERROR]\x1b[0m %s', line);
  }
});

// 2. Start Frontend Vite Client
console.log('\x1b[35m%s\x1b[0m', '▶ Memulai Frontend Web Client (Port 3000)...');

const clientProcess = spawn(npmCmd, ['run', 'dev'], {
  cwd: clientDir,
  shell: true,
  stdio: 'pipe'
});

clientProcess.stdout.on('data', (data) => {
  const line = data.toString().trim();
  if (line) {
    console.log('\x1b[35m[CLIENT]\x1b[0m %s', line);
  }
});

clientProcess.stderr.on('data', (data) => {
  const line = data.toString().trim();
  if (line) {
    console.error('\x1b[31m[CLIENT ERROR]\x1b[0m %s', line);
  }
});

// 3. Open Browser automatically after 2.5 seconds
setTimeout(() => {
  console.log('\n\x1b[36m%s\x1b[0m', '=======================================================');
  console.log('\x1b[32m%s\x1b[0m', '  ✅ APLIKASI SUDAH AKTIF DAN SIAP DIGUNAKAN:');
  console.log('\x1b[37m%s\x1b[0m', '  🌐 Frontend Client : http://localhost:3000');
  console.log('\x1b[37m%s\x1b[0m', '  🔌 Backend API     : http://localhost:5000');
  console.log('\x1b[36m%s\x1b[0m', '=======================================================\n');

  const startCmd = isWin ? 'start http://localhost:3000' : 'open http://localhost:3000';
  exec(startCmd, () => {});
}, 2500);

// Handle graceful exit
function cleanExit() {
  console.log('\n\x1b[33m%s\x1b[0m', '⏹ Menghentikan seluruh service SGX Vendor...');
  try {
    if (serverProcess) serverProcess.kill();
    if (clientProcess) clientProcess.kill();
  } catch (e) {}
  process.exit();
}

process.on('SIGINT', cleanExit);
process.on('SIGTERM', cleanExit);
