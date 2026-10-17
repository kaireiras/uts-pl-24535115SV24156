<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

const router = useRouter();

// Tab navigasi aktif: 'list' | 'create' | 'about'
const currentTab = ref('list');
const searchQuery = ref('');
const isLoading = ref(false);
const message = ref('');

// Data Projects, About, dan Foto me.jpg
const projects = ref([]);
const aboutForm = reactive({
  title: '',
  bio: '',
});
const photoFile = ref(null);
const currentPhotoUrl = ref('');
const isUploadingPhoto = ref(false);

// Form input project (untuk create & update)
const projectForm = reactive({
  id: null,
  title: '',
  description: '',
  link: '',
  is_featured: false,
});

// Penanganan error autentikasi terpusat
function handleAuthError(error) {
  if (error.response?.status === 401) {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    router.push('/login');
  }
}

// Load semua data dari Laravel API
async function loadData() {
  isLoading.value = true;
  try {
    const [resProjects, resPortfolio] = await Promise.all([
      api.get('/projects'),
      api.get('/portfolio-data'),
    ]);

    projects.value = resProjects.data.data || [];
    if (resPortfolio.data.about) {
      aboutForm.title = resPortfolio.data.about.title || '';
      aboutForm.bio = resPortfolio.data.about.bio || '';
    }
    if (resPortfolio.data.me_photo) {
      currentPhotoUrl.value = resPortfolio.data.me_photo;
    }
  } catch (error) {
    console.error('Gagal mengambil data:', error);
    handleAuthError(error);
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  loadData();
});

