<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const API_BASE_URL = 'http://127.0.0.1:8000/api';

const portfolioData = ref({
  about: {
    title: 'Loading...',
    bio: 'Memuat data profil...',
  },
  me_photo: null,
  featured_project: null,
  all_projects: [],
});
const isLoading = ref(true);

const highestZIndex = ref(10);

// State penampung waktu real-time
const currentTime = ref('');
let timer = null;

// Fungsi memperbarui jam
function updateClock() {
  const now = new Date();
  const hours = String(now.getHours()).padStart(2, '0');
  const minutes = String(now.getMinutes()).padStart(2, '0');
  const seconds = String(now.getSeconds()).padStart(2, '0');
  currentTime.value = `${hours}:${minutes}:${seconds} [WIB]`;
}

// State penampung posisi, ukuran, dan status jendela
const windows = reactive({
  about: {
    isOpen: true,
    top: 120,
    left: 200,
    zIndex: 10,
  },
  project: {
    isOpen: true,
    top: 200,
    left: 320,
    zIndex: 11,
  },
  works: {
    isOpen: false,
    top: 150,
    left: 280,
    zIndex: 12,
  },
  photo: {
    isOpen: false,
    top: 100,
    left: 400,
    zIndex: 13,
  },
});

// Penempatan posisi jendela di tengah layar secara proporsional
function centerWindows() {
  const screenW = window.innerWidth;
  const screenH = window.innerHeight;

  const midX = screenW / 2;
  const midY = screenH / 2;

  // Window About (Lebar 350px)
  windows.about.left = Math.max(20, midX - 260);
  windows.about.top = Math.max(50, midY - 180);

  // Window Featured Project (Lebar 320px)
  windows.project.left = Math.max(40, midX - 80);
  windows.project.top = Math.max(70, midY - 90);

  // Window Works (Lebar 420px)
  windows.works.left = Math.max(30, midX - 210);
  windows.works.top = Math.max(60, midY - 140);

  // Window Photo me.jpg (Lebar 280px)
  windows.photo.left = Math.max(50, midX + 160);
  windows.photo.top = Math.max(60, midY - 160);
}

// Ambil bundle data dari Laravel API
async function fetchPortfolioData() {
  try {
    const response = await axios.get(`${API_BASE_URL}/portfolio-data`);
    portfolioData.value = response.data;
  } catch (error) {
    console.error('Gagal mengambil data dari Laravel:', error);
  } finally {
    isLoading.value = false;
  }
}

// Buka jendela dan bawa ke layer terdepan
function openWindow(key) {
  windows[key].isOpen = true;
  bringToFront(key);
}

// Tutup jendela
function closeWindow(key) {
  windows[key].isOpen = false;
}

// Naikkan z-index jendela yang aktif
function bringToFront(key) {
  highestZIndex.value++;
  windows[key].zIndex = highestZIndex.value;
}

// Drag handler jendela
function startDrag(key, event) {
  bringToFront(key);
  const startX = event.clientX;
  const startY = event.clientY;
  const initialLeft = windows[key].left;
  const initialTop = windows[key].top;

  function onMouseMove(e) {
    windows[key].left = initialLeft + (e.clientX - startX);
    windows[key].top = initialTop + (e.clientY - startY);
  }

  function onMouseUp() {
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseup', onMouseUp);
  }

  window.addEventListener('mousemove', onMouseMove);
  window.addEventListener('mouseup', onMouseUp);
}

