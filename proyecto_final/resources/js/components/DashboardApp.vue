<template>
  <div class="dashboard-layout">
    
    <!-- SIDEBAR -->
    <aside class="dashboard-sidebar">
      <div class="sidebar-header">
        <!-- Tu nuevo logo en imagen -->
        <img src="/images/logo_ugb.png" alt="Logo UGB" class="ugb-logo-img">
      </div>

      <nav class="sidebar-nav">
        <ul>
          <li
            v-for="item in navItems"
            :key="item.seccion"
            :class="{ active: seccionActiva === item.seccion || (item.alias && item.alias.includes(seccionActiva)), 'nav-logout': item.logout }"
            @click="item.logout ? cerrarSesion() : (seccionActiva = item.seccion)"
          >
            <a href="#">
              <i :class="item.icon + ' me-3'"></i>
              <span>{{ item.label }}</span>
            </a>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- MAIN AREA -->
    <div class="main-area">
      <!-- MAIN HEADER -->
      <header class="main-header">
        <div class="page-title">
          <h2>{{ formatTitle(seccionActiva) }}</h2>
        </div>
        <div class="user-profile-section">
          <div class="avatar-dropdown">
            <div class="avatar-circle">
              <i class="bi bi-person-fill"></i>
            </div>
            <div class="dropdown-menu-custom">
              <p class="user-name">{{ usuario.nombres }} {{ usuario.apellidos }}</p>
              <p class="user-role">{{ usuario.rol }}</p>
              <button class="btn-logout" @click="cerrarSesion">Cerrar Sesión</button>
            </div>
          </div>
        </div>
      </header>

      <!-- MAIN CONTENT -->
      <main class="dashboard-content">
        <component 
          :is="dashboardComponent" 
          :seccionActiva="seccionActiva" 
          @cambiarSeccion="cambiarSeccion"
        ></component>
      </main>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const seccionActiva = ref('dashboard');
import PasanteDashboard from './PasanteDashboard.vue';
import SupervisorDashboard from './SupervisorDashboard.vue';
import ViceDecanoDashboard from './ViceDecanoDashboard.vue';

const usuario = ref({});

onMounted(() => {
  const userJson = localStorage.getItem('usuario');
  if (!userJson) {
    window.location.href = '/login';
    return;
  }
  usuario.value = JSON.parse(userJson);
});

const dashboardComponent = computed(() => {
  if (usuario.value.rol === 'pasante') return PasanteDashboard;
  if (usuario.value.rol === 'supervisor') return SupervisorDashboard;
  if (usuario.value.rol === 'vice_decano') return ViceDecanoDashboard;
  return null;
});

// ── Sidebar items según rol ─────────────────────────────────
const navItems = computed(() => {
  const rol = usuario.value.rol;

  if (rol === 'supervisor') {
    return [
      { seccion: 'dashboard',        label: 'Dashboard',           icon: 'bi bi-grid-fill' },
      { seccion: 'solicitudes',       label: 'Solicitudes',         icon: 'bi bi-person-plus-fill' },
      { seccion: 'validar_cv',        label: 'Validar CVs',         icon: 'bi bi-check2-square' },
      { seccion: 'mis_pasantes',      label: 'Mis Pasantes',        icon: 'bi bi-people-fill' },
      { seccion: 'evaluar_informes',  label: 'Evaluar Informes',    icon: 'bi bi-file-earmark-check-fill' },
      { seccion: 'recomendaciones',   label: 'Recomendaciones',     icon: 'bi bi-award-fill' },
      { seccion: 'logout',            label: 'Cerrar sesión',       icon: 'bi bi-box-arrow-left', logout: true },
    ];
  }

  if (rol === 'vice_decano') {
    return [
      { seccion: 'dashboard',            label: 'Dashboard',             icon: 'bi bi-grid-fill' },
      { seccion: 'crear_vacantes',       label: 'Crear Vacantes',        icon: 'bi bi-plus-square-dotted' },
      { seccion: 'asignar_supervisores', label: 'Asignar Supervisores',  icon: 'bi bi-person-lines-fill' },
      { seccion: 'evaluar_informe',      label: 'Evaluar Informes',      icon: 'bi bi-file-earmark-check-fill' },
      { seccion: 'vista_supervisores',   label: 'Modo Supervisor',       icon: 'bi bi-person-badge-fill' },
      { seccion: 'analisis_ia',          label: 'Análisis IA',           icon: 'bi bi-search-heart' },
      { seccion: 'estadisticas',         label: 'Estadísticas',          icon: 'bi bi-bar-chart-fill' },
      { seccion: 'logout',               label: 'Cerrar sesión',         icon: 'bi bi-box-arrow-left', logout: true },
    ];
  }

  // Pasante (default)
  return [
    { seccion: 'dashboard',    label: 'Dashboard',            icon: 'bi bi-grid-fill', alias: [] },
    { seccion: 'perfil',       label: 'Perfil',               icon: 'bi bi-person-fill', alias: ['cv'] },
    { seccion: 'informes',     label: 'Informes',             icon: 'bi bi-file-earmark-text-fill' },
    { seccion: 'vacantes',     label: 'Vacantes',             icon: 'bi bi-building-fill-add' },
    { seccion: 'progreso',     label: 'Historial de progreso',icon: 'bi bi-clock-fill' },
    { seccion: 'analisis_ia',  label: 'Análisis IA',          icon: 'bi bi-search-heart' },
    { seccion: 'configuracion',label: 'Configuración',        icon: 'bi bi-gear-fill' },
    { seccion: 'logout',       label: 'Cerrar sesión',        icon: 'bi bi-box-arrow-left', logout: true },
  ];
});

