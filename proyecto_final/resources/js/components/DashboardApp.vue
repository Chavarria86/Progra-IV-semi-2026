<template>
  <div class="dashboard-layout" :class="{ 'dark-mode': darkActive }">
    
    <!-- OVERLAY FOR MOBILE SIDEBAR -->
    <div class="sidebar-overlay" v-if="sidebarOpen" @click="sidebarOpen = false"></div>

    <!-- SIDEBAR -->
    <aside class="dashboard-sidebar" :class="{ 'open': sidebarOpen }">
      <div class="sidebar-header">
        <!-- Tu nuevo logo en imagen y toggle de modo oscuro -->
        <div class="d-flex flex-column align-items-center w-100 gap-3">
          <img src="/images/logo_ugb.png" alt="Logo UGB" class="ugb-logo-img">
        </div>
      </div>

      <nav class="sidebar-nav">
        <ul>
          <li
            v-for="item in navItems"
            :key="item.seccion"
            :class="{ active: seccionActiva === item.seccion || (item.alias && item.alias.includes(seccionActiva)), 'nav-logout': item.logout }"
            @click="item.logout ? cerrarSesion() : cambiarSeccion(item.seccion)"
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
      <!-- MOBILE TOP BAR -->
      <header class="mobile-top-bar">
        <button class="menu-toggle-btn" @click="toggleSidebar">
          <i class="bi bi-list"></i>
        </button>
        <img src="/images/logo_ugb.png" alt="Logo UGB" class="mobile-logo">
        <div style="width: 40px;"></div> <!-- spacer to center the logo roughly -->
      </header>

      <!-- MAIN CONTENT -->
      <main class="dashboard-content">
        <AiChatWindow
          v-if="seccionActiva === 'chat_ia'"
          :usuario="usuario"
        />
        <ConfiguracionApp
          v-else-if="seccionActiva === 'configuracion'"
          :isDark="darkActive"
          @toggleDarkMode="toggleDarkMode"
        />
        <component 
          v-else
          :is="dashboardComponent" 
          :seccionActiva="seccionActiva" 
          :isDark="darkActive"
          @cambiarSeccion="cambiarSeccion"
        ></component>
      </main>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AiChatWindow from './AiChatWindow.vue';
import ConfiguracionApp from './ConfiguracionApp.vue';

const seccionActiva = ref('dashboard');
const sidebarOpen = ref(false);
const darkActive = ref(localStorage.getItem('theme') === 'dark');

const toggleDarkMode = () => {
  darkActive.value = !darkActive.value;
  localStorage.setItem('theme', darkActive.value ? 'dark' : 'light');
};

import PasanteDashboard from './PasanteDashboard.vue';
import SupervisorDashboard from './SupervisorDashboard.vue';
import ViceDecanoDashboard from './ViceDecanoDashboard.vue';

const usuario = ref({});

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

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
      { seccion: 'ver_vacantes',      label: 'Sugerir Vacantes',    icon: 'bi bi-building-fill-add' },
      { seccion: 'recomendaciones',   label: 'Recomendaciones',     icon: 'bi bi-award-fill' },
      { seccion: 'chat_ia',           label: 'Análisis IA',         icon: 'bi bi-search-heart' },
      { seccion: 'configuracion',     label: 'Configuración',       icon: 'bi bi-gear-fill' },
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
      { seccion: 'estadisticas',         label: 'Estadísticas',          icon: 'bi bi-bar-chart-fill' },
      { seccion: 'chat_ia',              label: 'Análisis IA',           icon: 'bi bi-search-heart' },
      { seccion: 'configuracion',        label: 'Configuración',         icon: 'bi bi-gear-fill' },
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
    { seccion: 'chat_ia',      label: 'Análisis IA',          icon: 'bi bi-search-heart' },
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
    configuracion:        'Configuración',
    validar_cv:           'Validar CVs de Pasantes',
    asignar:              'Asignar Vacantes',
    mis_pasantes:         'Gestión de Pasantes',
    solicitudes:          'Solicitudes de Pasantes',
    evaluar_informes:     'Evaluar Informes y Validar Horas',
    ver_vacantes:         'Sugerir Vacantes a Pasantes',
    recomendaciones:      'Recomendaciones para Pasantes',
    evaluar_informe:      'Evaluar Informes Finales',
    estadisticas:         'Estadísticas Generales',
    crear_vacantes:       'Crear Nuevas Vacantes',
    asignar_supervisores: 'Asignación de Supervisores',
    vista_supervisores:   'Modo Supervisor Global',
    chat_ia:              'Análisis IA',
  };
  return labels[seccion] ?? 'Dashboard';
};

const cambiarSeccion = (nuevaSeccion) => {
  seccionActiva.value = nuevaSeccion;
  sidebarOpen.value = false;
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
  background-color: #F4F4F4;
  overflow: hidden;
  position: relative;
}

/* Sidebar Overlay */
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.4);
  z-index: 998;
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
  z-index: 1000;
  height: 100%;
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
  overflow-y: auto;
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

/* Mobile Top Bar */
.mobile-top-bar {
  display: none;
  height: 64px;
  background-color: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  flex-shrink: 0;
  z-index: 90;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}

.menu-toggle-btn {
  background: none;
  border: none;
  font-size: 28px;
  color: #000B58;
  cursor: pointer;
  padding: 5px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: background-color 0.2s;
}

.menu-toggle-btn:hover {
  background-color: #f1f5f9;
}

.mobile-logo {
  height: 44px;
  object-fit: contain;
}

/* Main Content */
.dashboard-content {
  flex: 1;
  padding: 35px 40px;
  overflow-y: auto;
  background-color: #F4F4F4;
}

/* Responsive Styles */
@media (max-width: 991px) {
  .mobile-top-bar {
    display: flex;
  }

  .dashboard-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 10px 0 30px rgba(0,0,0,0.1);
  }

  .dashboard-sidebar.open {
    transform: translateX(0);
  }

  .dashboard-content {
    padding: 20px 16px;
  }
}
</style>