// Filter pencarian project
const filteredProjects = computed(() => {
  if (!searchQuery.value) return projects.value;
  return projects.value.filter(
    (p) =>
      p.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (p.description && p.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
  );
});

// Reset form project
function resetProjectForm() {
  projectForm.id = null;
  projectForm.title = '';
  projectForm.description = '';
  projectForm.link = '';
  projectForm.is_featured = false;
}

// Buka form tambah
function openCreate() {
  resetProjectForm();
  currentTab.value = 'create';
  message.value = '';
}

// Buka form edit
function editProject(project) {
  projectForm.id = project.id;
  projectForm.title = project.title;
  projectForm.description = project.description;
  projectForm.link = project.link || '';
  projectForm.is_featured = Boolean(project.is_featured);
  currentTab.value = 'create';
  message.value = '';
}

// Simpan project (Create / Update dengan Bearer Token)
async function saveProject() {
  if (!projectForm.title || !projectForm.description) {
    alert('Judul dan deskripsi wajib diisi!');
    return;
  }

  try {
    if (projectForm.id) {
      await api.put(`/projects/${projectForm.id}`, projectForm);
      message.value = 'Project berhasil diperbarui.';
    } else {
      await api.post('/projects', projectForm);
      message.value = 'Project baru berhasil disimpan.';
    }
    resetProjectForm();
    await loadData();
    currentTab.value = 'list';
  } catch (error) {
    console.error('Gagal simpan project:', error);
    handleAuthError(error);
    alert(error.response?.data?.message || 'Terjadi kesalahan saat menyimpan project.');
  }
}

// Hapus project (Destroy dengan Bearer Token)
async function deleteProject(id) {
  if (!confirm('Hapus project ini?')) return;
  try {
    await api.delete(`/projects/${id}`);
    projects.value = projects.value.filter((p) => p.id !== id);
  } catch (error) {
    console.error('Gagal hapus project:', error);
    handleAuthError(error);
  }
}

// Update teks About Me
async function saveAbout() {
  try {
    await api.post('/settings/about', {
      title: aboutForm.title,
      bio: aboutForm.bio,
    });
    alert('Data About berhasil diperbarui!');
  } catch (error) {
    console.error('Gagal update about:', error);
    handleAuthError(error);
    alert(error.response?.data?.message || 'Terjadi kesalahan saat menyimpan data About.');
  }
}

// Handler pemilihan file foto me.jpg
function onFileChange(e) {
  const file = e.target.files[0];
  if (file) {
    photoFile.value = file;
  }
}

// Upload foto me.jpg
async function uploadMePhoto() {
  if (!photoFile.value) {
    alert('Pilih file gambar terlebih dahulu!');
    return;
  }

  const formData = new FormData();
  formData.append('photo', photoFile.value);

  isUploadingPhoto.value = true;
  try {
    const res = await api.post('/settings/upload-photo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    currentPhotoUrl.value = res.data.photo_url;
    photoFile.value = null;
    alert('Foto me.jpg berhasil diperbarui!');
  } catch (error) {
    console.error('Gagal upload gambar:', error);
    handleAuthError(error);
    alert('Gagal mengunggah foto. Pastikan format berupa JPG, PNG, atau WEBP.');
  } finally {
    isUploadingPhoto.value = false;
  }
}

// Logout dengan memanggil endpoint API dan mencabut token
async function handleLogout() {
  try {
    await api.post('/logout');
  } catch (error) {
    console.error('Gagal logout di server:', error);
  } finally {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    router.push('/login');
  }
}

function goBackHome() {
  router.push('/');
}
</script>

<template>
  <div class="admin-layout">
    <!-- Sidebar Kiri -->
    <aside class="sidebar">
      <div class="brand">admin panel</div>

      <nav class="nav-menu">
        <button
          :class="['nav-link', { active: currentTab === 'create' }]"
          @click="openCreate"
        >
          buat
        </button>
        <button
          :class="['nav-link', { active: currentTab === 'list' }]"
          @click="currentTab = 'list'"
        >
          list
        </button>
        <button
          :class="['nav-link', { active: currentTab === 'about' }]"
          @click="currentTab = 'about'"
        >
          about & foto
        </button>
        <button class="nav-link" @click="goBackHome">
          lihat publik
        </button>
      </nav>

      <div class="logout-wrapper">
        <button class="nav-link logout-btn" @click="handleLogout">keluar</button>
      </div>
    </aside>

    <!-- Konten Utama Kanan -->
    <main class="main-content">
      <!-- 1. TAB LIST PROJECT -->
      <section v-if="currentTab === 'list'" class="content-section">
        <h1 class="page-title">list project</h1>

        <div class="toolbar">
          <span class="count-label">{{ projects.length }} project</span>
          <button class="btn-primary" @click="openCreate">+ buat baru</button>
        </div>

        <div class="search-box">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="cari project..."
            class="underline-input"
          />
        </div>

        <table class="minimal-table">
          <thead>
            <tr>
              <th style="width: 45%;">judul & deskripsi</th>
              <th style="width: 25%;">link</th>
              <th style="width: 15%;">status</th>
              <th style="width: 15%; text-align: right;">aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="filteredProjects.length === 0">
              <td colspan="4" class="empty-state">
                {{ isLoading ? 'Memuat data...' : 'Tidak ada project ditemukan.' }}
              </td>
            </tr>
            <tr v-for="item in filteredProjects" :key="item.id">
              <td>
                <div class="item-title">{{ item.title }}</div>
                <div class="item-desc">{{ item.description }}</div>
              </td>
              <td>
                <a
                  v-if="item.link"
                  :href="item.link"
                  target="_blank"
                  class="table-link"
                >
                  {{ item.link }}
                </a>
                <span v-else class="text-muted">-</span>
              </td>
              <td>
                <span v-if="item.is_featured" class="tag-featured">featured</span>
                <span v-else class="text-muted">standar</span>
              </td>
              <td style="text-align: right;">
                <button class="action-btn" @click="editProject(item)">edit</button>
                <button class="action-btn text-danger" @click="deleteProject(item.id)">
                  hapus
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- 2. TAB FORM PROJECT (BUAT / EDIT) -->
      <section v-else-if="currentTab === 'create'" class="content-section">
        <h1 class="page-title">{{ projectForm.id ? 'edit project' : 'buat project baru' }}</h1>

        <form class="minimal-form" @submit.prevent="saveProject">
          <div class="form-group">
            <label>judul project</label>
            <input
              v-model="projectForm.title"
              type="text"
              placeholder="masukkan judul..."
              class="underline-input form-input"
              required
            />
          </div>

          <div class="form-group">
            <label>deskripsi</label>
            <textarea
              v-model="projectForm.description"
              rows="4"
              placeholder="penjelasan ringkas project..."
              class="underline-input form-textarea"
              required
            ></textarea>
          </div>

          <div class="form-group">
            <label>link / url (opsional)</label>
            <input
              v-model="projectForm.link"
              type="url"
              placeholder="https://..."
              class="underline-input form-input"
            />
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input v-model="projectForm.is_featured" type="checkbox" />
              <span>Jadikan Featured Project (tampil di jendela utama HomeView)</span>
            </label>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary">
              {{ projectForm.id ? 'simpan perubahan' : 'buat project' }}
            </button>
            <button type="button" class="btn-secondary" @click="currentTab = 'list'">
              batal
            </button>
          </div>
        </form>
      </section>

      <!-- 3. TAB ABOUT ME & UPLOAD FOTO -->
      <section v-else-if="currentTab === 'about'" class="content-section">
        <h1 class="page-title">pengaturan about & foto</h1>

        <!-- Form Teks About -->
        <form class="minimal-form" @submit.prevent="saveAbout">
          <div class="form-group">
            <label>judul jendela about</label>
            <input
              v-model="aboutForm.title"
              type="text"
              placeholder="contoh: About Me"
              class="underline-input form-input"
              required
            />
          </div>

          <div class="form-group">
            <label>bio / deskripsi profil</label>
            <textarea
              v-model="aboutForm.bio"
              rows="5"
              placeholder="tulis bio atau cerita singkat..."
              class="underline-input form-textarea"
              required
            ></textarea>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary">simpan data about</button>
          </div>
        </form>

        <!-- Form Upload me.jpg -->
        <div class="photo-section">
          <h2 class="sub-title">foto profil (me.jpg)</h2>
          <div class="form-group">
            <label>pilih file gambar baru</label>
            <input
              type="file"
              accept="image/jpeg,image/png,image/jpg,image/webp"
              class="file-input"
              @change="onFileChange"
            />
          </div>

          <button
            type="button"
            class="btn-primary"
            :disabled="isUploadingPhoto"
            @click="uploadMePhoto"
          >
            {{ isUploadingPhoto ? 'mengunggah...' : 'unggah me.jpg' }}
          </button>

          <!-- Tampilan Preview Gambar Aktif -->
          <div v-if="currentPhotoUrl" class="photo-preview-wrap">
            <span class="preview-label">foto aktif saat ini:</span>
            <div class="preview-box">
              <img :src="currentPhotoUrl" alt="Preview me.jpg" class="preview-img" />
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  width: 100vw;
  background-color: #ffffff;
  color: #1a1a1a;
  font-family: Georgia, 'Times New Roman', Times, serif;
}

/* Sidebar Kiri */
.sidebar {
  width: 220px;
  border-right: 1px solid #e5e5e5;
  padding: 40px 28px;
  display: flex;
  flex-direction: column;
}

.brand {
  font-size: 15px;
  color: #8c8c8c;
  margin-bottom: 40px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.nav-menu {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.nav-link {
  background: none;
  border: none;
  text-align: left;
  font-size: 14px;
  color: #7a7a7a;
  cursor: pointer;
  padding: 2px 0;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  transition: color 0.15s ease;
}

.nav-link:hover {
  color: #111111;
}

.nav-link.active {
  color: #111111;
  font-weight: 600;
}

.logout-wrapper {
  margin-top: auto;
  padding-top: 40px;
}

.logout-btn {
  color: #d32f2f;
}

.logout-btn:hover {
  color: #b71c1c;
}

/* Main Area */
.main-content {
  flex: 1;
  padding: 48px 64px;
  max-width: 900px;
}

.page-title {
  font-size: 24px;
  font-weight: normal;
  margin: 0 0 32px 0;
  color: #1a1a1a;
}

.sub-title {
  font-size: 18px;
  font-weight: normal;
  margin: 0 0 16px 0;
  color: #1a1a1a;
}

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.count-label {
  font-size: 13px;
  color: #8c8c8c;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Tombol Persegi Minimalis */
.btn-primary {
  background: #ffffff;
  color: #111111;
  border: 1px solid #111111;
  padding: 6px 16px;
  font-size: 13px;
  cursor: pointer;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  transition: all 0.15s ease;
}

.btn-primary:hover:not(:disabled) {
  background: #111111;
  color: #ffffff;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: transparent;
  color: #777777;
  border: none;
  padding: 6px 16px;
  font-size: 13px;
  cursor: pointer;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.btn-secondary:hover {
  color: #111111;
}

/* Input Underline */
.search-box {
  margin-bottom: 24px;
  width: 260px;
}

.underline-input {
  border: none;
  border-bottom: 1px solid #dcdcdc;
  padding: 6px 0;
  font-size: 13px;
  width: 100%;
  outline: none;
  background: transparent;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #333333;
}

.underline-input:focus {
  border-bottom-color: #111111;
}

/* Tabel Minimalis */
.minimal-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

.minimal-table thead tr {
  border-bottom: 1px solid #222222;
}

.minimal-table th {
  text-align: left;
  padding: 8px 0;
  font-size: 12px;
  font-weight: normal;
  color: #777777;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.minimal-table td {
  padding: 16px 0;
  border-bottom: 1px solid #f0f0f0;
  font-size: 13px;
  vertical-align: top;
}

.empty-state {
  text-align: center;
  color: #999999;
  padding: 48px 0 !important;
  font-size: 13px;
}

.item-title {
  font-weight: bold;
  margin-bottom: 4px;
}

.item-desc {
  color: #666666;
  font-size: 12px;
  line-height: 1.4;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.table-link {
  color: #555555;
  text-decoration: underline;
  word-break: break-all;
  font-size: 12px;
}

.tag-featured {
  display: inline-block;
  background: #111111;
  color: #ffffff;
  font-size: 10px;
  padding: 2px 6px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.text-muted {
  color: #aaaaaa;
  font-size: 12px;
}

.action-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 12px;
  color: #444444;
  margin-left: 8px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.action-btn:hover {
  text-decoration: underline;
}

.text-danger {
  color: #d32f2f;
}

/* Form Styles */
.minimal-form {
  max-width: 500px;
  margin-top: 24px;
}

.form-group {
  margin-bottom: 24px;
}

.form-group label {
  display: block;
  font-size: 12px;
  color: #777777;
  margin-bottom: 6px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.form-input {
  font-size: 14px;
}

.form-textarea {
  resize: vertical;
  line-height: 1.5;
}

.checkbox-group {
  margin-top: 10px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  cursor: pointer;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.form-actions {
  display: flex;
  gap: 12px;
  align-items: center;
  margin-top: 28px;
}

/* Bagian Upload Foto Profil */
.photo-section {
  max-width: 500px;
  margin-top: 48px;
  padding-top: 36px;
  border-top: 1px dashed #e0e0e0;
}

.file-input {
  font-size: 12px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #555555;
}

.photo-preview-wrap {
  margin-top: 20px;
}

.preview-label {
  display: block;
  font-size: 12px;
  color: #888888;
  margin-bottom: 8px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.preview-box {
  width: 140px;
  border: 1px solid #111111;
  padding: 4px;
  background: #fafafa;
}

.preview-img {
  width: 100%;
  height: auto;
  display: block;
}
</style>