const formatTitle = (seccion) => {
  const labels = {
    dashboard:            'Dashboard',
    perfil:               'Perfil Profesional',
    cv:                   'Mi Currículum Vitae',
    informes:             'Informes',
    vacantes:             'Vacantes Disponibles',
    progreso:             'Historial de Progreso',
    analisis_ia:          'Análisis IA',
    configuracion:        'Configuración',
    validar_cv:           'Validar CVs de Pasantes',
    asignar:              'Asignar Vacantes',
    mis_pasantes:         'Gestión de Pasantes',
    solicitudes:          'Solicitudes de Pasantes',
    evaluar_informes:     'Evaluar Informes y Validar Horas',
    recomendaciones:      'Recomendaciones para Pasantes',
    evaluar_informe:      'Evaluar Informes Finales',
    estadisticas:         'Estadísticas Generales',
    crear_vacantes:       'Crear Nuevas Vacantes',
    asignar_supervisores: 'Asignación de Supervisores',
    vista_supervisores:   'Modo Supervisor Global',
  };
  return labels[seccion] ?? 'Dashboard';
};

const cambiarSeccion = (nuevaSeccion) => {
  seccionActiva.value = nuevaSeccion;
};

const cerrarSesion = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('usuario');
  window.location.href = '/login';
};
</script>

<style scoped>
.dashboard-layout {
  display: flex;
  height: 100vh;
  width: 100vw;
  font-family: 'Inter', sans-serif;
  background-color: #f8fafc;
  overflow: hidden;
}

/* Sidebar */
.dashboard-sidebar {
  width: 280px;
  background-color: #ffffff;
  display: flex;
  flex-direction: column;
  border-right: 1px solid #e2e8f0;
  box-shadow: 4px 0 20px rgba(0,0,0,0.04);
  flex-shrink: 0;
  z-index: 10;
}

.sidebar-header {
  padding: 30px 24px 20px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.ugb-logo-img {
  max-width: 100%;
  max-height: 80px;
  object-fit: contain;
  display: block;
  margin: 0 auto;
}

.sidebar-nav {
  flex: 1;
}

.sidebar-nav ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-nav li {
  width: 100%;
}

.sidebar-nav li a, .nav-logout a {
  display: flex;
  align-items: center;
  padding: 16px 28px;
  color: #1e293b;
  text-decoration: none;
  font-size: 16px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.sidebar-nav li:hover a {
  background-color: #f1f5f9;
  color: #010C67;
}

.sidebar-nav li.active a {
  background-color: #001374; /* Royal blue */
  color: #ffffff;
  font-weight: 600;
}

.sidebar-nav li a i, .nav-logout a i {
  font-size: 20px;
  color: inherit;
}

.nav-logout {
  cursor: pointer;
  border-top: 1px solid #f1f5f9;
  margin-top: auto; /* Push logout to bottom if there's space */
}

.nav-logout:hover a {
  background-color: #fef2f2;
  color: #991b1b;
}

/* Main Area */
.main-area {
  display: flex;
  flex-direction: column;
  flex: 1;
  overflow: hidden;
  height: 100vh;
}

/* Main Header */
.main-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #ffffff;
  padding: 15px 40px;
  height: 75px;
  border-bottom: 1px solid #e2e8f0;
  flex-shrink: 0;
}

.page-title h2 {
  font-family: 'Lora', serif;
  font-size: 24px;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.avatar-dropdown {
  position: relative;
  cursor: pointer;
}

.avatar-circle {
  width: 45px;
  height: 45px;
  background-color: #e2e8f0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  color: #475569;
  transition: background-color 0.2s;
  border: 2px solid #001374;
}

.avatar-circle:hover {
  background-color: #cbd5e1;
}

.dropdown-menu-custom {
  display: none;
  position: absolute;
  top: 55px;
  right: 0;
  background-color: white;
  color: #1e293b;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  padding: 15px;
  width: 220px;
  z-index: 100;
}

.avatar-dropdown:hover .dropdown-menu-custom {
  display: block;
}

.user-name {
  font-weight: 600;
  font-size: 15px;
  margin-bottom: 2px;
  color: #0f172a;
}

.user-role {
  font-size: 13px;
  color: #64748b;
  margin-bottom: 12px;
  text-transform: capitalize;
}

.btn-logout {
  background-color: #ef4444;
  color: white;
  border: none;
  width: 100%;
  padding: 8px;
  border-radius: 6px;
  font-weight: 500;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-logout:hover {
  background-color: #dc2626;
}

/* Main Content */
.dashboard-content {
  flex: 1;
  padding: 35px 40px;
  overflow-y: auto;
  background-color: #f8fafc;
}
</style>
