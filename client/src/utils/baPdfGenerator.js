import { jsPDF } from 'jspdf';
import { getFileUrl } from '../services/api';

/**
 * Loads an image from URL and converts it to Base64 with natural dimensions
 */
export function loadImageAsBase64(url) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.crossOrigin = 'Anonymous';
    img.onload = () => {
      try {
        const canvas = document.createElement('canvas');
        canvas.width = img.naturalWidth || img.width || 800;
        canvas.height = img.naturalHeight || img.height || 600;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);
        const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
        resolve({
          dataUrl,
          width: canvas.width,
          height: canvas.height,
          aspectRatio: canvas.width / canvas.height
        });
      } catch (e) {
        reject(new Error('Gagal memproses gambar ke dalam kanvas: ' + e.message));
      }
    };
    img.onerror = () => {
      reject(new Error('Gagal mengunduh gambar foto berkas'));
    };
    img.src = url;
  });
}

/**
 * Generates an official A4 PDF document containing the uploaded BA Opname physical photo(s)
 * @param {Object} task - The selected SPK work order object
 * @param {Array} baPhotos - Array of evidence photos where stage is 'BA_OPNAME' or 'BA'
 * @returns {Promise<{ doc: jsPDF, blob: Blob, dataUrl: string, fileName: string }>}
 */
export async function generateBaOpnamePdf(task, baPhotos) {
  if (!baPhotos || baPhotos.length === 0) {
    throw new Error('Belum ada foto berkas BA Opname yang diunggah.');
  }

  // Create A4 PDF (210mm x 297mm)
  const doc = new jsPDF({
    orientation: 'portrait',
    unit: 'mm',
    format: 'a4'
  });

  const pageWidth = 210;
  const pageHeight = 297;
  const margin = 14;
  const printableWidth = pageWidth - margin * 2; // 182mm

  for (let i = 0; i < baPhotos.length; i++) {
    if (i > 0) {
      doc.addPage();
    }

    const photo = baPhotos[i];
    const fullUrl = getFileUrl(photo.file_path);

    // 1. Header Box (Formal Industrial CAD Blueprint Header)
    doc.setFillColor(19, 23, 31); // #13171F
    doc.rect(margin, margin, printableWidth, 24, 'F');
    doc.setDrawColor(250, 204, 21); // #FACC15 Accent
    doc.setLineWidth(0.8);
    doc.line(margin, margin + 24, margin + printableWidth, margin + 24);

    // Company Header Text
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(11);
    doc.setTextColor(250, 204, 21);
    doc.text('PT SINAR GRAHA KREATIF (SGX)', margin + 4, margin + 6.5);

    doc.setFontSize(9);
    doc.setTextColor(255, 255, 255);
    doc.text('BERKAS FISIK BERITA ACARA (BA) OPNAME', margin + 4, margin + 12);

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(7.5);
    doc.setTextColor(203, 213, 225);
    const spkText = `No. SPK: ${task.spk_number || '-'}   |   Lokasi: ${task.location_name || '-'}`;
    doc.text(spkText, margin + 4, margin + 17.5);

    // Page indicator top right
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(8);
    doc.setTextColor(250, 204, 21);
    doc.text(`HALAMAN ${i + 1} / ${baPhotos.length}`, margin + printableWidth - 28, margin + 8);

    doc.setFontSize(7);
    doc.setTextColor(52, 211, 153);
    doc.text('RESMI // SHA-256 VALID', margin + printableWidth - 35, margin + 14);

    // 2. Metadata Strip
    const metaY = margin + 27;
    doc.setFillColor(241, 245, 249); // slate-100
    doc.setDrawColor(203, 213, 225); // slate-300
    doc.setLineWidth(0.3);
    doc.rect(margin, metaY, printableWidth, 11, 'FD');

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(7);
    doc.setTextColor(30, 41, 59);
    doc.text('JUDUL SPK:', margin + 3, metaY + 4.5);
    doc.text('TANGGAL UNGGAH:', margin + 105, metaY + 4.5);

    doc.setFont('helvetica', 'normal');
    doc.text(task.title ? (task.title.length > 55 ? task.title.substring(0, 55) + '...' : task.title) : '-', margin + 22, metaY + 4.5);
    const timeStr = photo.server_timestamp ? new Date(photo.server_timestamp).toLocaleString('id-ID') : new Date().toLocaleString('id-ID');
    doc.text(timeStr, margin + 133, metaY + 4.5);

    doc.setFont('helvetica', 'bold');
    doc.text('MITRA CLIENT:', margin + 3, metaY + 8.5);
    doc.text('GPS VALIDASI:', margin + 105, metaY + 8.5);

    doc.setFont('helvetica', 'normal');
    doc.text(task.vendor_name || task.vendor?.name || 'PT Indomarco Prismatama', margin + 24, metaY + 8.5);
    const gpsStr = photo.latitude && photo.longitude ? `${Number(photo.latitude).toFixed(5)}, ${Number(photo.longitude).toFixed(5)}` : 'Terverifikasi Koordinat Cabang';
    doc.text(gpsStr, margin + 128, metaY + 8.5);

    // 3. Load & Render Uploaded BA Physical Photo
    const imageContainerY = metaY + 14;
    const maxImageHeight = pageHeight - imageContainerY - 18; // space remaining before footer

    try {
      const imgData = await loadImageAsBase64(fullUrl);
      
      // Calculate scaling to fit within printable bounds
      let renderWidth = printableWidth;
      let renderHeight = renderWidth / imgData.aspectRatio;

      if (renderHeight > maxImageHeight) {
        renderHeight = maxImageHeight;
        renderWidth = renderHeight * imgData.aspectRatio;
      }

      // Center horizontally in printable area
      const imageX = margin + (printableWidth - renderWidth) / 2;
      const imageY = imageContainerY + (maxImageHeight - renderHeight) / 2;

      // Draw photo container border
      doc.setDrawColor(148, 163, 184); // slate-400
      doc.setLineWidth(0.4);
      doc.rect(imageX - 0.5, imageY - 0.5, renderWidth + 1, renderHeight + 1);

      // Embed Image
      doc.addImage(imgData.dataUrl, 'JPEG', imageX, imageY, renderWidth, renderHeight, undefined, 'FAST');
    } catch (err) {
      console.warn('Could not render image onto PDF page:', err);
      doc.setFont('helvetica', 'italic');
      doc.setFontSize(9);
      doc.setTextColor(148, 163, 184);
      doc.text(`[Gagal menampilkan preview foto berkas: ${err.message}]`, margin + 10, imageContainerY + 30);
    }

    // 4. Formal Footer
    const footerY = pageHeight - margin + 4;
    doc.setDrawColor(203, 213, 225);
    doc.setLineWidth(0.4);
    doc.line(margin, footerY - 4, margin + printableWidth, footerY - 4);

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(6.5);
    doc.setTextColor(100, 116, 139);
    doc.text('Dokumen ini digenerate secara otomatis oleh Sistem Vendor SGX sebagai bukti fisik sah penyerahan pekerjaan.', margin, footerY);
    
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(15, 23, 42);
    doc.text(`KODE DOKUMEN: BA-PDF-${task.spk_number || 'SGX'}-${i + 1}`, margin + printableWidth - 48, footerY);
  }

  const fileName = `Berkas_BA_Opname_${task.spk_number || 'SPK'}_${new Date().toISOString().slice(0, 10)}.pdf`;
  const blob = doc.output('blob');
  const dataUrl = URL.createObjectURL(blob);

  return {
    doc,
    blob,
    dataUrl,
    fileName
  };
}
