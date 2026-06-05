<template>
  <div class="evaluar-informes-view animate-fade-in">
    <div class="dashboard-section-card">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
          <h5 class="card-title mb-1"><i class="bi bi-file-earmark-check-fill text-primary"></i> Control de Informes y Horas</h5>
          <p class="text-muted small mb-0">Revisa los informes mensuales de tus pasantes y lleva el registro histórico de las evaluaciones realizadas.</p>
        </div>
      </div>

      <!-- Pestañas de Navegación -->
      <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3">
        <li class="nav-item">
          <button 
            class="nav-link px-4 py-2" 
            :class="{ active: tabActiva === 'pendientes' }" 
            @click="cambiarTab('pendientes')"
          >
            <i class="bi bi-hourglass-split me-2"></i> Pendientes por Evaluar
            <span class="badge ms-2" :class="tabActiva === 'pendientes' ? 'bg-white text-primary' : 'bg-primary text-white'">
              {{ informesPendientes.length }}
            </span>
          </button>
        </li>
        <li class="nav-item">
          <button 
            class="nav-link px-4 py-2" 
            :class="{ active: tabActiva === 'historial' }" 
            @click="cambiarTab('historial')"
          >
            <i class="bi bi-clock-history me-2"></i> Historial de Evaluados
            <span class="badge ms-2" :class="tabActiva === 'historial' ? 'bg-white text-primary' : 'bg-secondary text-white'">
              {{ informesRevisados.length }}
            </span>
          </button>
        </li>
      </ul>

      <!-- ── SECCIÓN PENDIENTES ── -->
      <div v-if="tabActiva === 'pendientes'">
        <div v-if="cargando" class="text-center py-4">
          <span class="spinner-border text-primary"></span>
          <p class="text-muted mt-2">Cargando informes...</p>
        </div>

        <div v-else-if="informesPendientes.length === 0" class="text-center py-5 text-muted bg-light rounded border">
          <i class="bi bi-check-circle d-block fs-1 mb-3 text-success"></i>
          <span>No tienes informes pendientes por evaluar. ¡Todo al día!</span>
        </div>

        <div v-else class="informes-grid">
          <div v-for="informe in informesPendientes" :key="informe.id" class="informe-card border rounded p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3 flex-wrap gap-2">
              <div>
                <h5 class="fw-bold text-dark mb-1">{{ informe.nombre }}</h5>
                <p class="text-muted small mb-0"><i class="bi bi-person-fill me-1"></i> {{ informe.pasante }} | <i class="bi bi-calendar-event me-1"></i> {{ informe.fecha }}</p>
                <div v-if="informe.fecha_inicio && informe.fecha_fin" class="small text-secondary mt-1">
                  <i class="bi bi-calendar-range me-1 text-primary"></i> <strong>Período:</strong> {{ informe.fecha_inicio }} al {{ informe.fecha_fin }}
                </div>
              </div>
              <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="bi bi-hourglass-split me-1"></i> {{ informe.horas }} horas reportadas</span>
            </div>

            <div v-if="informe.bitacora && informe.bitacora.length > 0" class="mb-3">
              <h6 class="fw-bold text-primary mb-2"><i class="bi bi-list-columns-reverse me-1"></i> Bitácora de Actividades</h6>
              <div class="table-responsive">
                <table class="table table-bordered table-sm custom-detail-table">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 15%;">Fecha</th>
                      <th style="width: 25%;">Objetivo</th>
                      <th style="width: 35%;">Actividades</th>
                      <th style="width: 25%;">Logros y conclusiones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(fila, idx) in informe.bitacora" :key="idx">
                      <td class="text-nowrap">{{ formatearFechaSencilla(fila.fecha) }}</td>
                      <td>{{ fila.objetivo }}</td>
                      <td class="pre-wrap">{{ fila.actividades }}</td>
                      <td>{{ fila.logros }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div v-else class="informe-content mb-3">
              <h6 class="fw-bold text-primary">Objetivos</h6>
              <p class="small text-muted">{{ informe.objetivos }}</p>
              
              <h6 class="fw-bold text-primary mt-3">Actividades Realizadas</h6>
              <p class="small text-muted">{{ informe.actividades }}</p>

              <h6 class="fw-bold text-primary mt-3">Conclusiones</h6>
              <p class="small text-muted">{{ informe.conclusiones }}</p>
            </div>

            <div v-if="informe.archivo_url" class="mt-3 mb-3">
              <a :href="informe.archivo_url" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-file-earmark-pdf-fill me-1 text-danger"></i> Ver PDF Oficial
              </a>
            </div>

            <!-- Imágenes de Evidencia -->
            <div class="mb-3 mt-4" v-if="informe.imagenes && informe.imagenes.length > 0">
              <span class="small text-muted fw-bold d-block mb-2"><i class="bi bi-images me-1 text-primary"></i> Imágenes de Evidencia / Anexos:</span>
              <div class="row row-cols-2 row-cols-md-4 g-2">
                <div class="col" v-for="(imgUrl, idx) in informe.imagenes" :key="idx">
                  <a :href="imgUrl" target="_blank" title="Ver imagen ampliada">
                    <div class="border rounded p-1 bg-white" style="height: 100px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                      <img :src="imgUrl" class="img-thumbnail border-0" alt="Evidencia" style="object-fit: cover; height: 100%; width: 100%;">
                    </div>
                  </a>
                </div>
              </div>
            </div>

            <div v-if="usuario.rol === 'vice_decano'" class="alert alert-info py-2 px-3 small mb-0 w-100 text-center">
              <i class="bi bi-info-circle-fill me-1"></i> Modo Vista: El Vicedecano solo puede visualizar los informes sin realizar acciones.
            </div>
            <div v-else class="informe-actions d-flex gap-3 justify-content-end bg-light p-3 rounded align-items-center">
              <button class="btn btn-outline-primary px-3 me-auto" @click="abrirRecomendacionRapida(informe)" :disabled="procesando === informe.id">
                <i class="bi bi-chat-left-quote me-1"></i> Dejar Recomendación
              </button>
              <button class="btn btn-outline-danger px-4" @click="rechazar(informe)" :disabled="procesando === informe.id">
                <i class="bi bi-x-circle me-1"></i> Solicitar Correcciones
              </button>
              <button class="btn btn-success px-4" @click="aprobar(informe)" :disabled="procesando === informe.id">
                <span v-if="procesando === informe.id" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-check-circle me-1"></i> Aprobar y Validar Horas
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ── SECCIÓN HISTORIAL ── -->
      <div v-if="tabActiva === 'historial'">
        <!-- Barra de Búsqueda -->
        <div class="mb-4">
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input 
              type="text" 
              class="form-control border-start-0 ps-1" 
              placeholder="Buscar por pasante o título de informe..." 
              v-model="busquedaHistorial"
            >
          </div>
        </div>

        <div v-if="cargando" class="text-center py-4">
          <span class="spinner-border text-secondary"></span>
          <p class="text-muted mt-2">Cargando historial de informes...</p>
        </div>

        <div v-else-if="historialFiltrado.length === 0" class="text-center py-5 text-muted bg-light rounded border">
          <i class="bi bi-archive d-block fs-1 mb-3 text-secondary"></i>
          <span>No se encontraron reportes revisados que coincidan con la búsqueda.</span>
        </div>

        <div v-else class="informes-grid">
          <div v-for="informe in historialFiltrado" :key="informe.id" class="informe-card border rounded p-4 mb-4 status-card" :class="'border-status-' + informe.estado">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3 flex-wrap gap-2">
              <div>
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                  <h5 class="fw-bold text-dark mb-0">{{ informe.nombre }}</h5>
                  <span class="badge" :class="informe.estado === 'aprobado' ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger'">
                    <i :class="informe.estado === 'aprobado' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-triangle-fill'"></i>
                    {{ informe.estado === 'aprobado' ? 'Aprobado' : 'Corrección Solicitada' }}
                  </span>
                </div>
                <p class="text-muted small mb-0">
                  <i class="bi bi-person-fill me-1"></i> {{ informe.pasante }} | 
                  <i class="bi bi-calendar-event me-1"></i> {{ informe.fecha }} 
                  <span v-if="informe.fecha_revision" class="ms-1 text-secondary">
                    (Revisado: {{ informe.fecha_revision }})
                  </span>
                </p>
                <div v-if="informe.fecha_inicio && informe.fecha_fin" class="small text-secondary mt-1">
                  <i class="bi bi-calendar-range me-1 text-primary"></i> <strong>Período:</strong> {{ informe.fecha_inicio }} al {{ informe.fecha_fin }}
                </div>
              </div>
              <span class="badge bg-secondary text-white fs-6 px-3 py-2"><i class="bi bi-check2-all me-1"></i> {{ informe.horas }} horas</span>
            </div>

            <!-- Detalles Expandibles -->
            <div class="mb-3">
              <button class="btn btn-link btn-sm p-0 text-decoration-none text-primary" @click="toggleDetalle(informe.id)">
                <i class="bi" :class="detallesAbiertos.includes(informe.id) ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                {{ detallesAbiertos.includes(informe.id) ? 'Ocultar contenidos del informe' : 'Ver contenidos del informe' }}
              </button>
            </div>

            <div v-if="detallesAbiertos.includes(informe.id)" class="mb-3 p-3 bg-light rounded border border-light-subtle">
              <div v-if="informe.bitacora && informe.bitacora.length > 0">
                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-list-columns-reverse me-1"></i> Bitácora de Actividades</h6>
                <div class="table-responsive bg-white rounded p-1 border">
                  <table class="table table-bordered table-sm custom-detail-table mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 15%;">Fecha</th>
                        <th style="width: 25%;">Objetivo</th>
                        <th style="width: 35%;">Actividades</th>
                        <th style="width: 25%;">Logros y conclusiones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(fila, idx) in informe.bitacora" :key="idx">
                        <td class="text-nowrap">{{ formatearFechaSencilla(fila.fecha) }}</td>
                        <td>{{ fila.objetivo }}</td>
                        <td class="pre-wrap">{{ fila.actividades }}</td>
                        <td>{{ fila.logros }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div v-else class="informe-content">
                <h6 class="fw-bold text-primary">Objetivos</h6>
                <p class="small text-muted">{{ informe.objetivos }}</p>
                
                <h6 class="fw-bold text-primary mt-3">Actividades Realizadas</h6>
                <p class="small text-muted">{{ informe.actividades }}</p>

                <h6 class="fw-bold text-primary mt-3">Conclusiones</h6>
                <p class="small text-muted">{{ informe.conclusiones }}</p>
              </div>

              <div v-if="informe.archivo_url" class="mt-3">
                <a :href="informe.archivo_url" target="_blank" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-file-earmark-pdf-fill me-1 text-danger"></i> Ver PDF Oficial
                </a>
              </div>
            </div>

            <!-- Sección de Observaciones -->
            <div class="observations-section p-3 rounded border" :class="informe.estado === 'aprobado' ? 'bg-success-light border-success-subtle' : 'bg-danger-light border-danger-subtle'">
              <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <span class="small fw-bold text-dark d-flex align-items-center">
                  <i class="bi bi-chat-left-text-fill me-2" :class="informe.estado === 'aprobado' ? 'text-success' : 'text-danger'"></i> 
                  Retroalimentación / Observaciones:
                </span>
                <button v-if="usuario.rol !== 'vice_decano'" class="btn btn-sm btn-outline-primary px-3 py-1" @click="iniciarEdicionObservacion(informe)">
                  <i class="bi bi-pencil-square me-1"></i> {{ informe.observaciones ? 'Editar Observación' : 'Agregar Observación' }}
                </button>
              </div>
              <div v-if="informe.observaciones">
                <p class="small mb-0 text-dark pre-wrap italic font-sans">{{ obtenerDetalleObservacion(informe.observaciones) }}</p>
                <div v-if="obtenerSeccionesObservacion(informe.observaciones).length > 0" class="mt-2 d-flex gap-2 flex-wrap align-items-center">
                  <span class="small text-muted me-1">Corrección solicitada en:</span>
                  <span v-for="sec in obtenerSeccionesObservacion(informe.observaciones)" :key="sec" class="badge bg-danger-subtle text-danger border border-danger-subtle small px-2">
                    {{ traduccirSeccion(sec) }}
                  </span>
                </div>
              </div>
              <p class="small mb-0 text-muted italic" v-else>Sin observaciones registradas por el supervisor.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── MODAL EDICIÓN OBSERVACIÓN ── -->
    <div class="modal fade show" v-if="modalEdicion.abierto" style="display: block; background: rgba(0,0,0,0.5); z-index: 1050;" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-chat-right-text text-primary me-2"></i> Retroalimentación del Supervisor</h5>
            <button type="button" class="btn-close" @click="cerrarModal" :disabled="guardandoObservacion"></button>
          </div>
          <div class="modal-body py-3">
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted mb-1">Pasante</label>
              <input type="text" class="form-control bg-light border-0" :value="modalEdicion.informe.pasante" disabled style="font-weight: 500;">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted mb-1">Informe</label>
              <input type="text" class="form-control bg-light border-0" :value="modalEdicion.informe.nombre" disabled style="font-weight: 500;">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted mb-1">Estado</label>
              <select class="form-select" v-model="modalEdicion.estado">
                <option value="aprobado">Aprobado (Horas Acreditadas)</option>
                <option value="correccion">Requiere Corrección</option>
              </select>
            </div>
            <div class="mb-3" v-if="modalEdicion.estado === 'correccion'">
              <label class="form-label small fw-bold text-muted mb-2">Secciones a corregir (Selecciona al menos una)</label>
              <div class="d-flex flex-column gap-2 bg-light p-3 rounded border">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="editChkNombre" v-model="modalEdicion.secciones.nombre">
                  <label class="form-check-label small fw-semibold text-dark" for="editChkNombre">
                    Nombre del Informe
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="editChkHoras" v-model="modalEdicion.secciones.horas">
                  <label class="form-check-label small fw-semibold text-dark" for="editChkHoras">
                    Horas a Reportar
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="editChkPeriodo" v-model="modalEdicion.secciones.periodo">
                  <label class="form-check-label small fw-semibold text-dark" for="editChkPeriodo">
                    Período (Fechas)
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="editChkBitacora" v-model="modalEdicion.secciones.bitacora">
                  <label class="form-check-label small fw-semibold text-dark" for="editChkBitacora">
                    Bitácora de Actividades
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="editChkImagenes" v-model="modalEdicion.secciones.imagenes">
                  <label class="form-check-label small fw-semibold text-dark" for="editChkImagenes">
                    Imágenes de Evidencia
                  </label>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted mb-1">Observaciones / Comentarios</label>
              <textarea 
                class="form-control" 
                rows="4" 
                placeholder="Escribe comentarios, observaciones o correcciones requeridas..." 
                v-model="modalEdicion.observaciones"
              ></textarea>
            </div>
          </div>
          <div class="modal-footer border-top-0 pt-0 d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary px-3" @click="cerrarModal" :disabled="guardandoObservacion">Cancelar</button>
            <button type="button" class="btn btn-primary px-4" @click="guardarObservacion" :disabled="guardandoObservacion">
              <span class="spinner-border spinner-border-sm me-1" v-if="guardandoObservacion"></span>
              {{ guardandoObservacion ? 'Guardando...' : 'Guardar Cambios' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── MODAL SOLICITAR CORRECCIONES ── -->
    <div class="modal fade show" v-if="modalRechazo.abierto" style="display: block; background: rgba(0,0,0,0.5); z-index: 1050;" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Solicitar Correcciones</h5>
            <button type="button" class="btn-close" @click="cerrarModalRechazo" :disabled="procesando === modalRechazo.informe.id"></button>
          </div>
          <div class="modal-body py-3">
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted mb-1">Pasante</label>
              <input type="text" class="form-control bg-light border-0" :value="modalRechazo.informe.pasante" disabled style="font-weight: 500;">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted mb-1">Informe</label>
              <input type="text" class="form-control bg-light border-0" :value="modalRechazo.informe.nombre" disabled style="font-weight: 500;">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted mb-2">Secciones a corregir (Selecciona al menos una)</label>
              <div class="d-flex flex-column gap-2 bg-light p-3 rounded border">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="chkNombre" v-model="modalRechazo.secciones.nombre">
                  <label class="form-check-label small fw-semibold text-dark" for="chkNombre">
                    Nombre del Informe
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="chkHoras" v-model="modalRechazo.secciones.horas">
                  <label class="form-check-label small fw-semibold text-dark" for="chkHoras">
                    Horas a Reportar
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="chkPeriodo" v-model="modalRechazo.secciones.periodo">
                  <label class="form-check-label small fw-semibold text-dark" for="chkPeriodo">
                    Período (Fechas)
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="chkBitacora" v-model="modalRechazo.secciones.bitacora">
                  <label class="form-check-label small fw-semibold text-dark" for="chkBitacora">
                    Bitácora de Actividades
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="chkImagenes" v-model="modalRechazo.secciones.imagenes">
                  <label class="form-check-label small fw-semibold text-dark" for="chkImagenes">
                    Imágenes de Evidencia
                  </label>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted mb-1">Observaciones / Instrucciones específicas</label>
              <textarea 
                class="form-control" 
                rows="4" 
                placeholder="Explica qué cambios debe realizar el pasante..." 
                v-model="modalRechazo.observaciones"
              ></textarea>
            </div>
          </div>
          <div class="modal-footer border-top-0 pt-0 d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary px-3" @click="cerrarModalRechazo" :disabled="procesando === modalRechazo.informe.id">Cancelar</button>
            <button type="button" class="btn btn-danger px-4" @click="enviarRechazo" :disabled="procesando === modalRechazo.informe.id">
              <span class="spinner-border spinner-border-sm me-1" v-if="procesando === modalRechazo.informe.id"></span>
              Enviar a Corrección
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── MODAL DEJAR RECOMENDACIÓN RÁPIDA ── -->
    <div class="modal fade show" v-if="modalRecomendacionRapida.abierto" style="display: block; background: rgba(0,0,0,0.5); z-index: 1050;" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-chat-left-quote text-primary me-2"></i> Dejar Recomendación</h5>
            <button type="button" class="btn-close" @click="cerrarRecomendacionRapida" :disabled="modalRecomendacionRapida.guardando"></button>
          </div>
          <div class="modal-body py-3">
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted mb-1">Informe</label>
              <input type="text" class="form-control bg-light border-0" :value="modalRecomendacionRapida.informe.nombre" disabled style="font-weight: 500;">
            </div>
            
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted mb-1">Sección del Informe</label>
              <select class="form-select" v-model="modalRecomendacionRapida.seccion">
                <option value="nombre">Nombre del Informe</option>
                <option value="horas">Horas a Reportar</option>
                <option value="periodo">Período (Fechas)</option>
                <option value="bitacora">Bitácora de Actividades</option>
                <option value="imagenes">Imágenes de Evidencia</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold text-muted mb-1">Recomendación / Sugerencia</label>
              <textarea 
                class="form-control" 
                rows="4" 
                placeholder="Escribe comentarios o sugerencias constructivas para esta sección..." 
                v-model="modalRecomendacionRapida.observaciones"
              ></textarea>
            </div>

            <!-- Listado de recomendaciones ya guardadas en esta sesión -->
            <div v-if="modalRecomendacionRapida.recomendacionesCreadas.length > 0" class="mt-3 border-top pt-3">
              <span class="small fw-bold text-muted d-block mb-2">Recomendaciones guardadas en esta sesión:</span>
              <div class="d-flex flex-column gap-2" style="max-height: 150px; overflow-y: auto;">
                <div v-for="(rec, idx) in modalRecomendacionRapida.recomendacionesCreadas" :key="idx" class="bg-success-light border border-success-subtle p-2 rounded small d-flex justify-content-between align-items-start">
                  <div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle me-2">{{ traduccirSeccion(rec.seccion) }}</span>
                    <span class="text-dark fst-italic">"{{ rec.texto }}"</span>
                  </div>
                  <i class="bi bi-check-circle-fill text-success ms-2"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer border-top-0 pt-0 d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary px-3" @click="cerrarRecomendacionRapida" :disabled="modalRecomendacionRapida.guardando">Cerrar</button>
            <button type="button" class="btn btn-primary px-4" @click="guardarRecomendacionRapida" :disabled="modalRecomendacionRapida.guardando">
              <span class="spinner-border spinner-border-sm me-1" v-if="modalRecomendacionRapida.guardando"></span>
              Guardar Recomendación
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  usuario: { type: Object, default: () => ({}) }
});

const tabActiva = ref('pendientes');
const cargando = ref(true);
const procesando = ref(null);

const informesPendientes = ref([]);
const informesRevisados = ref([]);

const busquedaHistorial = ref('');
const detallesAbiertos = ref([]);

const modalEdicion = ref({
  abierto: false,
  informe: {},
  observaciones: '',
  estado: '',
  secciones: {
    nombre: false,
    horas: false,
    periodo: false,
    bitacora: false,
    imagenes: false
  }
});
const guardandoObservacion = ref(false);

const modalRechazo = ref({
  abierto: false,
  informe: {},
  secciones: {
    nombre: false,
    horas: false,
    periodo: false,
    bitacora: false,
    imagenes: false
  },
  observaciones: ''
});

const feedbackInline = ref({});

const initFeedbackForInforme = (informeId) => {
  if (!feedbackInline.value[informeId]) {
    feedbackInline.value[informeId] = {
      secciones: { nombre: '', horas: '', periodo: '', bitacora: '', imagenes: '' },
      enviados: { nombre: false, horas: false, periodo: false, bitacora: false, imagenes: false }
    };
  }
};

const modalRecomendacionRapida = ref({
  abierto: false,
  informe: {},
  seccion: 'nombre',
  observaciones: '',
  guardando: false,
  recomendacionesCreadas: []
});

const abrirRecomendacionRapida = (informe) => {
  modalRecomendacionRapida.value.informe = informe;
  modalRecomendacionRapida.value.seccion = 'nombre';
  modalRecomendacionRapida.value.observaciones = '';
  modalRecomendacionRapida.value.guardando = false;
  modalRecomendacionRapida.value.recomendacionesCreadas = [];

  // Cargar recomendaciones ya hechas para este informe en esta sesión si existen
  initFeedbackForInforme(informe.id);
  const inline = feedbackInline.value[informe.id];
  const creadas = [];
  Object.keys(inline.enviados).forEach(sec => {
    if (inline.enviados[sec]) {
      creadas.push({
        seccion: sec,
        texto: inline.secciones[sec]
      });
    }
  });
  modalRecomendacionRapida.value.recomendacionesCreadas = creadas;
  modalRecomendacionRapida.value.abierto = true;
};

const cerrarRecomendacionRapida = () => {
  modalRecomendacionRapida.value.abierto = false;
  modalRecomendacionRapida.value.informe = {};
};

const guardarRecomendacionRapida = async () => {
  const { informe, seccion, observaciones } = modalRecomendacionRapida.value;
  if (!observaciones || !observaciones.trim()) {
    alertify.error('Por favor escribe la recomendación.');
    return;
  }

  modalRecomendacionRapida.value.guardando = true;
  try {
    await axios.post('/api/supervisor/recomendaciones', {
      pasante_id: informe.pasante_id,
      tipo: 'mejora',
      titulo: `Recomendación en Sección: ${traduccirSeccion(seccion)} - ${informe.nombre}`,
      contenido: observaciones.trim()
    }, {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });

    // Registrar en feedbackInline para el rechazo final
    initFeedbackForInforme(informe.id);
    feedbackInline.value[informe.id].secciones[seccion] = observaciones.trim();
    feedbackInline.value[informe.id].enviados[seccion] = true;

    // Agregar a la lista del modal
    modalRecomendacionRapida.value.recomendacionesCreadas.push({
      seccion: seccion,
      texto: observaciones.trim()
    });

    modalRecomendacionRapida.value.observaciones = '';
    alertify.success(`Recomendación para "${traduccirSeccion(seccion)}" guardada con éxito.`);
  } catch (err) {
    console.error('Error al guardar recomendación rápida:', err);
    alertify.error('Error al guardar la recomendación.');
  } finally {
    modalRecomendacionRapida.value.guardando = false;
  }
};

const formatearFechaSencilla = (fechaStr) => {
  if (!fechaStr) return '';
  const partes = fechaStr.split('-');
  if (partes.length === 3) {
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
  }
  return fechaStr;
};

onMounted(() => {
  cargarPendientes();
});

const cambiarTab = (tab) => {
  tabActiva.value = tab;
  if (tab === 'pendientes') {
    cargarPendientes();
  } else {
    cargarHistorial();
  }
};

const cargarPendientes = async () => {
  cargando.value = true;
  try {
    const res = await axios.get('/api/supervisor/informes', {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    informesPendientes.value = res.data.informes || [];
    informesPendientes.value.forEach(inf => {
      initFeedbackForInforme(inf.id);
    });
  } catch (error) {
    console.error('Error cargando informes pendientes:', error);
  } finally {
    cargando.value = false;
  }
};

const cargarHistorial = async () => {
  cargando.value = true;
  try {
    const res = await axios.get('/api/supervisor/informes-revisados', {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    informesRevisados.value = res.data.informes || [];
  } catch (error) {
    console.error('Error cargando historial:', error);
  } finally {
    cargando.value = false;
  }
};

const toggleDetalle = (id) => {
  if (detallesAbiertos.value.includes(id)) {
    detallesAbiertos.value = detallesAbiertos.value.filter(dId => dId !== id);
  } else {
    detallesAbiertos.value.push(id);
  }
};

const historialFiltrado = computed(() => {
  if (!busquedaHistorial.value.trim()) return informesRevisados.value;
  const q = busquedaHistorial.value.toLowerCase();
  return informesRevisados.value.filter(i => 
    i.pasante.toLowerCase().includes(q) || 
    i.nombre.toLowerCase().includes(q) ||
    (i.observaciones && i.observaciones.toLowerCase().includes(q))
  );
});

const aprobar = async (informe) => {
  // Opcional: Permitir agregar una observación al aprobar
  alertify.prompt(
    'Aprobar Informe',
    'Agrega alguna observación opcional sobre el informe:',
    '',
    async function(evt, value) {
      procesando.value = informe.id;
      try {
        await axios.put(`/api/supervisor/informes/${informe.id}/evaluar`, { 
          decision: 'aprobar',
          observaciones: value || ''
        }, {
          headers: { 'X-User-Id': props.usuario?.id || 2 }
        });
        informesPendientes.value = informesPendientes.value.filter(i => i.id !== informe.id);
        alertify.success(`Informe aprobado. Se validaron las horas.`);
      } catch (err) {
        alertify.error('Error al aprobar informe.');
      } finally {
        procesando.value = null;
      }
    },
    function() {}
  ).set('labels', {ok:'Aprobar sin observaciones', cancel:'Cancelar'});
};

const rechazar = (informe) => {
  modalRechazo.value.informe = informe;
  
  const inline = feedbackInline.value[informe.id];
  const secciones = {
    nombre: false,
    horas: false,
    periodo: false,
    bitacora: false,
    imagenes: false
  };
  let obsTexto = '';

  if (inline) {
    Object.keys(secciones).forEach(sec => {
      if (inline.enviados[sec]) {
        secciones[sec] = true;
        obsTexto += `[Sección: ${traduccirSeccion(sec)}]: ${inline.secciones[sec]}\n`;
      }
    });
  }

  modalRechazo.value.secciones = secciones;
  modalRechazo.value.observaciones = obsTexto.trim();
  modalRechazo.value.abierto = true;
};

const cerrarModalRechazo = () => {
  modalRechazo.value.abierto = false;
  modalRechazo.value.informe = {};
};

const enviarRechazo = async () => {
  const { informe, observaciones, secciones } = modalRechazo.value;
  const secs = Object.keys(secciones).filter(k => secciones[k]);
  
  if (secs.length === 0) {
    alertify.error('Debes seleccionar al menos una sección que requiera corrección.');
    return;
  }
  if (!observaciones || !observaciones.trim()) {
    alertify.error('Debes proporcionar observaciones específicas.');
    return;
  }

  procesando.value = informe.id;
  try {
    const payloadObservacion = JSON.stringify({
      secciones: secs,
      detalle: observaciones.trim()
    });

    await axios.put(`/api/supervisor/informes/${informe.id}/evaluar`, { 
      decision: 'rechazar', 
      observaciones: payloadObservacion 
    }, {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });
    
    alertify.warning('Notificación enviada al pasante para que corrija su informe.');
    informesPendientes.value = informesPendientes.value.filter(i => i.id !== informe.id);
    cerrarModalRechazo();
  } catch (err) {
    alertify.error('Error al rechazar informe.');
  } finally {
    procesando.value = null;
  }
};

// ── LÓGICA DE EDICIÓN DE OBSERVACIONES ──
const iniciarEdicionObservacion = (informe) => {
  const obs = informe.observaciones || '';
  let detalle = obs;
  const secciones = {
    nombre: false,
    horas: false,
    periodo: false,
    bitacora: false,
    imagenes: false
  };

  if (obs.trim().startsWith('{')) {
    try {
      const parsed = JSON.parse(obs);
      detalle = parsed.detalle || '';
      if (Array.isArray(parsed.secciones)) {
        parsed.secciones.forEach(sec => {
          if (sec in secciones) {
            secciones[sec] = true;
          }
        });
      }
    } catch (e) {
      // fallback
    }
  }

  modalEdicion.value = {
    abierto: true,
    informe: informe,
    observaciones: detalle,
    estado: informe.estado,
    secciones: secciones
  };
};

const cerrarModal = () => {
  modalEdicion.value.abierto = false;
  modalEdicion.value.informe = {};
};

const guardarObservacion = async () => {
  const { informe, observaciones, estado, secciones } = modalEdicion.value;
  
  let observacionFinal = observaciones;
  if (estado === 'correccion') {
    const secs = Object.keys(secciones).filter(k => secciones[k]);
    if (secs.length === 0) {
      alertify.error('Debes seleccionar al menos una sección que requiera corrección.');
      return;
    }
    if (!observaciones || !observaciones.trim()) {
      alertify.error('Debes proporcionar observaciones específicas.');
      return;
    }
    observacionFinal = JSON.stringify({
      secciones: secs,
      detalle: observaciones.trim()
    });
  }

  guardandoObservacion.value = true;
  try {
    await axios.put(`/api/supervisor/informes/${informe.id}/observar`, {
      observaciones: observacionFinal,
      estado
    }, {
      headers: { 'X-User-Id': props.usuario?.id || 2 }
    });

    alertify.success('Informe actualizado con éxito.');
    
    // Actualizar elemento en el array local
    const infIdx = informesRevisados.value.findIndex(i => i.id === informe.id);
    if (infIdx !== -1) {
      informesRevisados.value[infIdx].observaciones = observacionFinal;
      informesRevisados.value[infIdx].estado = estado;
      informesRevisados.value[infIdx].fecha_revision = new Date().toLocaleDateString('es-ES', {
        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
      });
    }

    cerrarModal();
  } catch (error) {
    console.error('Error actualizando observaciones:', error);
    alertify.error('Ocurrió un error al actualizar las observaciones.');
  } finally {
    guardandoObservacion.value = false;
  }
};

const obtenerDetalleObservacion = (obs) => {
  if (!obs) return '';
  if (obs.trim().startsWith('{')) {
    try {
      const parsed = JSON.parse(obs);
      return parsed.detalle || obs;
    } catch (e) {
      return obs;
    }
  }
  return obs;
};

const obtenerSeccionesObservacion = (obs) => {
  if (!obs) return [];
  if (obs.trim().startsWith('{')) {
    try {
      const parsed = JSON.parse(obs);
      return parsed.secciones || [];
    } catch (e) {
      return [];
    }
  }
  return [];
};

const traduccirSeccion = (seccion) => {
  const nombres = {
    nombre: 'Nombre del Informe',
    horas: 'Horas a Reportar',
    periodo: 'Período (Fechas)',
    bitacora: 'Bitácora de Actividades',
    imagenes: 'Imágenes de Evidencia'
  };
  return nombres[seccion] ?? seccion;
};
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.dashboard-section-card {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.03);
  border: 1px solid #e2e8f0;
}
.card-title {
  font-family: 'Lora', serif;
  font-size: 20px;
  font-weight: 600;
  color: #0f172a;
}
.text-primary { color: #001374 !important; }

/* Pills styling */
.nav-pills .nav-link {
  color: #64748b;
  font-weight: 500;
  border-radius: 8px;
  border: 1px solid transparent;
  transition: all 0.2s;
}
.nav-pills .nav-link.active {
  background-color: #001374;
  color: #ffffff;
}
.nav-pills .nav-link:not(.active):hover {
  background-color: #f1f5f9;
  color: #0f172a;
}

/* Cards styling */
.informe-card {
  transition: transform 0.2s, box-shadow 0.2s;
  background-color: #fafcff;
}
.informe-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0,0,0,0.05);
}

.status-card {
  background-color: #ffffff;
}
.border-status-aprobado {
  border-left: 5px solid #198754 !important;
}
.border-status-correccion {
  border-left: 5px solid #dc3545 !important;
}

.bg-success-subtle { background-color: #d1fae5 !important; }
.bg-danger-subtle { background-color: #fee2e2 !important; }

.bg-success-light { background-color: #f6fdf9; }
.bg-danger-light { background-color: #fffafb; }

.border-success-subtle { border-color: #a7f3d0 !important; }
.border-danger-subtle { border-color: #fecaca !important; }

.informe-content p {
  white-space: pre-line;
}
.pre-wrap { white-space: pre-wrap; }
.italic { font-style: italic; }
.fs-7 { font-size: 0.8rem; }

.custom-detail-table {
  font-size: 0.875rem;
  background-color: #ffffff;
}

.custom-detail-table th {
  background-color: #f8fafc;
  font-weight: 600;
}

@media (max-width: 768px) {
  .nav-pills {
    flex-direction: column;
    width: 100%;
    gap: 8px !important;
  }
  .nav-item {
    width: 100%;
  }
  .nav-link {
    width: 100%;
    text-align: center;
  }
}
</style>
