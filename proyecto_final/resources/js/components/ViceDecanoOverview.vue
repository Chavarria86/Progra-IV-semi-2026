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

    <!-- Welcome Card -->
    <div class="welcome-card">
      <div class="welcome-left">
        <h3>¡Bienvenido, Vice Decano!</h3>
        <p>Administra las vacantes del sistema, asigna supervisores y evalúa los informes finales de pasantías.</p>
      </div>
      <div class="welcome-right">
        <div class="welcome-crest">
          <i class="bi bi-shield-shaded"></i>
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

/* Welcome Card */
.welcome-card {
  background: linear-gradient(135deg, #67000f 0%, #3a0005 100%);
  border-radius: 14px;
  padding: 30px;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
  box-shadow: 0 10px 25px -5px rgba(103, 0, 15, 0.25);
  transition: background 0.3s;
}
.welcome-left h3 {
  font-family: 'Lora', serif;
  font-size: 24px;
  font-weight: 600;
  margin: 0 0 8px;
}
.welcome-left p {
  font-size: 14px;
  opacity: 0.85;
  max-width: 680px;
  margin: 0;
  line-height: 1.55;
}
.welcome-crest {
  font-size: 60px;
  color: rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
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

@media (max-width: 768px) {
  .welcome-card {
    flex-direction: column;
    text-align: center;
    gap: 16px;
    padding: 20px;
  }
  .welcome-crest {
    display: none;
  }
}

@media (max-width: 640px) {
  .stats-row { grid-template-columns: 1fr; }
}

@media (max-width: 576px) {
  .activity-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
    padding: 12px 16px;
  }
  .activity-tag {
    align-self: flex-start;
  }
}
</style>
