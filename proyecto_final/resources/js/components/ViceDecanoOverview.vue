<template>
  <div class="vice-overview animate-fade-in">
    <!-- ── TOGGLE DARK MODE ─────────────────────────── -->
    <button class="theme-toggle" @click="$emit('toggleDark')" :title="isDark ? 'Modo claro' : 'Modo oscuro'">
      <svg v-if="!isDark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="5"/>
        <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
        <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
      </svg>
      <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
      </svg>
    </button>

    <!-- ── HERO BANNER con SVG animado ─────────────── -->
    <div class="hero-banner">
      <div class="hero-bg">
        <svg class="hero-svg" viewBox="0 0 440 200" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
          <g class="geo-layer">
            <path d="M188 155L189 155C196 148 206 139 220 128C233 117 243 108 250 101C256 94 261 87 263 80C266 72 265 64 260 56C255 47 247 39 237 34C228 28 218 25 207 25C197 25 190 28 187 33C184 39 185 46 190 56L126 56C116 40 112 25 114 13C115 1 122 -8 133 -15C144 -22 159 -26 177 -26C197 -26 216 -22 235 -15C254 -8 271 2 287 13C303 25 315 40 325 56C333 71 337 84 336 95C335 106 331 116 325 125C318 133 308 143 295 156L377 156L406 206L217 206L188 155Z" :fill="isDark ? '#3D0A00' : '#F8B803'" opacity="0.7"/>
            <path d="M9 126L-14 126L-43 76L43 76L176 206L113 206L9 126Z" :fill="isDark ? '#3D0A00' : '#F8B803'" opacity="0.7"/>
          </g>
          <g class="geo-layer-overlay" style="mix-blend-mode: hard-light">
            <path d="M217 105L218 105C225 98 235 89 249 78C262 67 272 58 279 51C285 44 290 37 292 29C294 21 293 14 288 6C283 -3 276 -11 266 -17C257 -22 247 -25 236 -25C226 -25 219 -22 216 -17C213 -11 214 -4 219 6L155 6C145 -10 141 -25 143 -38C144 -50 151 -60 162 -67C173 -74 188 -78 206 -78C226 -78 245 -74 264 -67C283 -60 300 -50 316 -38C332 -25 344 -10 354 6C362 21 366 34 365 45C364 57 360 66 354 75C347 84 337 94 324 105L406 105L435 155L246 155L217 105Z" :fill="isDark ? '#67000F' : '#F0ACB8'" opacity="0.6"/>
          </g>
        </svg>

        <div class="hero-logo">
          <img src="/images/logo_ugb.png" alt="Logo UGB" class="ugb-logo-img">
          <div class="hero-title-group">
            <h1 class="hero-title">Panel del Vice Decano</h1>
            <p class="hero-subtitle">Génesis Profesional · Sistema de Pasantías</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ── STATS CARDS ───────────────────────────────── -->
    <div class="stats-row">
      <div class="stat-card stat-pending">
        <div class="stat-icon">📋</div>
        <div>
          <div class="stat-num">{{ informesPendientes }}</div>
          <div class="stat-label">Informes pendientes</div>
        </div>
      </div>
      <div class="stat-card stat-approved">
        <div class="stat-icon">✅</div>
        <div>
          <div class="stat-num">{{ informesAprobados }}</div>
          <div class="stat-label">Aprobados</div>
        </div>
      </div>
      <div class="stat-card stat-rejected">
        <div class="stat-icon">🔄</div>
        <div>
          <div class="stat-num">{{ informesCorreccion }}</div>
          <div class="stat-label">Con correcciones</div>
        </div>
      </div>
    </div>

    <!-- ── LISTADO DE INFORMES RECIENTES ─────────────── -->
    <div class="recent-card">
      <div class="recent-header">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
          <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
        <span>Actividad Reciente</span>
      </div>
      <div class="activity-list">
        <div class="activity-item" v-for="item in actividad" :key="item.id">
          <span class="activity-badge" :class="item.estado">{{ item.estado === 'aprobado' ? '✅' : '🔄' }}</span>
          <div class="activity-info">
            <strong>{{ item.nombre }}</strong>
            <small>{{ item.proyecto }} · {{ item.fecha }}</small>
          </div>
          <span class="activity-tag" :class="item.estado">{{ item.estado }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  isDark: { type: Boolean, default: false },
  informesPendientes: { type: Number, default: 0 },
  informesAprobados: { type: Number, default: 0 },
  informesCorreccion: { type: Number, default: 0 },
  actividad: { type: Array, default: () => [] }
});

