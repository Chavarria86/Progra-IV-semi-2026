/**
 * componentes/busqueda_docentes.js
 * Componente Vue para buscar, listar y eliminar Docentes
 */

'use strict';

const buscar_docentes = {
    name: 'buscar_docentes',
    props: ['forms'],
    emits: ['modificar'],
    template: `
    <div v-draggable class="card shadow" style="width:700px; top:80px; left:540px;">
        <div class="card-header d-flex justify-content-between align-items-center bg-warning text-dark">
            <span>📋 Listado de Docentes</span>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" v-model="filtro"
                       placeholder="Filtrar..." style="width:180px;" @input="filtrar" />
                <button class="btn btn-sm btn-dark" @click="obtenerDocentes">🔄</button>
            </div>
        </div>
        <div class="card-body p-0" style="max-height:400px; overflow-y:auto;">
            <table class="table table-sm table-hover table-striped mb-0">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Especialidad</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="listaFiltrada.length === 0">
                        <td colspan="7" class="text-center text-muted py-3">Sin registros</td>
                    </tr>
                    <tr v-for="(docente, idx) in listaFiltrada" :key="docente.id_Docente">
                        <td>{{ idx + 1 }}</td>
                        <td>{{ docente.codigo }}</td>
                        <td>{{ docente.nombre }}</td>
                        <td>{{ docente.especialidad }}</td>
                        <td>{{ docente.telefono }}</td>
                        <td>{{ docente.email }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-1"
                                    @click="$emit('modificar', docente)" title="Editar">✏️</button>
                            <button class="btn btn-danger btn-sm"
                                    @click="eliminarDocente(docente)" title="Eliminar">🗑️</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted small">
            Total: {{ listaFiltrada.length }} registro(s)
        </div>
    </div>
    `,
    data() {
        return {
            lista:        [],
            listaFiltrada:[],
            filtro:       ''
        };
    },
    methods: {
        async obtenerDocentes() {
            try {
                this.lista = await dbQuery(
                    `SELECT id_Docente, codigo, nombre, especialidad, telefono, email FROM docentes ORDER BY nombre ASC`
                );
                this.filtrar();
            } catch (e) {
                console.error(e);
                alertify.error('Error al obtener docentes: ' + e.message);
            }
        },

        filtrar() {
            const f = this.filtro.toLowerCase().trim();
            if (!f) {
                this.listaFiltrada = [...this.lista];
                return;
            }
            this.listaFiltrada = this.lista.filter(d =>
                d.codigo.toLowerCase().includes(f)        ||
                d.nombre.toLowerCase().includes(f)        ||
                (d.especialidad || '').toLowerCase().includes(f) ||
                d.email.toLowerCase().includes(f)
            );
        },

        eliminarDocente(docente) {
            alertify.confirm(
                'Eliminar Docente',
                `¿Está seguro de eliminar al docente <b>${docente.nombre}</b>?`,
                async () => {
                    try {
                        await dbExec(`DELETE FROM docentes WHERE id_Docente = ?`, [docente.id_Docente]);
                        alertify.success('Docente eliminado correctamente.');
                        await this.obtenerDocentes();
                    } catch (e) {
                        console.error(e);
                        alertify.error('Error al eliminar docente: ' + e.message);
                    }
                },
                () => {}
            );
        }
    }
};
