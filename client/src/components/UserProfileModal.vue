<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-[9999] overflow-y-auto flex items-center justify-center p-4 sm:p-6 bg-slate-950/80 backdrop-blur-md animate-fade-in">
      <div class="relative w-full max-w-xl bg-slate-900 border border-slate-700/80 rounded-3xl shadow-2xl overflow-hidden text-slate-100 animate-scale-up my-auto">
      <!-- Ambient Glow Decorator -->
      <div class="absolute -top-16 -right-16 w-40 h-40 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-16 -left-16 w-40 h-40 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

      <!-- Header -->
      <div class="p-6 sm:p-7 border-b border-slate-800 flex items-start justify-between relative z-10 bg-slate-900/60 backdrop-blur-md">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-purple-900/30 border border-white/20 shrink-0">
            {{ user?.name?.charAt(0) || 'U' }}
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-lg sm:text-xl font-black text-white tracking-tight">{{ user?.name || 'Profil Pengguna' }}</h2>
              <span :class="['text-[10px] font-mono font-bold px-2 py-0.5 rounded-full border', roleBadgeClass]">
                {{ user?.role }}
              </span>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">{{ user?.vendor_name || (user?.role === 'VENDOR' ? 'Perusahaan Client' : 'PT Sinar Grafika (SGX)') }}</p>
          </div>
        </div>

        <button
          @click="$emit('close')"
          class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 active:scale-95 transition-all cursor-pointer border border-transparent hover:border-slate-700"
          title="Tutup Modal"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Form Body -->
      <form @submit.prevent="handleSaveProfile" class="p-6 sm:p-7 space-y-6 relative z-10 max-h-[75vh] overflow-y-auto custom-scrollbar">
        <!-- Success Alert -->
        <div v-if="successMsg" class="p-3.5 bg-emerald-500/15 border border-emerald-500/30 rounded-2xl text-emerald-300 text-xs flex items-center gap-2.5 animate-fade-in">
          <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
          <span>{{ successMsg }}</span>
        </div>

        <!-- Error Alert -->
        <div v-if="errorMsg" class="p-3.5 bg-rose-500/15 border border-rose-500/30 rounded-2xl text-rose-300 text-xs flex items-center gap-2.5 animate-shake">
          <AlertCircle class="w-4 h-4 text-rose-400 shrink-0" />
          <span>{{ errorMsg }}</span>
        </div>

        <!-- Section 1: Informasi Pribadi & Kontak -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 text-xs font-bold text-slate-300 pb-1 border-b border-slate-800">
            <User class="w-4 h-4 text-purple-400" />
            <span>Data Pribadi & Kontak Akun</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Nama Lengkap -->
            <div class="space-y-1.5 sm:col-span-2">
              <label class="block text-xs font-bold text-slate-300">Nama Lengkap</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                  <User class="w-4 h-4" />
                </div>
                <input
                  type="text"
                  required
                  v-model="form.name"
                  placeholder="Masukkan nama lengkap Anda"
                  class="w-full pl-9 pr-3.5 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                />
              </div>
            </div>

            <!-- Email -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-300">Alamat Email</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                  <Mail class="w-4 h-4" />
                </div>
                <input
                  type="email"
                  required
                  v-model="form.email"
                  placeholder="nama@perusahaan.com"
                  class="w-full pl-9 pr-3.5 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                />
              </div>
            </div>

            <!-- No. HP / WhatsApp -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-300">Nomor HP / WhatsApp</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                  <Phone class="w-4 h-4" />
                </div>
                <input
                  type="tel"
                  v-model="form.phone"
                  placeholder="08xxxxxxxxxx"
                  class="w-full pl-9 pr-3.5 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Section 2: Keamanan & Ubah Password -->
        <div class="space-y-4 pt-2">
          <div class="flex items-center justify-between pb-1 border-b border-slate-800">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-300">
              <ShieldCheck class="w-4 h-4 text-amber-400" />
              <span>Ganti Kata Sandi (Opsional)</span>
            </div>
            <span class="text-[10px] text-slate-500">Kosongkan jika tidak ingin mengganti sandi</span>
          </div>

          <div class="space-y-3.5 rounded-2xl bg-slate-950/60 p-4 border border-slate-800">
            <!-- Password Lama (Wajib jika ganti sandi baru) -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-amber-300 flex items-center justify-between">
                <span>Kata Sandi Lama (Password Saat Ini)</span>
                <span v-if="form.newPassword" class="text-[10px] text-rose-400 font-mono">*Wajib Diisi</span>
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                  <KeyRound class="w-4 h-4" />
                </div>
                <input
                  :type="showPass.current ? 'text' : 'password'"
                  v-model="form.currentPassword"
                  :placeholder="form.newPassword ? 'Masukkan kata sandi lama Anda' : 'Hanya diisi jika ingin mengganti sandi'"
                  class="w-full pl-9 pr-9 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                />
                <button
                  type="button"
                  @click="showPass.current = !showPass.current"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300 cursor-pointer"
                >
                  <EyeOff v-if="showPass.current" class="w-3.5 h-3.5" />
                  <Eye v-else class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>

            <!-- Password Baru & Konfirmasi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
              <!-- Password Baru -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-300">Kata Sandi Baru</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <Lock class="w-4 h-4" />
                  </div>
                  <input
                    :type="showPass.new ? 'text' : 'password'"
                    v-model="form.newPassword"
                    placeholder="Min. 6 karakter"
                    class="w-full pl-9 pr-9 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  />
                  <button
                    type="button"
                    @click="showPass.new = !showPass.new"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300 cursor-pointer"
                  >
                    <EyeOff v-if="showPass.new" class="w-3.5 h-3.5" />
                    <Eye v-else class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>

              <!-- Konfirmasi Password Baru -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-300">Konfirmasi Sandi Baru</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <Lock class="w-4 h-4" />
                  </div>
                  <input
                    :type="showPass.confirm ? 'text' : 'password'"
                    v-model="form.confirmPassword"
                    placeholder="Ulangi sandi baru"
                    class="w-full pl-9 pr-9 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  />
                  <button
                    type="button"
                    @click="showPass.confirm = !showPass.confirm"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300 cursor-pointer"
                  >
                    <EyeOff v-if="showPass.confirm" class="w-3.5 h-3.5" />
                    <Eye v-else class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Actions Footer -->
        <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-3">
          <button
            type="button"
            @click="$emit('close')"
            class="px-5 py-2.5 rounded-xl border border-slate-700 text-xs font-bold text-slate-300 hover:bg-slate-800 hover:text-white transition-all cursor-pointer"
          >
            Batal
          </button>

          <button
            type="submit"
            :disabled="saving"
            class="px-6 py-2.5 bg-gradient-to-r from-purple-700 via-indigo-600 to-purple-600 hover:from-purple-600 hover:to-indigo-500 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-lg shadow-purple-900/30 active:scale-95 transition-all flex items-center gap-2 cursor-pointer"
          >
            <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
            <Save v-else class="w-4 h-4" />
            <span>{{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</Teleport>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { useAuth } from '../composables/useAuth';
import {
  User,
  Mail,
  Phone,
  Lock,
  KeyRound,
  ShieldCheck,
  CheckCircle2,
  AlertCircle,
  X,
  Eye,
  EyeOff,
  Save,
  Loader2
} from 'lucide-vue-next';

const emit = defineEmits(['close', 'updated']);
const { user, updateProfile } = useAuth();

const form = reactive({
  name: user.value?.name || '',
  email: user.value?.email || '',
  phone: user.value?.phone || '',
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
});

const showPass = reactive({
  current: false,
  new: false,
  confirm: false
});

const saving = ref(false);
const successMsg = ref(null);
const errorMsg = ref(null);

watch(user, (val) => {
  if (val) {
    form.name = val.name || '';
    form.email = val.email || '';
    form.phone = val.phone || '';
  }
}, { immediate: true });

const roleBadgeClass = computed(() => {
  switch (user.value?.role) {
    case 'SUPERUSER':
      return 'text-purple-300 bg-purple-500/20 border-purple-500/40';
    case 'ADMIN':
      return 'text-indigo-300 bg-indigo-500/20 border-indigo-500/40';
    case 'SUPERVISOR':
      return 'text-blue-300 bg-blue-500/20 border-blue-500/40';
    case 'FIELD_TEAM':
      return 'text-amber-300 bg-amber-500/20 border-amber-500/40';
    case 'VENDOR':
      return 'text-emerald-300 bg-emerald-500/20 border-emerald-500/40';
    default:
      return 'text-slate-300 bg-slate-800 border-slate-700';
  }
});

async function handleSaveProfile() {
  errorMsg.value = null;
  successMsg.value = null;

  if (!form.name.trim()) {
    errorMsg.value = 'Nama lengkap tidak boleh kosong.';
    return;
  }
  if (!form.email.trim()) {
    errorMsg.value = 'Alamat email tidak boleh kosong.';
    return;
  }

  // Password change validation
  if (form.newPassword) {
    if (!form.currentPassword) {
      errorMsg.value = 'Silakan masukkan kata sandi lama Anda untuk konfirmasi keamanan.';
      return;
    }
    if (form.newPassword.length < 6) {
      errorMsg.value = 'Kata sandi baru minimal 6 karakter.';
      return;
    }
    if (form.newPassword !== form.confirmPassword) {
      errorMsg.value = 'Konfirmasi kata sandi baru tidak cocok dengan kata sandi baru.';
      return;
    }
  }

  saving.value = true;
  try {
    const payload = {
      name: form.name.trim(),
      email: form.email.trim().toLowerCase(),
      phone: form.phone ? form.phone.trim() : ''
    };

    if (form.newPassword) {
      payload.currentPassword = form.currentPassword;
      payload.newPassword = form.newPassword;
    }

    await updateProfile(payload);
    successMsg.value = 'Profil Anda dan pengaturan keamanan berhasil diperbarui!';
    
    // Clear password fields
    form.currentPassword = '';
    form.newPassword = '';
    form.confirmPassword = '';

    emit('updated');
    setTimeout(() => {
      emit('close');
    }, 1500);
  } catch (err) {
    errorMsg.value = err.message || 'Gagal memperbarui profil. Periksa kembali isian Anda.';
  } finally {
    saving.value = false;
  }
}
</script>
