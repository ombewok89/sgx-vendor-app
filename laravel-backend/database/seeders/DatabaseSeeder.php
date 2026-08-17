<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Area;
use App\Models\JobType;
use App\Models\FieldTeam;
use App\Models\DocumentTemplate;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles (Spatie)
        $roles = [
            'SUPERUSER' => Role::firstOrCreate(['name' => 'SUPERUSER', 'guard_name' => 'sanctum']),
            'ADMIN' => Role::firstOrCreate(['name' => 'ADMIN', 'guard_name' => 'sanctum']),
            'FIELD_TEAM' => Role::firstOrCreate(['name' => 'FIELD_TEAM', 'guard_name' => 'sanctum']),
            'VENDOR' => Role::firstOrCreate(['name' => 'VENDOR', 'guard_name' => 'sanctum']),
            'CLIENT' => Role::firstOrCreate(['name' => 'CLIENT', 'guard_name' => 'sanctum']),
        ];

        // 2. Vendors
        $v1 = Vendor::firstOrCreate(['code' => 'VND-001'], [
            'name' => 'PT Sinar Graha Konstruksi',
            'contact_person' => 'Dian Anggraini',
            'phone' => '081100000002',
            'email' => 'admin@sgx.com',
            'address' => 'Jl. Percetakan Grafika No. 8 Bandung',
            'is_active' => true,
        ]);

        $v2 = Vendor::firstOrCreate(['code' => 'VND-002'], [
            'name' => 'CV Bintang Mandiri Jaya',
            'contact_person' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@bintangmandiri.com',
            'address' => 'Jl. Soekarno Hatta No. 120 Bandung',
            'is_active' => true,
        ]);

        $v3 = Vendor::firstOrCreate(['code' => 'VND-003'], [
            'name' => 'PT Mitra Perkasa Abadi',
            'contact_person' => 'Hendra Gunawan',
            'phone' => '081398765432',
            'email' => 'hendra@mitraperkasa.com',
            'address' => 'Kawasan Industri Jababeka Cikarang',
            'is_active' => true,
        ]);

        // 3. Areas
        $a1 = Area::firstOrCreate(['name' => 'Area Bandung Raya'], [
            'province' => 'Jawa Barat',
            'city' => 'Kota Bandung',
            'district' => 'Coblong',
        ]);

        $a2 = Area::firstOrCreate(['name' => 'Area Jabodetabek'], [
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'district' => 'Kebayoran Baru',
        ]);

        $a3 = Area::firstOrCreate(['name' => 'Area Priangan Timur'], [
            'province' => 'Jawa Barat',
            'city' => 'Kota Tasikmalaya',
            'district' => 'Cihideung',
        ]);

        // 4. Job Types
        $jt1 = JobType::firstOrCreate(['code' => 'JT-STICKER'], [
            'name' => 'Pemasangan Sticker & Branding',
            'doc_mode' => 'BEFORE_PROCESS_AFTER',
            'min_photos_per_stage' => 3,
            'is_active' => true,
        ]);

        $jt2 = JobType::firstOrCreate(['code' => 'JT-NEONBOX'], [
            'name' => 'Pemasangan Neonbox & Signage',
            'doc_mode' => 'BEFORE_PROCESS_AFTER',
            'min_photos_per_stage' => 3,
            'is_active' => true,
        ]);

        $jt3 = JobType::firstOrCreate(['code' => 'JT-FACADE'], [
            'name' => 'Renovasi Facade & Cat Dinding',
            'doc_mode' => 'BEFORE_PROCESS_AFTER',
            'min_photos_per_stage' => 4,
            'is_active' => true,
        ]);

        $jt4 = JobType::firstOrCreate(['code' => 'JT-MAINTENANCE'], [
            'name' => 'Perawatan & Pembersihan Rutin',
            'doc_mode' => 'AFTER_ONLY',
            'min_photos_per_stage' => 1,
            'is_active' => true,
        ]);

        // 5. Users
        $defaultPassword = Hash::make('admin123');

        $uSuper = User::firstOrCreate(['email' => 'superuser@sgx.com'], [
            'name' => 'Super Admin SGX',
            'password' => $defaultPassword,
            'phone' => '081100000001',
            'is_active' => true,
        ]);
        $uSuper->syncRoles(['SUPERUSER']);

        $uAdmin = User::firstOrCreate(['email' => 'admin@sgx.com'], [
            'name' => 'Dian Anggraini (Admin)',
            'password' => $defaultPassword,
            'phone' => '081100000002',
            'is_active' => true,
        ]);
        $uAdmin->syncRoles(['ADMIN']);

        $uField1 = User::firstOrCreate(['email' => 'andi.lapangan@sgx.com'], [
            'name' => 'Andi Pratama (PIC Lapangan)',
            'password' => $defaultPassword,
            'phone' => '081211112222',
            'vendor_id' => $v1->id,
            'is_active' => true,
        ]);
        $uField1->syncRoles(['FIELD_TEAM']);

        $uField2 = User::firstOrCreate(['email' => 'budi.lapangan@sgx.com'], [
            'name' => 'Budi Santoso (Teknisi Lapangan)',
            'password' => $defaultPassword,
            'phone' => '081233334444',
            'vendor_id' => $v1->id,
            'is_active' => true,
        ]);
        $uField2->syncRoles(['FIELD_TEAM']);

        $uVendor1 = User::firstOrCreate(['email' => 'vendor@sgx.com'], [
            'name' => 'PT Sinar Graha (Vendor Management)',
            'password' => $defaultPassword,
            'phone' => '081355556666',
            'vendor_id' => $v1->id,
            'is_active' => true,
        ]);
        $uVendor1->syncRoles(['VENDOR']);

        $uVendor2 = User::firstOrCreate(['email' => 'vendor.bintang@sgx.com'], [
            'name' => 'CV Bintang Mandiri (Vendor)',
            'password' => $defaultPassword,
            'phone' => '081234567890',
            'vendor_id' => $v2->id,
            'is_active' => true,
        ]);
        $uVendor2->syncRoles(['VENDOR']);

        $uClient = User::firstOrCreate(['email' => 'client@sgx.com'], [
            'name' => 'Indomaret / Alfamart (Client QA)',
            'password' => $defaultPassword,
            'phone' => '081177778888',
            'is_active' => true,
        ]);
        $uClient->syncRoles(['CLIENT']);

        // 6. Field Team
        $team = FieldTeam::firstOrCreate(['name' => 'Tim Rebranding Bandung Alpha'], [
            'leader_user_id' => $uField1->id,
            'area_id' => $a1->id,
            'is_active' => true,
        ]);
        $team->members()->syncWithoutDetaching([$uField1->id, $uField2->id]);

        // 7. Document Template BA
        DocumentTemplate::firstOrCreate(['code' => 'TPL-DEFAULT-BA'], [
            'name' => 'Template Standar Berita Acara SGX',
            'header_html' => 'Pada hari ini <strong>{{ba_date}}</strong>, telah dilakukan pemeriksaan dan verifikasi lapangan atas pelaksanaan seluruh item pekerjaan untuk <strong>{{title}}</strong> di lokasi <strong>{{location_name}}</strong> dengan rincian sebagai berikut:',
            'footer_html' => 'Demikian Berita Acara Serah Terima ini dibuat dalam rangkap 2 (dua) untuk dipergunakan sebagaimana mestinya.',
            'body_template' => 'Berdasarkan hasil pemeriksaan bukti foto digital (Before, Process, After) dan verifikasi teknis di lapangan, kedua belah pihak menyatakan bahwa seluruh item pekerjaan telah <strong>SELESAI 100% SECARA BAIK DAN MEMENUHI SPESIFIKASI MUTU</strong>.<br><br>Mitra Vendor memberikan jaminan masa pemeliharaan (garansi mutu) selama <strong>90 (sembilan puluh) hari kalender</strong> terhitung sejak tanggal penandatanganan Berita Acara ini.',
            'is_default' => true,
        ]);

        // 8. Sample Work Orders
        $wo1 = WorkOrder::firstOrCreate(['spk_number' => 'SPK-SGX-20260815-0001'], [
            'title' => 'Rebranding Toko Cabang Dago Bandung',
            'vendor_id' => $v1->id,
            'area_id' => $a1->id,
            'job_type_id' => $jt1->id,
            'location_name' => 'Indomaret Point Dago Heritage No. 45',
            'target_lat' => -6.8850000,
            'target_lng' => 107.6150000,
            'pic_user_id' => $uField1->id,
            'start_date' => '2026-08-15',
            'deadline' => '2026-08-25',
            'doc_mode' => 'BEFORE_PROCESS_AFTER',
            'require_checkin' => true,
            'status' => 'IN_PROGRESS',
            'progress_percent' => 50,
            'notes' => 'Pemasangan sticker wall visual + sign blade LED',
            'created_by' => $uAdmin->id,
        ]);
        $wo1->assignments()->syncWithoutDetaching([
            $uField1->id => ['role_in_team' => 'PIC', 'assigned_at' => now()],
            $uField2->id => ['role_in_team' => 'MEMBER', 'assigned_at' => now()],
        ]);

        WorkOrderItem::firstOrCreate(['work_order_id' => $wo1->id, 'item_name' => 'Pemasangan Sign Blade LED'], [
            'job_type_id' => $jt2->id,
            'doc_mode' => 'BEFORE_PROCESS_AFTER',
            'weight_percent' => 50,
            'status' => 'IN_PROGRESS',
        ]);

        WorkOrderItem::firstOrCreate(['work_order_id' => $wo1->id, 'item_name' => 'Branding Kaca Depan Full Sticker'], [
            'job_type_id' => $jt1->id,
            'doc_mode' => 'BEFORE_PROCESS_AFTER',
            'weight_percent' => 50,
            'status' => 'PENDING',
        ]);
    }
}