onMounted(() => {
  centerWindows();
  fetchPortfolioData();

  // Jalankan jam real-time
  updateClock();
  timer = setInterval(updateClock, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>

<template>
  <div class="os-container">
    <!-- Header / Top Bar -->
    <header>
      <div><strong>kaireiras</strong></div>
      <div class="nav-links">
        <span @click="openWindow('about')">Home</span>
        <span @click="openWindow('works')">Works</span>
        <span @click="openWindow('about')">About</span>
      </div>
      <div>{{ currentTime }}</div>
    </header>

    <main class="desktop">
      <!-- Shortcut Icons Desktop -->
      <div class="desktop-icons">
        <button class="icon-btn" @click="openWindow('about')">📁 About Me</button>
        <button class="icon-btn" @click="openWindow('project')">📄 Featured Project</button>
        <button class="icon-btn" @click="openWindow('works')">💼 Works Gallery</button>
        <button class="icon-btn" @click="openWindow('photo')">🖼️ me.jpg</button>
      </div>

      <!-- Jendela 1: About -->
      <div
        v-show="windows.about.isOpen"
        class="window"
        :style="{
          top: windows.about.top + 'px',
          left: windows.about.left + 'px',
          zIndex: windows.about.zIndex,
          width: '350px'
        }"
        @mousedown="bringToFront('about')"
      >
        <div class="window-header" @mousedown="startDrag('about', $event)">
          <span>{{ portfolioData.about?.title || 'About' }}</span>
          <button class="close-btn" @click.stop="closeWindow('about')">✕</button>
        </div>
        <div class="window-content">
          <p>{{ portfolioData.about?.bio || 'Tidak ada deskripsi.' }}</p>
        </div>
      </div>

      <!-- Jendela 2: Featured Project -->
      <div
        v-show="windows.project.isOpen"
        class="window"
        :style="{
          top: windows.project.top + 'px',
          left: windows.project.left + 'px',
          zIndex: windows.project.zIndex,
          width: '320px'
        }"
        @mousedown="bringToFront('project')"
      >
        <div class="window-header" @mousedown="startDrag('project', $event)">
          <span>{{ portfolioData.featured_project?.title || 'Featured Article' }}</span>
          <button class="close-btn" @click.stop="closeWindow('project')">✕</button>
        </div>
        <div class="window-content">
          <div v-if="isLoading">
            <p>Memuat data proyek...</p>
          </div>
          <div v-else-if="portfolioData.featured_project">
            <p>{{ portfolioData.featured_project.description }}</p>
            <a
              v-if="portfolioData.featured_project.link"
              :href="portfolioData.featured_project.link"
              target="_blank"
              class="project-link"
            >
              Lihat Proyek &rarr;
            </a>
          </div>
          <div v-else>
            <p>Belum ada proyek yang ditampilkan.</p>
          </div>
        </div>
      </div>

      <!-- Jendela 3: Works -->
      <div
        v-show="windows.works.isOpen"
        class="window"
        :style="{
          top: windows.works.top + 'px',
          left: windows.works.left + 'px',
          zIndex: windows.works.zIndex,
          width: '420px'
        }"
        @mousedown="bringToFront('works')"
      >
        <div class="window-header" @mousedown="startDrag('works', $event)">
          <span>Works ({{ portfolioData.all_projects?.length || 0 }})</span>
          <button class="close-btn" @click.stop="closeWindow('works')">✕</button>
        </div>
        <div class="window-content works-scroll">
          <div v-if="isLoading">
            <p>Memuat daftar project...</p>
          </div>
          <div v-else-if="portfolioData.all_projects && portfolioData.all_projects.length > 0">
            <div
              v-for="item in portfolioData.all_projects"
              :key="item.id"
              class="works-item"
            >
              <div class="works-item-header">
                <span class="works-item-title">{{ item.title }}</span>
                <span v-if="item.is_featured" class="badge-featured">featured</span>
              </div>
              <p class="works-item-desc">{{ item.description }}</p>
              <a
                v-if="item.link"
                :href="item.link"
                target="_blank"
                class="project-link"
              >
                Kunjungi Link &rarr;
              </a>
            </div>
          </div>
          <div v-else>
            <p>Belum ada project.</p>
          </div>
        </div>
      </div>

      <!-- Jendela 4: me.jpg (Foto Profil) -->
      <div
        v-show="windows.photo.isOpen"
        class="window"
        :style="{
          top: windows.photo.top + 'px',
          left: windows.photo.left + 'px',
          zIndex: windows.photo.zIndex,
          width: '280px'
        }"
        @mousedown="bringToFront('photo')"
      >
        <div class="window-header" @mousedown="startDrag('photo', $event)">
          <span>me.jpg</span>
          <button class="close-btn" @click.stop="closeWindow('photo')">✕</button>
        </div>
        <div class="window-content photo-content">
          <div v-if="portfolioData.me_photo" class="photo-wrapper">
            <img :src="portfolioData.me_photo" alt="me.jpg" class="profile-img" />
          </div>
          <div v-else class="photo-placeholder">
            <p>Belum ada foto me.jpg.</p>
            <small>Unggah foto lewat admin panel.</small>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
