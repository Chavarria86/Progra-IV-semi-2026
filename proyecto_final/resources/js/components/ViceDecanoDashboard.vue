<template>
  <div class="vice-dashboard" :class="{ 'dark-mode': isDark }">

    <!-- ── TOGGLE DARK MODE ─────────────────────────── -->
    <button class="theme-toggle" @click="isDark = !isDark" :title="isDark ? 'Modo claro' : 'Modo oscuro'">
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
      <!-- Fondo geométrico inspirado en welcome.blade -->
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

        <!-- Logo UGB / Genesis Profesional -->
        <div class="hero-logo">
          <svg class="ugb-logo-svg" viewBox="0 0 200 50" fill="none" xmlns="http://www.w3.org/2000/svg">
            <text x="0" y="38" font-family="'Segoe UI', sans-serif" font-size="36" font-weight="700" :fill="isDark ? '#FF750F' : '#67000F'">UGB</text>
          </svg>
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

    <!-- ── PANEL DE EVALUACIÓN ───────────────────────── -->
    <div class="eval-card">
      <div class="eval-header">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
        </svg>
        <span>Evaluación de Informes Finales</span>
      </div>

      <div class="eval-body">
        <form @submit.prevent="evaluarInforme">

          <!-- Selector de informe -->
          <div class="field-group">
            <label>Seleccionar Informe Pendiente</label>
            <div class="select-wrapper">
              <select v-model="evaluacion.informe_id" required>
                <option value="" disabled>Seleccione un informe final para revisar</option>
                <option value="10">Ana Martínez — Desarrollo Web (Fase 4 completada)</option>
                <option value="11">Roberto Suárez — UI/UX (Fase 4 completada)</option>
              </select>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-arrow">
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </div>
          </div>

          <!-- Veredicto -->
          <div class="field-group">
            <label>Veredicto</label>
            <div class="radio-group">
              <label class="radio-option" :class="{ active: evaluacion.veredicto === 'aprobado' }">
                <input type="radio" v-model="evaluacion.veredicto" value="aprobado"> 
                <span class="radio-dot"></span>
                ✅ Aprobar — Generar Carta de Finalización
              </label>
              <label class="radio-option" :class="{ active: evaluacion.veredicto === 'rechazado' }">
                <input type="radio" v-model="evaluacion.veredicto" value="rechazado">
                <span class="radio-dot"></span>
                🔄 Solicitar correcciones
              </label>
            </div>
          </div>

          <!-- Observaciones (solo si rechazado) -->
          <div class="field-group" v-if="evaluacion.veredicto === 'rechazado'">
            <label>Observaciones requeridas</label>
            <textarea v-model="evaluacion.observaciones" rows="4" 
              placeholder="Especifique qué debe corregir el estudiante en el informe..." required></textarea>
          </div>

          <!-- Botón submit -->
          <button type="submit" class="btn-submit" :disabled="cargando">
            <span v-if="cargando" class="spinner"></span>
            <span v-else>Guardar Evaluación</span>
          </button>

        </form>
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
import { ref } from 'vue';

const isDark = ref(window.matchMedia('(prefers-color-scheme: dark)').matches);
const cargando   = ref(false);
const informesPendientes = ref(2);
const informesAprobados  = ref(14);
const informesCorreccion = ref(3);

const evaluacion = ref({ informe_id: '', veredicto: 'aprobado', observaciones: '' });

const actividad = ref([
  { id: 1, nombre: 'Carlos Molina', proyecto: 'E-Commerce', estado: 'aprobado', fecha: 'Hoy 09:14' },
  { id: 2, nombre: 'Sofía López',   proyecto: 'App Móvil',  estado: 'correccion', fecha: 'Ayer 16:30' },
  { id: 3, nombre: 'Luis Hernández',proyecto: 'Redes',      estado: 'aprobado', fecha: '10 may' },
]);

