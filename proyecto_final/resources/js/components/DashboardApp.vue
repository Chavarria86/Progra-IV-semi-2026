<template>
  <div class="dashboard-layout">
    
    <!-- HEADER -->
    <header class="dashboard-header">
      <div class="header-left">
        <!-- Puedes poner un logo aquí en el futuro -->
        <div class="logo-placeholder"></div>
      </div>
      <div class="header-center">
        <h1>Bienvenido a su perfil profesional!</h1>
      </div>
      <div class="header-right">
        <div class="avatar-dropdown">
          <div class="avatar-circle">
            <i class="bi bi-person-fill"></i>
          </div>
          <div class="dropdown-menu-custom">
            <p class="user-name">{{ usuario.nombres }}</p>
            <p class="user-role">{{ usuario.rol }}</p>
            <button class="btn-logout" @click="cerrarSesion">Cerrar Sesión</button>
          </div>
        </div>
      </div>
    </header>

    <div class="dashboard-body">
      <!-- SIDEBAR -->
      <aside class="dashboard-sidebar">
        <nav class="sidebar-nav">
          <ul>
            <li :class="{ active: seccionActiva === 'perfil' }" @click="seccionActiva = 'perfil'"><a href="#">Perfil Profesional</a></li>
            <li :class="{ active: seccionActiva === 'cv' }" @click="seccionActiva = 'cv'"><a href="#"><i class="bi bi-file-earmark-person me-2"></i>Mi CV</a></li>
            <li :class="{ active: seccionActiva === 'informes' }" @click="seccionActiva = 'informes'"><a href="#">Informes</a></li>
            <li :class="{ active: seccionActiva === 'progreso' }" @click="seccionActiva = 'progreso'"><a href="#">Historial de Progreso</a></li>
            <li :class="{ active: seccionActiva === 'vacantes' }" @click="seccionActiva = 'vacantes'"><a href="#">Vacantes de Empresas</a></li>
          </ul>
        </nav>
      </aside>

      <!-- MAIN CONTENT -->
      <main class="dashboard-content">
        <component :is="dashboardComponent" :seccionActiva="seccionActiva"></component>
      </main>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const seccionActiva = ref('perfil');
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

const cerrarSesion = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('usuario');
  window.location.href = '/login';
};
</script>

<style scoped>
.dashboard-layout {
  display: flex;
  flex-direction: column;
  height: 100vh;
  font-family: 'Inter', sans-serif;
  background-color: #EFEFEF; /* Gris claro de fondo */
}

/* Header */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #010C67; /* Color primario */
  color: white;
  padding: 10px 40px;
  height: 90px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  z-index: 10;
}

.header-center h1 {
  font-family: 'Lora', serif;
  font-size: 28px;
  font-weight: 500;
  margin: 0;
}

.avatar-dropdown {
  position: relative;
  cursor: pointer;
}

.avatar-circle {
  width: 60px;
  height: 60px;
  background-color: #DEDCDC;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 35px;
  color: #555;
}

.dropdown-menu-custom {
  display: none;
  position: absolute;
  top: 70px;
  right: 0;
  background-color: white;
  color: black;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  border-radius: 8px;
  padding: 15px;
  width: 200px;
  z-index: 20;
}

.avatar-dropdown:hover .dropdown-menu-custom {
  display: block;
}

.user-name {
  font-weight: bold;
  margin-bottom: 5px;
}

.user-role {
  font-size: 14px;
  color: #666;
  margin-bottom: 15px;
  text-transform: capitalize;
}

.btn-logout {
  background-color: #67000F;
  color: white;
  border: none;
  width: 100%;
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
}

.btn-logout:hover {
  background-color: #4a000a;
}

/* Body (Sidebar + Content) */
.dashboard-body {
  display: flex;
  flex: 1;
  overflow: hidden;
}

/* Sidebar */
.dashboard-sidebar {
  width: 250px;
  background-color: #010C67;
  color: white;
  display: flex;
  flex-direction: column;
  padding-top: 20px;
  border-right: 1px solid #00589B;
}

.sidebar-nav ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-nav li {
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.sidebar-nav li a {
  display: block;
  padding: 20px 25px;
  color: white;
  text-decoration: none;
  font-size: 18px;
  font-family: 'Lora', serif;
  transition: background-color 0.3s;
}

.sidebar-nav li:hover a, .sidebar-nav li.active a {
  background-color: #00589B; /* Color secundario 1 */
}

/* Main Content */
.dashboard-content {
  flex: 1;
  padding: 30px;
  overflow-y: auto;
  background-color: #EFEFEF;
}
</style>
