const fs = require('fs');
const path = require('path');
const { run, query } = require('../src/config/database');

async function fixPhotos() {
  const uploadsDir = path.resolve(__dirname, '../../uploads');
  const genDir = path.join(uploadsDir, 'general');

  if (fs.existsSync(genDir)) {
    const files = fs.readdirSync(genDir);
    for (const f of files) {
      const src = path.join(genDir, f);
      const dest = path.join(uploadsDir, f);
      fs.copyFileSync(src, dest);
      console.log('Copied to root uploads:', f);
    }
  }

  const photos = await query('SELECT * FROM evidence_photos');
  for (const p of photos) {
    const filename = path.basename(p.file_path);
    const newPath = `/uploads/${filename}`;
    await run('UPDATE evidence_photos SET file_path = ? WHERE id = ?', [newPath, p.id]);
    console.log(`Updated photo #${p.id} path to: ${newPath}`);
  }

  const updated = await query('SELECT id, stage, file_path, file_name FROM evidence_photos');
  console.log('Current DB Evidence Photos:', updated);
}

fixPhotos().then(() => {
  console.log('Fix complete!');
  process.exit(0);
}).catch(err => {
  console.error(err);
  process.exit(1);
});