* {
  box-sizing: border-box;
  font-family: 'Courier New', Courier, monospace;
}

.os-container {
  background-color: #ffffff;
  color: #eaeaea;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  user-select: none;
}

header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 16px;
  border-bottom: 1px solid #333;
  background: #141414;
  color: #eaeaea;
  font-size: 13px;
  height: 40px;
}

.nav-links span {
  margin: 0 10px;
  cursor: pointer;
}

.nav-links span:hover {
  text-decoration: underline;
}

.desktop {
  position: relative;
  width: 100vw;
  height: calc(100vh - 40px);
  background-color: #ffffff;
}

.desktop-icons {
  position: absolute;
  top: 20px;
  left: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.icon-btn {
  background: none;
  border: none;
  color: #111111;
  font-size: 13px;
  font-weight: bold;
  cursor: pointer;
  text-align: left;
}

.icon-btn:hover {
  text-decoration: underline;
}

.window {
  position: absolute;
  background: #121212;
  border: 1px solid #444;
  border-radius: 4px;
  min-width: 260px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
}

.window-header {
  background: #1c1c1c;
  padding: 6px 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: grab;
  border-bottom: 1px solid #333;
  font-size: 12px;
  color: #ffffff;
}

.window-header:active {
  cursor: grabbing;
}

.close-btn {
  background: transparent;
  border: 1px solid #555;
  color: #aaa;
  border-radius: 50%;
  width: 16px;
  height: 16px;
  line-height: 14px;
  font-size: 10px;
  cursor: pointer;
}

.close-btn:hover {
  background: #e81123;
  color: #fff;
  border-color: #e81123;
}

.window-content {
  padding: 14px;
  font-size: 13px;
  line-height: 1.5;
  color: #ccc;
}

.photo-content {
  padding: 8px;
}

.profile-img {
  width: 100%;
  height: auto;
  display: block;
  border: 1px solid #333;
  filter: grayscale(40%);
}

.photo-placeholder {
  padding: 20px 10px;
  text-align: center;
  color: #777;
}

.project-link {
  display: inline-block;
  margin-top: 8px;
  color: #64b5f6;
  text-decoration: none;
  font-size: 12px;
}

.project-link:hover {
  text-decoration: underline;
}

.works-scroll {
  max-height: 280px;
  overflow-y: auto;
}

.works-scroll::-webkit-scrollbar {
  width: 5px;
}

.works-scroll::-webkit-scrollbar-thumb {
  background: #333;
  border-radius: 2px;
}

.works-item {
  border-bottom: 1px dashed #333;
  padding: 10px 0;
}

.works-item:first-child {
  padding-top: 0;
}

.works-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.works-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.works-item-title {
  font-weight: bold;
  color: #ffffff;
}

.works-item-desc {
  font-size: 12px;
  color: #aaaaaa;
  margin: 0;
}

.badge-featured {
  background: #ffffff;
  color: #121212;
  font-size: 9px;
  padding: 1px 4px;
  font-weight: bold;
  border-radius: 2px;
}
</style>