defineEmits(['toggleDark']);
</script>

<style scoped>
.vice-overview {
  width: 100%;
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ─── Toggle ─────────────────────────── */
.theme-toggle {
  position: absolute; top: 16px; right: 16px; z-index: 10;
  width: 36px; height: 36px;
  border: 1px solid var(--border);
  border-radius: 50%;
  background: var(--surface);
  color: var(--text);
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
  box-shadow: var(--shadow);
}
.theme-toggle:hover { border-color: var(--accent); color: var(--accent); }
.theme-toggle svg { width: 16px; height: 16px; }

/* ─── Hero Banner ────────────────────── */
.hero-banner {
  width: 100%;
  margin-bottom: 28px;
  border-radius: var(--radius);
  overflow: hidden;
  box-shadow: var(--shadow);
}
.hero-bg {
  position: relative;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 32px 32px 24px;
  overflow: hidden;
  min-height: 140px;
}
.hero-svg {
  position: absolute; top: 0; right: 0;
  width: 55%; height: 100%;
  opacity: 0.85;
  pointer-events: none;
}
.hero-logo {
  position: relative; z-index: 2;
  display: flex; align-items: center; gap: 20px;
}
.ugb-logo-img { width: 90px; height: 45px; object-fit: contain; flex-shrink: 0; }
.hero-title {
  font-size: 1.5rem; font-weight: 700;
  color: var(--text); margin: 0 0 4px;
}
.hero-subtitle {
  font-size: 0.85rem; color: var(--sub); margin: 0;
}

/* ─── Stats ──────────────────────────── */
.stats-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}
.stat-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 20px;
  display: flex; align-items: center; gap: 16px;
  box-shadow: var(--shadow);
  transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-icon { font-size: 2rem; }
.stat-num { font-size: 1.8rem; font-weight: 700; line-height: 1; }
.stat-label { font-size: 0.78rem; color: var(--sub); margin-top: 2px; }
.stat-pending .stat-num { color: var(--warning); }
.stat-approved .stat-num { color: var(--success); }
.stat-rejected .stat-num { color: var(--accent); }

/* ─── Recent Card ────────────────────── */
.recent-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  margin-bottom: 24px;
  overflow: hidden;
}
.recent-header {
  display: flex; align-items: center; gap: 10px;
  padding: 16px 24px;
  border-bottom: 1px solid var(--border);
  font-weight: 600; font-size: 0.95rem;
  background: var(--bg);
  color: var(--accent);
}

/* ─── Activity List ──────────────────── */
.activity-list { padding: 8px 0; }
.activity-item {
  display: flex; align-items: center; gap: 14px;
  padding: 12px 24px;
  border-bottom: 1px solid var(--border);
  transition: background 0.15s;
}
.activity-item:last-child { border-bottom: none; }
.activity-item:hover { background: color-mix(in srgb, var(--accent) 5%, transparent); }
.activity-badge { font-size: 1.2rem; }
.activity-info { flex: 1; }
.activity-info strong { display: block; font-size: 0.9rem; }
.activity-info small { color: var(--sub); font-size: 0.78rem; }
.activity-tag {
  font-size: 0.72rem; font-weight: 600;
  padding: 3px 10px; border-radius: 20px;
  text-transform: uppercase; letter-spacing: 0.05em;
}
.activity-tag.aprobado { background: #DCFCE7; color: #16A34A; }
.activity-tag.correccion { background: #FEF3C7; color: #D97706; }

@media (max-width: 640px) {
  .stats-row { grid-template-columns: 1fr; }
  .hero-svg { width: 70%; opacity: 0.4; }
}
</style>
