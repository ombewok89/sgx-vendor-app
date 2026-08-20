const express = require('express');
const cors = require('cors');
const path = require('path');
const dotenv = require('dotenv');
const jwt = require('jsonwebtoken');
const { initDatabase } = require('./config/database');
const { getJwtSecret } = require('./middleware/auth');

// Load environment configuration (.env at project root and server root)
dotenv.config({ path: path.resolve(__dirname, '../../.env') });
dotenv.config();

const app = express();
const PORT = process.env.PORT || 5000;

// Enable Strict CORS with Whitelist Origin (Point 2.2)
const defaultAllowed = [
  'http://localhost:3000',
  'http://127.0.0.1:3000',
  'http://localhost:5000',
  'https://vendor.sinargrafika.my.id',
  'http://vendor.sinargrafika.my.id',
  'https://sinargrafika.my.id',
  'https://sgx-vendor-app-production.up.railway.app'
];

const rawOrigins = process.env.CORS_ORIGIN ? process.env.CORS_ORIGIN.split(',').map(o => o.trim()).filter(Boolean) : [];
const allowedOrigins = Array.from(new Set([...defaultAllowed, ...rawOrigins]));

app.use(cors({
  origin: function (origin, callback) {
    // Allow non-browser requests (mobile clients, curl, server-to-server)
    if (!origin) return callback(null, true);
    if (
      allowedOrigins.includes('*') ||
      allowedOrigins.includes(origin) ||
      origin.endsWith('sinargrafika.my.id') ||
      origin.endsWith('up.railway.app')
    ) {
      return callback(null, true);
    }
    return callback(new Error(`CORS Policy: Origin '${origin}' is not allowed by CORS_ORIGIN whitelist.`));
  },
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization']
}));

// Body parsers
app.use(express.json({ limit: '15mb' }));
app.use(express.urlencoded({ extended: true, limit: '15mb' }));

// Secure Static Uploads Directory (Point 2.3)
const uploadsDir = process.env.UPLOADS_DIR || path.resolve(__dirname, '../../uploads');

function secureUploadsAccess(req, res, next) {
  // 1. Allow public assets such as template logos and headers
  if (req.path.startsWith('/templates') || req.path.includes('logo') || req.path.includes('default')) {
    return next();
  }

  // 2. Allow if valid token provided via Header or Query param
  const authHeader = req.headers.authorization;
  let token = null;
  if (authHeader && authHeader.startsWith('Bearer ')) {
    token = authHeader.split(' ')[1];
  } else if (req.query && req.query.token) {
    token = req.query.token;
  }

  if (token) {
    try {
      jwt.verify(token, getJwtSecret());
      return next();
    } catch (err) {}
  }

  // 3. Allow if request comes from authorized domain / referer (browser img tags)
  const referer = req.headers.referer || req.headers.origin || '';
  if (
    referer.includes('vendor.sinargrafika.my.id') ||
    referer.includes('sinargrafika.my.id') ||
    referer.includes('localhost') ||
    referer.includes('up.railway.app')
  ) {
    return next();
  }

  return res.status(401).json({
    success: false,
    message: 'Akses Ditolak: Diperlukan token autentikasi untuk mengakses foto bukti pekerjaan.'
  });
}

app.use('/uploads', secureUploadsAccess, express.static(uploadsDir));

// Route Handlers
const authRoutes = require('./routes/authRoutes');
const workOrderRoutes = require('./routes/workOrderRoutes');
const checkInRoutes = require('./routes/checkInRoutes');
const evidenceRoutes = require('./routes/evidenceRoutes');
const reviewRoutes = require('./routes/reviewRoutes');
const baRoutes = require('./routes/baRoutes');
const masterDataRoutes = require('./routes/masterDataRoutes');
const reportRoutes = require('./routes/reportRoutes');
const systemRoutes = require('./routes/systemRoutes');
const permissionRoutes = require('./routes/permissionRoutes');
const notificationFeedRoutes = require('./routes/notificationFeedRoutes');

app.use('/api/auth', authRoutes);
app.use('/api/work-orders', workOrderRoutes);
app.use('/api/check-ins', checkInRoutes);
app.use('/api/evidence', evidenceRoutes);
app.use('/api/reviews', reviewRoutes);
app.use('/api/ba', baRoutes);
app.use('/api/master', masterDataRoutes);
app.use('/api/reports', reportRoutes);
app.use('/api/system', systemRoutes);
app.use('/api/permissions', permissionRoutes);
app.use('/api/notifications-feed', notificationFeedRoutes);

// Health check endpoint
app.get('/api/health', (req, res) => {
  res.json({
    status: 'online',
    app: 'SGX Vendor Work Evidence API',
    timestamp: new Date().toISOString(),
    environment: process.env.NODE_ENV || 'development'
  });
});

// Serve built client frontend static files (if available)
const clientDistPath = path.resolve(__dirname, '../../client/dist');
const rootDistPath = path.resolve(__dirname, '../../');

if (fs.existsSync(path.join(clientDistPath, 'index.html'))) {
  app.use(express.static(clientDistPath));
  app.get('*', (req, res, next) => {
    if (req.path.startsWith('/api') || req.path.startsWith('/uploads')) {
      return next();
    }
    res.sendFile(path.join(clientDistPath, 'index.html'));
  });
} else if (fs.existsSync(path.join(rootDistPath, 'index.html'))) {
  app.use(express.static(rootDistPath));
  app.get('*', (req, res, next) => {
    if (req.path.startsWith('/api') || req.path.startsWith('/uploads')) {
      return next();
    }
    res.sendFile(path.join(rootDistPath, 'index.html'));
  });
}

// Global Error Handler
app.use((err, req, res, next) => {
  console.error('[Unhandled Server Error]:', err);
  res.status(500).json({
    success: false,
    message: err.message || 'Internal Server Error'
  });
});

// Initialize database schema and start server
async function startServer() {
  try {
    // Validate critical security env vars
    getJwtSecret();
    await initDatabase();
    app.listen(PORT, () => {
      console.log(`====================================================`);
      console.log(`SGX Vendor Work Evidence Backend API Server`);
      console.log(`Environment: ${process.env.NODE_ENV || 'development'}`);
      console.log(`Status: Running on http://localhost:${PORT}`);
      console.log(`API Health: http://localhost:${PORT}/api/health`);
      console.log(`CORS Whitelist: ${allowedOrigins.join(', ')}`);
      console.log(`====================================================`);
    });
  } catch (error) {
    console.error('Fatal: Server startup failed:', error.message);
    process.exit(1);
  }
}

startServer();

module.exports = app;