const evaluarInforme = async () => {
  cargando.value = true;
  setTimeout(() => {
    cargando.value = false;
    if (evaluacion.value.veredicto === 'aprobado') {
      informesAprobados.value++;
      informesPendientes.value = Math.max(0, informesPendientes.value - 1);
      alertify.success('✅ El informe final ha sido aprobado. Carta de finalización generada.');
    } else {
      informesCorreccion.value++;
      informesPendientes.value = Math.max(0, informesPendientes.value - 1);
      alertify.warning('🔄 Se han solicitado correcciones al pasante.');
    }
    evaluacion.value = { informe_id: '', veredicto: 'aprobado', observaciones: '' };
  }, 1000);
};
</script>

<style scoped>
/* ─── Variables ──────────────────────── */
.vice-dashboard {
  --bg:       #F8F9FA;
  --surface:  #FFFFFF;
  --border:   #E8E8E4;
  --text:     #1B1B18;
  --sub:      #706F6C;
  --accent:   #67000F;
  --accent2:  #F8B803;
  --success:  #16A34A;
  --warning:  #D97706;
  --radius:   12px;
  --shadow:   0 4px 24px rgba(0,0,0,0.07);

  background: var(--bg);
  color: var(--text);
  min-height: 100%;
  padding: 0;
  transition: background 0.3s, color 0.3s;
  position: relative;
}

/* Dark mode */
.vice-dashboard.dark-mode {
  --bg:      #0A0A0A;
  --surface: #161615;
  --border:  #2E2E2A;
  --text:    #EDEDEC;
  --sub:     #A1A09A;
  --accent:  #FF750F;
  --accent2: #FFB347;
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
.ugb-logo-svg { width: 90px; height: 45px; flex-shrink: 0; }
.hero-title-group {}
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

/* ─── Eval Card ──────────────────────── */
.eval-card, .recent-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  margin-bottom: 24px;
  overflow: hidden;
}
.eval-header, .recent-header {
  display: flex; align-items: center; gap: 10px;
  padding: 16px 24px;
  border-bottom: 1px solid var(--border);
  font-weight: 600; font-size: 0.95rem;
  background: var(--bg);
  color: var(--accent);
}
.eval-body { padding: 24px; }

/* ─── Fields ─────────────────────────── */
.field-group { margin-bottom: 20px; }
.field-group label {
  display: block; font-size: 0.82rem;
  font-weight: 600; color: var(--sub);
  margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;
}
.select-wrapper { position: relative; }
.select-wrapper select, textarea {
  width: 100%; padding: 10px 14px;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--text);
  font-size: 0.9rem;
  transition: border-color 0.2s;
  appearance: none;
}
.select-wrapper select:focus, textarea:focus {
  outline: none; border-color: var(--accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 15%, transparent);
}
.select-arrow {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  width: 16px; pointer-events: none; color: var(--sub);
}
textarea { resize: vertical; min-height: 100px; }

/* ─── Radio Options ──────────────────── */
.radio-group { display: flex; flex-direction: column; gap: 10px; }
.radio-option {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 16px;
  border: 1.5px solid var(--border);
  border-radius: 8px; cursor: pointer;
  font-size: 0.9rem; transition: all 0.2s;
}
.radio-option input { display: none; }
.radio-dot {
  width: 16px; height: 16px; border-radius: 50%;
  border: 2px solid var(--border); flex-shrink: 0;
  transition: all 0.2s;
}
.radio-option.active {
  border-color: var(--accent);
  background: color-mix(in srgb, var(--accent) 8%, transparent);
}
.radio-option.active .radio-dot {
  border-color: var(--accent);
  background: var(--accent);
}

/* ─── Submit Button ──────────────────── */
.btn-submit {
  width: 100%; padding: 12px;
  background: var(--accent);
  color: #fff; border: none;
  border-radius: 8px; font-size: 0.95rem;
  font-weight: 600; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: opacity 0.2s, transform 0.1s;
  margin-top: 8px;
}
.btn-submit:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

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
.dark-mode .activity-tag.aprobado { background: #14532D; color: #86EFAC; }
.dark-mode .activity-tag.correccion { background: #451A03; color: #FCD34D; }

/* ─── Responsive ─────────────────────── */
@media (max-width: 640px) {
  .stats-row { grid-template-columns: 1fr; }
  .hero-svg { width: 70%; opacity: 0.4; }
}
</style>
