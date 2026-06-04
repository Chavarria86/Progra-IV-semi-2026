<template>
  <div class="supervisor-overview animate-fade-in">
    <!-- ── WELCOME BANNER ──────────────────────────────────── -->
    <div class="welcome-card">
      <div class="welcome-left">
        <h3>¡Bienvenido, Supervisor!</h3>
        <p>Gestiona los currículums de tus pasantes, valida su progreso y asigna vacantes disponibles desde este panel de control.</p>
      </div>
      <div class="welcome-right">
        <div class="welcome-crest">
          <i class="bi bi-person-check-fill"></i>
        </div>
      </div>
    </div>

    <!-- ── STATS CARDS ──────────────────────────────────────── -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon bg-blue"><i class="bi bi-people-fill"></i></div>
        <div class="stat-info">
          <div class="stat-num">{{ stats.pasantesPendientes }}</div>
          <div class="stat-label">Pasantes pendientes</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-green"><i class="bi bi-check-circle-fill"></i></div>
        <div class="stat-info">
          <div class="stat-num">{{ stats.cvsAprobados }}</div>
          <div class="stat-label">CVs aprobados</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-orange"><i class="bi bi-building-fill-gear"></i></div>
        <div class="stat-info">
          <div class="stat-num">{{ stats.vacantesActivas }}</div>
          <div class="stat-label">Vacantes activas</div>
        </div>
      </div>
    </div>

    <!-- ── ACTIVIDAD RECIENTE ─────────────────────────────── -->
    <div class="row">
      <!-- Actividad Reciente -->
      <div class="col-12 mb-4">
        <div class="dashboard-section-card">
          <h5 class="card-title"><i class="bi bi-clock-history text-primary"></i> Actividad Reciente</h5>
          <div class="activity-list mt-3">
            <div v-for="item in actividadReciente" :key="item.id" class="activity-item">
              <div class="activity-avatar" :class="item.tipo === 'aprobado' ? 'bg-green' : 'bg-orange'">
                <i :class="item.tipo === 'aprobado' ? 'bi bi-check-lg' : 'bi bi-arrow-repeat'"></i>
              </div>
              <div class="activity-info">
                <strong>{{ item.nombre }}</strong>
                <span>{{ item.accion }}</span>
              </div>
              <div class="activity-meta">
                <span class="activity-badge" :class="item.tipo === 'aprobado' ? 'badge-success' : 'badge-warning'">
                  {{ item.tipo === 'aprobado' ? 'Aprobado' : 'Pendiente' }}
                </span>
                <small>{{ item.fecha }}</small>
              </div>
            </div>
            <div v-if="actividadReciente.length === 0" class="text-center py-4 text-muted">
              No hay actividad reciente registrada.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  stats: { type: Object, default: () => ({ pasantesPendientes: 0, cvsAprobados: 0, vacantesActivas: 0 }) },
  actividadReciente: { type: Array, default: () => [] }
});
</script>

<style scoped>
.supervisor-overview {
  width: 100%;
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Welcome Card */
.welcome-card {
  background: linear-gradient(135deg, #7c3a00 0%, #5c2800 100%);
  border-radius: 14px;
  padding: 30px;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
  box-shadow: 0 10px 25px -5px rgba(124, 58, 0, 0.25);
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

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 28px;
}
.stat-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  gap: 18px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.03);
  transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.07);
}
.stat-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
}
.bg-blue { background-color: #eff6ff; color: #1d4ed8; }
.bg-green { background-color: #ecfdf5; color: #059669; }
.bg-orange { background-color: #fff7ed; color: #c2410c; }
.stat-num {
  font-size: 30px;
  font-weight: 800;
  color: #0f172a;
  line-height: 1;
}
.stat-label {
  font-size: 13px;
  color: #64748b;
  margin-top: 3px;
}

/* Section Cards */
.dashboard-section-card {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 28px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.03);
  border: 1px solid #e2e8f0;
  margin-bottom: 24px;
}
.card-title {
  font-family: 'Lora', serif;
  font-size: 18px;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}
.text-primary {
  color: #001374 !important;
}

/* Activity List */
.activity-list {
  display: flex;
  flex-direction: column;
  gap: 0;
}
.activity-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 0;
  border-bottom: 1px solid #f1f5f9;
}
.activity-item:last-child { border-bottom: none; }
.activity-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}
.activity-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.activity-info strong {
  font-size: 14px;
  font-weight: 600;
  color: #0f172a;
}
.activity-info span {
  font-size: 13px;
  color: #64748b;
}
.activity-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}
.activity-badge {
  font-size: 11px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.badge-success { background: #d1fae5; color: #065f46; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-warning { background: #fef3c7; color: #92400e; }
.activity-meta small {
  font-size: 12px;
  color: #94a3b8;
}

/* Area Bars Chart */
.area-bars {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.area-row {
  display: grid;
  grid-template-columns: 160px 1fr 30px;
  align-items: center;
  gap: 12px;
}
.area-label {
  font-size: 13.5px;
  font-weight: 600;
  color: #475569;
}
.area-bar-track {
  background: #f1f5f9;
  border-radius: 20px;
  height: 10px;
  overflow: hidden;
}
.area-bar-fill {
  height: 100%;
  border-radius: 20px;
  transition: width 1s ease-out;
}
.area-score {
  font-size: 14px;
  color: #0f172a;
  text-align: right;
}

.text-accent { color: #67000F !important; }

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
  .stats-grid { grid-template-columns: 1fr; }
  .area-row { grid-template-columns: 1fr; gap: 4px; }
  .area-label { margin-bottom: 2px; }
}

@media (max-width: 576px) {
  .activity-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  .activity-meta {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding-left: 54px;
  }
}
</style>
